<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\ParticipantProfile;
use App\Models\Registration;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\NewRegistrationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::updateOrCreate(
            ['key' => 'whatsapp_number'],
            ['value' => '081234567890', 'type' => 'text', 'group' => 'contact']
        );
        Setting::updateOrCreate(
            ['key' => 'contact_email'],
            ['value' => 'admin@julehabrebes.org', 'type' => 'email', 'group' => 'contact']
        );
    }

    public function test_registration_form_page_can_be_rendered_for_open_event(): void
    {
        $event = Event::create([
            'title' => 'Bimtek Angkatan I',
            'slug' => 'bimtek-angkatan-1',
            'category' => 'Bimbingan Teknis',
            'description' => 'Pelatihan teori dan praktik juru sembelih halal.',
            'location' => 'Islamic Center Brebes',
            'starts_at' => now()->addDays(7),
            'ends_at' => now()->addDays(7)->addHours(8),
            'quota' => 30,
            'status' => 'open',
        ]);

        // Tamu tanpa login diarahkan ke login Google terlebih dahulu
        $guestResponse = $this->get(route('bimtek.daftar', $event->slug));
        $guestResponse->assertStatus(302);

        // Peserta yang sudah login dan lengkap profilnya dapat mengakses konfirmasi pendaftaran
        $user = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        ParticipantProfile::create([
            'user_id' => $user->id,
            'phone' => '081234567890',
            'full_name_ktp' => 'Ahmad Test',
            'nik' => '3329010101900001',
            'district' => 'Brebes',
            'village' => 'Brebes',
            'status' => 'calon_juleha',
            'consent' => true,
            'consent_at' => now(),
        ]);

        $authResponse = $this->actingAs($user)->get(route('enrollment.create', $event->slug));
        $authResponse->assertStatus(200);
        $authResponse->assertSee('Konfirmasi Pendaftaran');
        $authResponse->assertSee($event->title);
    }

    public function test_registration_successful(): void
    {
        Notification::fake();

        $event = Event::create([
            'title' => 'Bimtek JULEHA Brebes 2026',
            'slug' => 'bimtek-juleha-2026',
            'category' => 'Bimbingan Teknis',
            'description' => 'Pelatihan kompetensi penyembelih halal.',
            'location' => 'Gedung Korpri Brebes',
            'starts_at' => now()->addDays(14),
            'ends_at' => now()->addDays(14)->addHours(8),
            'quota' => 20,
            'status' => 'open',
        ]);

        $payload = [
            'full_name' => 'Ahmad Fauzi',
            'phone' => '081298765432',
            'email' => 'ahmad.fauzi@example.com',
            'district' => 'Brebes',
            'village' => 'Limbangan Wetan',
            'participant_type' => 'calon_juleha',
            'notes' => 'Pengurus masjid jami',
            'consent' => '1',
            'website_hp' => '', // Honeypot must be empty
        ];

        $response = $this->post(route('bimtek.daftar.store', $event->slug), $payload);

        $response->assertStatus(302);
        $response->assertRedirect(route('registration.success'));
        $response->assertSessionHas('registered_code');

        $this->assertDatabaseHas('registrations', [
            'event_id' => $event->id,
            'full_name' => 'Ahmad Fauzi',
            'phone' => '6281298765432', // Normalized to 62xxxxxxxx
            'district' => 'Brebes',
            'status' => 'pending',
            'consent' => true,
        ]);

        $registration = Registration::where('event_id', $event->id)->first();
        $this->assertNotNull($registration);
        $this->assertMatchesRegularExpression('/^JLH-\d{4}-[A-Z0-9]{4}$/', $registration->code);
        $this->assertNotNull($registration->consent_at);

        Notification::assertSentTo(
            new AnonymousNotifiable,
            NewRegistrationNotification::class
        );
    }

    public function test_registration_duplicate_phone_rejected(): void
    {
        Notification::fake();

        $event = Event::create([
            'title' => 'Bimtek Duplikat Test',
            'slug' => 'bimtek-duplikat-test',
            'category' => 'Bimbingan Teknis',
            'location' => 'Brebes',
            'starts_at' => now()->addDays(10),
            'quota' => 15,
            'status' => 'open',
        ]);

        Registration::create([
            'event_id' => $event->id,
            'code' => 'JLH-2610-TEST',
            'full_name' => 'Peserta Pertama',
            'phone' => '6281234567890',
            'district' => 'Jatibarang',
            'participant_type' => 'calon_juleha',
            'status' => 'pending',
            'consent' => true,
            'consent_at' => now(),
        ]);

        $payload = [
            'full_name' => 'Peserta Kedua Dengan Nomor Sama',
            'phone' => '081234567890', // Format 0812... normalizes to 6281234567890
            'district' => 'Brebes',
            'participant_type' => 'calon_juleha',
            'consent' => '1',
            'website_hp' => '',
        ];

        $response = $this->from(route('bimtek.daftar', $event->slug))
            ->post(route('bimtek.daftar.store', $event->slug), $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['phone']);

        $this->assertEquals(1, Registration::where('event_id', $event->id)->count());
    }

    public function test_registration_quota_full_rejected(): void
    {
        Notification::fake();

        $event = Event::create([
            'title' => 'Bimtek Kuota Penuh Test',
            'slug' => 'bimtek-kuota-penuh-test',
            'category' => 'Bimbingan Teknis',
            'location' => 'Brebes',
            'starts_at' => now()->addDays(5),
            'quota' => 1,
            'status' => 'open',
        ]);

        Registration::create([
            'event_id' => $event->id,
            'code' => 'JLH-2610-FUL1',
            'full_name' => 'Peserta Kuota 1',
            'phone' => '6281111111111',
            'district' => 'Brebes',
            'participant_type' => 'calon_juleha',
            'status' => 'confirmed',
            'consent' => true,
            'consent_at' => now(),
        ]);

        $payload = [
            'full_name' => 'Peserta Kelebihan Kuota',
            'phone' => '082222222222',
            'district' => 'Wanasari',
            'participant_type' => 'calon_juleha',
            'consent' => '1',
            'website_hp' => '',
        ];

        $response = $this->from(route('bimtek.daftar', $event->slug))
            ->post(route('bimtek.daftar.store', $event->slug), $payload);

        $response->assertStatus(302);
        $response->assertSessionHas('error');

        $this->assertEquals(1, Registration::where('event_id', $event->id)->count());
    }

    public function test_registration_closed_event_rejected(): void
    {
        Notification::fake();

        $event = Event::create([
            'title' => 'Bimtek Sudah Ditutup Test',
            'slug' => 'bimtek-sudah-ditutup-test',
            'category' => 'Bimbingan Teknis',
            'location' => 'Brebes',
            'starts_at' => now()->addDays(3),
            'quota' => 20,
            'status' => 'closed',
        ]);

        $payload = [
            'full_name' => 'Calon Peserta Tertolak',
            'phone' => '083333333333',
            'district' => 'Larangan',
            'participant_type' => 'calon_juleha',
            'consent' => '1',
            'website_hp' => '',
        ];

        $response = $this->post(route('bimtek.daftar.store', $event->slug), $payload);

        $response->assertStatus(302);
        $response->assertSessionHas('error');

        $this->assertEquals(0, Registration::where('event_id', $event->id)->count());
    }

    public function test_registration_honeypot_triggers_validation_error(): void
    {
        $event = Event::create([
            'title' => 'Bimtek Honeypot Test',
            'slug' => 'bimtek-honeypot-test',
            'location' => 'Brebes',
            'starts_at' => now()->addDays(10),
            'quota' => 10,
            'status' => 'open',
        ]);

        $payload = [
            'full_name' => 'Bot Spammer',
            'phone' => '089999999999',
            'district' => 'Brebes',
            'participant_type' => 'calon_juleha',
            'consent' => '1',
            'website_hp' => 'http://spam.example.com', // Honeypot filled by bot
        ];

        $response = $this->post(route('bimtek.daftar.store', $event->slug), $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['website_hp']);
        $this->assertEquals(0, Registration::where('event_id', $event->id)->count());
    }

    public function test_registration_requires_personal_data_consent(): void
    {
        $event = Event::create([
            'title' => 'Bimtek Consent Test',
            'slug' => 'bimtek-consent-test',
            'location' => 'Brebes',
            'starts_at' => now()->addDays(10),
            'quota' => 10,
            'status' => 'open',
        ]);

        $payload = [
            'full_name' => 'Peserta Tanpa Centang Consent',
            'phone' => '088888888888',
            'district' => 'Brebes',
            'participant_type' => 'calon_juleha',
            // 'consent' omitted
            'website_hp' => '',
        ];

        $response = $this->post(route('bimtek.daftar.store', $event->slug), $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['consent']);
        $this->assertEquals(0, Registration::where('event_id', $event->id)->count());
    }
}
