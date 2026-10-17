<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Enums\EventType;
use App\Mail\NewMaterialNotification;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\Material;
use App\Models\MaterialDownload;
use App\Models\User;
use App\Services\MaterialService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventMaterialTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->event = Event::first() ?? Event::create([
            'title' => 'Bimtek JULEHA Brebes Angkatan 1',
            'slug' => 'bimtek-juleha-angkatan-1',
            'type' => EventType::Bimtek,
            'starts_at' => now()->addDays(7),
            'location' => 'Brebes',
            'quota' => 30,
            'fee' => 0,
            'status' => 'open',
        ]);
    }

    /**
     * 1. Tamu tidak terautentikasi diarahkan ke halaman login (302).
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $material = Material::create([
            'event_id' => $this->event->id,
            'title' => 'Modul Syariah',
            'kind' => 'file',
            'path_or_url' => 'materials/modul.pdf',
            'is_published' => true,
        ]);

        $this->get(route('participant.event.materials', $this->event->slug))
            ->assertRedirect(route('login'));

        $this->get(route('participant.material.download', [$this->event->slug, $material->id]))
            ->assertRedirect(route('login'));

        $this->get(route('participant.material.preview', [$this->event->slug, $material->id]))
            ->assertRedirect(route('login'));
    }

    /**
     * 2. Peserta aktif bisa melihat daftar materi kegiatan.
     */
    public function test_active_participant_can_view_materials_page(): void
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        Enrollment::create([
            'event_id' => $this->event->id,
            'user_id' => $participant->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        $material = Material::create([
            'event_id' => $this->event->id,
            'title' => 'Buku Saku Fiqih Sembelih',
            'description' => 'Panduan ringkas fiqih sembelih halal.',
            'kind' => 'file',
            'path_or_url' => 'materials/buku-saku.pdf',
            'size' => 1024 * 1024 * 2, // 2MB
            'sort_order' => 1,
            'is_published' => true,
        ]);

        $response = $this->actingAs($participant)->get(route('participant.event.materials', $this->event->slug));

        $response->assertStatus(200);
        $response->assertSee('Buku Saku Fiqih Sembelih');
        $response->assertSee('2.0 MB');
        $response->assertSee('Panduan ringkas fiqih sembelih halal.');
    }

    /**
     * 3. Peserta kegiatan lain ditolak (403).
     */
    public function test_participant_of_another_event_is_forbidden_403(): void
    {
        $otherEvent = Event::create([
            'title' => 'Kegiatan Lain di Luar Brebes',
            'slug' => 'kegiatan-lain-luar-brebes',
            'type' => EventType::Bimtek,
            'starts_at' => now()->addDays(20),
            'location' => 'Bumiayu',
            'quota' => 20,
            'fee' => 0,
            'status' => 'open',
        ]);

        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        // Terdaftar aktif HANYA di otherEvent
        Enrollment::create([
            'event_id' => $otherEvent->id,
            'user_id' => $participant->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        // Mencoba mengakses materi di $this->event
        $response = $this->actingAs($participant)->get(route('participant.event.materials', $this->event->slug));

        $response->assertStatus(403);
    }

    /**
     * 4. Peserta dengan status menunggu ditolak (403).
     */
    public function test_participant_with_pending_status_is_forbidden_403(): void
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        Enrollment::create([
            'event_id' => $this->event->id,
            'user_id' => $participant->id,
            'status' => EnrollmentStatus::Menunggu,
        ]);

        $response = $this->actingAs($participant)->get(route('participant.event.materials', $this->event->slug));

        $response->assertStatus(403);
    }

    /**
     * 5. Peserta aktif bisa mengunduh file privat dan unduhan tercatat di material_downloads.
     */
    public function test_active_participant_can_download_private_file_and_logs_download(): void
    {
        Storage::fake('private');

        $fakeFilePath = 'materials/materi-uji.pdf';
        Storage::disk('private')->put($fakeFilePath, 'konten file privat');

        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        Enrollment::create([
            'event_id' => $this->event->id,
            'user_id' => $participant->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        $material = Material::create([
            'event_id' => $this->event->id,
            'title' => 'Materi Uji Praktik',
            'kind' => 'file',
            'path_or_url' => $fakeFilePath,
            'is_published' => true,
        ]);

        $response = $this->actingAs($participant)
            ->get(route('participant.material.download', [$this->event->slug, $material->id]));

        $response->assertStatus(200);

        // Pastikan catatan riwayat unduhan tersimpan
        $this->assertDatabaseHas('material_downloads', [
            'material_id' => $material->id,
            'user_id' => $participant->id,
        ]);
        $this->assertEquals(1, MaterialDownload::where('material_id', $material->id)->count());
    }

    /**
     * 6. Pratinjau PDF di browser mengembalikan respons inline dengan header yang sesuai.
     */
    public function test_active_participant_can_preview_pdf_inline(): void
    {
        Storage::fake('private');

        $fakePdfPath = 'materials/pedoman-haji.pdf';
        Storage::disk('private')->put($fakePdfPath, '%PDF-1.4 konten dummy pdf');

        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        Enrollment::create([
            'event_id' => $this->event->id,
            'user_id' => $participant->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        $material = Material::create([
            'event_id' => $this->event->id,
            'title' => 'Pedoman Sembelih Syar\'i',
            'kind' => 'file',
            'path_or_url' => $fakePdfPath,
            'is_published' => true,
        ]);

        $response = $this->actingAs($participant)
            ->get(route('participant.material.preview', [$this->event->slug, $material->id]));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
    }

    /**
     * 7. Peserta tidak dapat membuka materi kegiatan lain walau menebak ID atau URL (404).
     */
    public function test_cannot_tamper_material_id_across_events_404(): void
    {
        $event2 = Event::create([
            'title' => 'Kegiatan Lain',
            'slug' => 'kegiatan-lain-2',
            'type' => EventType::Bimtek,
            'starts_at' => now()->addDays(10),
            'location' => 'Brebes',
            'quota' => 20,
            'fee' => 0,
            'status' => 'open',
        ]);

        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        Enrollment::create([
            'event_id' => $this->event->id,
            'user_id' => $participant->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        // Materi milik event2
        $foreignMaterial = Material::create([
            'event_id' => $event2->id,
            'title' => 'Materi Rahasia Event 2',
            'kind' => 'file',
            'path_or_url' => 'materials/event2.pdf',
            'is_published' => true,
        ]);

        // Peserta mencoba membuka materi milik event2 menggunakan slug $this->event
        $response = $this->actingAs($participant)
            ->get(route('participant.material.download', [$this->event->slug, $foreignMaterial->id]));

        $response->assertStatus(404);
    }

    /**
     * 8. Materi belum terbit (is_published = false) disembunyikan dan ditolak dari peserta (404).
     */
    public function test_unpublished_material_is_hidden_and_rejected(): void
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        Enrollment::create([
            'event_id' => $this->event->id,
            'user_id' => $participant->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        $draftMaterial = Material::create([
            'event_id' => $this->event->id,
            'title' => 'Draft Materi Belum Terbit',
            'kind' => 'file',
            'path_or_url' => 'materials/draft.pdf',
            'is_published' => false,
        ]);

        // Pada halaman index peserta, materi draft tidak tampil
        $indexResponse = $this->actingAs($participant)
            ->get(route('participant.event.materials', $this->event->slug));
        $indexResponse->assertStatus(200);
        $indexResponse->assertDontSee('Draft Materi Belum Terbit');

        // Percobaan unduh langsung ditolak 404
        $downloadResponse = $this->actingAs($participant)
            ->get(route('participant.material.download', [$this->event->slug, $draftMaterial->id]));
        $downloadResponse->assertStatus(404);
    }

    /**
     * 9. Notifikasi email dikirim ke antrean (queue) hanya untuk peserta aktif saat materi terbit.
     */
    public function test_queued_email_notification_sent_to_active_participants(): void
    {
        Mail::fake();

        $activeParticipant = User::factory()->create([
            'email' => 'peserta.aktif@example.com',
            'role' => User::ROLE_PARTICIPANT,
        ]);
        $pendingParticipant = User::factory()->create([
            'email' => 'peserta.menunggu@example.com',
            'role' => User::ROLE_PARTICIPANT,
        ]);

        Enrollment::create([
            'event_id' => $this->event->id,
            'user_id' => $activeParticipant->id,
            'status' => EnrollmentStatus::Aktif,
        ]);
        Enrollment::create([
            'event_id' => $this->event->id,
            'user_id' => $pendingParticipant->id,
            'status' => EnrollmentStatus::Menunggu,
        ]);

        $material = Material::create([
            'event_id' => $this->event->id,
            'title' => 'Materi Fiqih Sembelih Baru',
            'kind' => 'file',
            'path_or_url' => 'materials/fiqih.pdf',
            'is_published' => true,
        ]);

        $sentCount = MaterialService::notifyActiveParticipants($material);

        $this->assertEquals(1, $sentCount);

        Mail::assertQueued(NewMaterialNotification::class, function ($mail) use ($activeParticipant, $material) {
            return $mail->hasTo($activeParticipant->email) &&
                   $mail->material->id === $material->id;
        });

        Mail::assertNotQueued(NewMaterialNotification::class, function ($mail) use ($pendingParticipant) {
            return $mail->hasTo($pendingParticipant->email);
        });
    }

    /**
     * 10. Materi bertipe tautan daring (misal YouTube/Drive) mengarahkan peserta ke tautan luar.
     */
    public function test_link_material_redirects_away_and_logs_download(): void
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        Enrollment::create([
            'event_id' => $this->event->id,
            'user_id' => $participant->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        $material = Material::create([
            'event_id' => $this->event->id,
            'title' => 'Video Praktik Asah Bilah',
            'kind' => 'link',
            'path_or_url' => 'https://www.youtube.com/watch?v=sample123',
            'is_published' => true,
        ]);

        $response = $this->actingAs($participant)
            ->get(route('participant.material.download', [$this->event->slug, $material->id]));

        $response->assertRedirect('https://www.youtube.com/watch?v=sample123');

        $this->assertDatabaseHas('material_downloads', [
            'material_id' => $material->id,
            'user_id' => $participant->id,
        ]);
    }

    /**
     * 11. Admin dapat membuka form pembuatan materi di panel admin tanpa TypeError schema closure.
     */
    public function test_admin_can_render_material_create_page(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/materials/create');

        $response->assertSuccessful();
    }
}
