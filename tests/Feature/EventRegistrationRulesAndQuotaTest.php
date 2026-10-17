<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Enums\EventRegistrationState;
use App\Models\Event;
use App\Models\ParticipantProfile;
use App\Models\User;
use App\Services\EnrollmentService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventRegistrationRulesAndQuotaTest extends TestCase
{
    use RefreshDatabase;

    protected function createParticipantUser(string $name = 'Peserta Test', string $email = 'peserta@julehatest.org', string $nik = '3329012345670001'): User
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
            'role' => User::ROLE_PARTICIPANT,
            'is_active' => true,
        ]);

        ParticipantProfile::create([
            'user_id' => $user->id,
            'full_name_ktp' => $name,
            'nik' => $nik,
            'nik_hash' => hash('sha256', $nik),
            'phone' => '0812'.rand(10000000, 99999999),
            'district' => 'Brebes',
            'village' => 'Brebes',
            'status' => 'calon_juleha',
        ]);

        return $user;
    }

    protected function createEvent(array $attributes = []): Event
    {
        return Event::create(array_merge([
            'title' => 'Bimtek Sembelih Halal Angkatan 1',
            'slug' => 'bimtek-sembelih-halal-angkatan-1-'.uniqid(),
            'status' => 'open',
            'location' => 'Islamic Center Brebes',
            'starts_at' => Carbon::now('Asia/Jakarta')->addDays(3),
            'ends_at' => Carbon::now('Asia/Jakarta')->addDays(4),
            'quota' => 30,
        ], $attributes));
    }

    public function test_full_quota_rejects_new_enrollment_and_menunggu_is_counted(): void
    {
        $event = $this->createEvent(['quota' => 1]);
        $user1 = $this->createParticipantUser('User Satu', 'user1@test.com', '3329000000000001');
        $user2 = $this->createParticipantUser('User Dua', 'user2@test.com', '3329000000000002');

        $service = app(EnrollmentService::class);

        // User 1 mendaftar -> Menunggu (kursi terpakai = 1)
        $enrollment1 = $service->enroll($user1, $event);
        $this->assertEquals(EnrollmentStatus::Menunggu, $enrollment1->status);

        $event->refresh();
        $this->assertEquals(1, $event->usedSeats());
        $this->assertEquals(0, $event->seatsLeft());
        $this->assertTrue($event->isFull());
        $this->assertEquals(EventRegistrationState::Full, $event->registrationState());

        // User 2 mencoba mendaftar -> ditolak karena kuota penuh
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Maaf, kuota kegiatan ini sudah penuh.');
        $service->enroll($user2, $event);
    }

    public function test_rejected_enrollment_frees_up_quota_seat(): void
    {
        $event = $this->createEvent(['quota' => 1]);
        $user1 = $this->createParticipantUser('User Satu', 'user1@test.com', '3329000000000001');
        $user2 = $this->createParticipantUser('User Dua', 'user2@test.com', '3329000000000002');

        $service = app(EnrollmentService::class);
        $enrollment1 = $service->enroll($user1, $event);

        $this->assertTrue($event->refresh()->isFull());

        // Admin menolak pendaftaran User 1
        $enrollment1->update(['status' => EnrollmentStatus::Ditolak]);

        $event->refresh();
        $this->assertEquals(0, $event->usedSeats());
        $this->assertEquals(1, $event->seatsLeft());
        $this->assertFalse($event->isFull());
        $this->assertTrue($event->canRegister());

        // User 2 sekarang bisa mendaftar
        $enrollment2 = $service->enroll($user2, $event);
        $this->assertNotNull($enrollment2->id);
    }

    public function test_closed_status_rejects_enrollment(): void
    {
        $event = $this->createEvent(['status' => 'closed']);
        $user = $this->createParticipantUser();

        $this->assertEquals(EventRegistrationState::Closed, $event->registrationState());

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Pendaftaran kegiatan ini sudah ditutup.');
        app(EnrollmentService::class)->enroll($user, $event);
    }

    public function test_deadline_passed_rejects_enrollment(): void
    {
        $event = $this->createEvent([
            'registration_deadline' => Carbon::now('Asia/Jakarta')->subHour(),
        ]);
        $user = $this->createParticipantUser();

        $this->assertEquals(EventRegistrationState::DeadlinePassed, $event->registrationState());

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Batas pendaftaran sudah lewat.');
        app(EnrollmentService::class)->enroll($user, $event);
    }

    public function test_finished_event_rejects_enrollment(): void
    {
        $event = $this->createEvent([
            'starts_at' => Carbon::now('Asia/Jakarta')->subDays(2),
            'ends_at' => Carbon::now('Asia/Jakarta')->subHour(),
        ]);
        $user = $this->createParticipantUser();

        $this->assertEquals(EventRegistrationState::Finished, $event->registrationState());

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Kegiatan ini sudah selesai.');
        app(EnrollmentService::class)->enroll($user, $event);
    }

    public function test_null_quota_allows_unlimited_enrollment(): void
    {
        $event = $this->createEvent(['quota' => null]);
        $user = $this->createParticipantUser();

        $this->assertNull($event->seatsLeft());
        $this->assertFalse($event->isFull());
        $this->assertEquals(EventRegistrationState::Open, $event->registrationState());

        $enrollment = app(EnrollmentService::class)->enroll($user, $event);
        $this->assertNotNull($enrollment->id);
    }

    public function test_direct_post_to_full_event_is_rejected_at_server(): void
    {
        $event = $this->createEvent(['quota' => 1]);
        $user1 = $this->createParticipantUser('User 1', 'u1@test.com', '3329000000000011');
        $user2 = $this->createParticipantUser('User 2', 'u2@test.com', '3329000000000012');

        app(EnrollmentService::class)->enroll($user1, $event);

        // User 2 melakukan POST langsung ke route pendaftaran
        $response = $this->actingAs($user2)->post(route('enrollment.store', $event->slug));

        $response->assertRedirect(route('bimtek.show', $event->slug));
        $response->assertSessionHas('error', 'Maaf, kuota kegiatan ini sudah penuh.');

        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $user2->id,
            'event_id' => $event->id,
        ]);
    }

    public function test_direct_get_form_for_unregistrable_event_redirects_to_detail_with_message(): void
    {
        $event = $this->createEvent([
            'registration_deadline' => Carbon::now('Asia/Jakarta')->subMinutes(10),
        ]);
        $user = $this->createParticipantUser();

        $response = $this->actingAs($user)->get(route('enrollment.create', $event->slug));

        $response->assertRedirect(route('bimtek.show', $event->slug));
        $response->assertSessionHas('error', 'Batas pendaftaran sudah lewat.');
    }

    public function test_duplicate_enrollment_is_rejected(): void
    {
        $event = $this->createEvent(['quota' => 10]);
        $user = $this->createParticipantUser();

        app(EnrollmentService::class)->enroll($user, $event);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Anda sudah terdaftar pada kegiatan ini.');
        app(EnrollmentService::class)->enroll($user, $event);
    }

    public function test_incomplete_profile_without_nik_is_rejected(): void
    {
        $event = $this->createEvent(['quota' => 10]);
        $user = User::factory()->create([
            'role' => User::ROLE_PARTICIPANT,
            'is_active' => true,
        ]);
        // Tanpa ParticipantProfile

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Nomor Induk Kependudukan (NIK) dan data profil wajib dilengkapi terlebih dahulu.');
        app(EnrollmentService::class)->enroll($user, $event);
    }

    public function test_staff_cannot_enroll_in_events(): void
    {
        $event = $this->createEvent(['quota' => 10]);
        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Pengurus tidak dapat mendaftar sebagai peserta kegiatan.');
        app(EnrollmentService::class)->enroll($admin, $event);
    }

    public function test_admin_can_bypass_quota_with_manual_enroll_action_and_logs_audit(): void
    {
        $event = $this->createEvent(['quota' => 1]);
        $user1 = $this->createParticipantUser('User 1', 'u1@test.com', '3329000000000021');
        $user2 = $this->createParticipantUser('User 2', 'u2@test.com', '3329000000000022');
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN_KEGIATAN,
            'is_active' => true,
        ]);

        app(EnrollmentService::class)->enroll($user1, $event);
        $this->assertTrue($event->refresh()->isFull());

        // Admin menambahkan User 2 dengan aksi bypass kuota
        $enrollment = app(EnrollmentService::class)->manualEnrollBypassingQuota(
            $user2,
            $event,
            'Dispensasi dinas peternakan',
            $admin->id
        );

        $this->assertEquals(EnrollmentStatus::Aktif, $enrollment->status);
        $this->assertEquals(2, $event->refresh()->usedSeats());

        // Pastikan tercatat di audit_logs
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'manual_enroll_quota_bypass',
            'user_id' => $admin->id,
            'target_type' => 'User',
            'target_id' => $user2->id,
        ]);
    }

    public function test_close_finished_events_command_sets_status_to_done(): void
    {
        $expiredEvent = $this->createEvent([
            'status' => 'open',
            'starts_at' => Carbon::now('Asia/Jakarta')->subDays(3),
            'ends_at' => Carbon::now('Asia/Jakarta')->subMinutes(5),
        ]);

        $futureEvent = $this->createEvent([
            'status' => 'open',
            'starts_at' => Carbon::now('Asia/Jakarta')->addDays(2),
            'ends_at' => Carbon::now('Asia/Jakarta')->addDays(3),
        ]);

        $this->artisan('app:close-finished-events')->assertSuccessful();

        $this->assertEquals('done', $expiredEvent->refresh()->status);
        $this->assertEquals('open', $futureEvent->refresh()->status);
    }

    public function test_home_page_cta_changes_when_no_open_events(): void
    {
        // Tutup semua event
        Event::query()->update(['status' => 'closed']);

        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee('Lihat Jadwal Kegiatan');
        $response->assertDontSee('Daftar Bimtek Sekarang');
    }

    public function test_finished_events_do_not_appear_on_homepage(): void
    {
        $doneEvent = $this->createEvent([
            'title' => 'Bimtek Selesai Masa Lalu',
            'status' => 'done',
            'starts_at' => Carbon::now('Asia/Jakarta')->subDays(10),
            'ends_at' => Carbon::now('Asia/Jakarta')->subDays(9),
        ]);

        $pastTimeEvent = $this->createEvent([
            'title' => 'Bimtek Waktu Berakhir Lewat',
            'status' => 'open',
            'starts_at' => Carbon::now('Asia/Jakarta')->subDays(2),
            'ends_at' => Carbon::now('Asia/Jakarta')->subHours(1),
        ]);

        $activeEvent = $this->createEvent([
            'title' => 'Bimtek Mendatang Masih Aktif',
            'status' => 'open',
            'starts_at' => Carbon::now('Asia/Jakarta')->addDays(5),
            'ends_at' => Carbon::now('Asia/Jakarta')->addDays(6),
        ]);

        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee('Bimtek Mendatang Masih Aktif');
        $response->assertDontSee('Bimtek Selesai Masa Lalu');
        $response->assertDontSee('Bimtek Waktu Berakhir Lewat');
    }

    public function test_finished_events_do_not_appear_on_bimtek_page(): void
    {
        $doneEvent = $this->createEvent([
            'title' => 'Bimtek Selesai Tidak Muncul',
            'status' => 'done',
        ]);

        $activeEvent = $this->createEvent([
            'title' => 'Bimtek Muncul di Halaman Bimtek',
            'status' => 'open',
        ]);

        $response = $this->get(route('bimtek.index'));
        $response->assertStatus(200);
        $response->assertSee('Bimtek Muncul di Halaman Bimtek');
        $response->assertDontSee('Bimtek Selesai Tidak Muncul');
    }

    public function test_accessing_finished_event_detail_redirects_to_bimtek_page(): void
    {
        $doneEvent = $this->createEvent([
            'title' => 'Bimtek Yang Telah Selesai',
            'status' => 'done',
        ]);

        $response = $this->get(route('bimtek.show', $doneEvent->slug));
        $response->assertRedirect(route('bimtek.index'));
        $response->assertSessionHas('info');
    }

    public function test_homepage_shows_friendly_empty_state_when_all_events_are_finished(): void
    {
        Event::query()->delete();

        $this->createEvent([
            'title' => 'Bimtek Lampau Selesai',
            'status' => 'done',
        ]);

        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee('Belum Ada Jadwal Bimtek Terdekat');
        $response->assertDontSee('Bimtek Lampau Selesai');
    }
}
