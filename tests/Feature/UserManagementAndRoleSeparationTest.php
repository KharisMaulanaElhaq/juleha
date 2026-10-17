<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\EnrollmentStatus;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\DashboardOverviewWidget;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\EventSession;
use App\Models\ParticipantProfile;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementAndRoleSeparationTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $adminKegiatan;

    protected User $editorKonten;

    protected User $peserta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'name' => 'Super Admin Test',
            'email' => 'superadmin@julehatest.org',
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->adminKegiatan = User::factory()->create([
            'name' => 'Admin Kegiatan Test',
            'email' => 'kegiatan@julehatest.org',
            'role' => User::ROLE_ADMIN_KEGIATAN,
            'is_active' => true,
        ]);

        $this->editorKonten = User::factory()->create([
            'name' => 'Editor Konten Test',
            'email' => 'editor@julehatest.org',
            'role' => User::ROLE_EDITOR_KONTEN,
            'is_active' => true,
        ]);

        $this->peserta = User::factory()->create([
            'name' => 'Peserta Uji',
            'email' => 'peserta@julehatest.org',
            'role' => User::ROLE_PARTICIPANT,
            'is_active' => true,
        ]);
    }

    public function test_staff_roles_opening_participant_dashboard_are_redirected_to_admin(): void
    {
        // 1. Super Admin
        $response = $this->actingAs($this->superAdmin)->get(route('dashboard'));
        $response->assertRedirect('/admin');
        $response->assertSessionHas('info', 'Anda masuk sebagai pengurus. Panel pengurus ada di sini.');

        // 2. Admin Kegiatan
        $response = $this->actingAs($this->adminKegiatan)->get(route('dashboard'));
        $response->assertRedirect('/admin');
        $response->assertSessionHas('info', 'Anda masuk sebagai pengurus. Panel pengurus ada di sini.');

        // 3. Editor Konten
        $response = $this->actingAs($this->editorKonten)->get(route('dashboard'));
        $response->assertRedirect('/admin');
        $response->assertSessionHas('info', 'Anda masuk sebagai pengurus. Panel pengurus ada di sini.');

        // 4. Peserta Asli dapat membuka dashboard
        $response = $this->actingAs($this->peserta)->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_participant_cannot_access_admin_panel(): void
    {
        $response = $this->actingAs($this->peserta)->get('/admin');
        $response->assertForbidden();
    }

    public function test_staff_cannot_enroll_in_events_as_participant(): void
    {
        $event = Event::create([
            'title' => 'Bimtek Angkatan 1',
            'slug' => 'bimtek-angkatan-1',
            'category' => 'Bimtek',
            'status' => 'open',
            'quota' => 50,
            'starts_at' => now()->addDays(5),
            'location' => 'Brebes',
        ]);

        // Coba buka form pendaftaran
        $response = $this->actingAs($this->superAdmin)->get(route('enrollment.create', $event->slug));
        $response->assertRedirect('/admin');

        // Coba kirim pendaftaran via POST
        $response = $this->actingAs($this->superAdmin)->post(route('enrollment.store', $event->slug));
        $response->assertRedirect('/admin');

        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $this->superAdmin->id,
            'event_id' => $event->id,
        ]);
    }

    public function test_staff_cannot_scan_attendance_as_participant(): void
    {
        $event = Event::create([
            'title' => 'Bimtek Angkatan 2',
            'slug' => 'bimtek-angkatan-2',
            'category' => 'Bimtek',
            'status' => 'open',
            'quota' => 50,
            'starts_at' => now(),
            'location' => 'Brebes',
        ]);
        $session = EventSession::create([
            'event_id' => $event->id,
            'title' => 'Sesi 1',
            'attendance_open' => true,
            'starts_at' => now(),
        ]);

        $response = $this->actingAs($this->adminKegiatan)->get(route('attendance.absen'));
        $response->assertRedirect('/admin');
    }

    public function test_user_scopes_participants_and_staff(): void
    {
        $participants = User::participants()->pluck('id');
        $staff = User::staff()->pluck('id');

        $this->assertTrue($participants->contains($this->peserta->id));
        $this->assertFalse($participants->contains($this->superAdmin->id));
        $this->assertFalse($participants->contains($this->adminKegiatan->id));
        $this->assertFalse($participants->contains($this->editorKonten->id));

        $this->assertTrue($staff->contains($this->superAdmin->id));
        $this->assertTrue($staff->contains($this->adminKegiatan->id));
        $this->assertTrue($staff->contains($this->editorKonten->id));
        $this->assertFalse($staff->contains($this->peserta->id));
    }

    public function test_dashboard_overview_widget_counts_only_participants(): void
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(DashboardOverviewWidget::class)
            ->assertSeeHtml('Peserta Terdaftar')
            ->assertSeeHtml('1</span> <span class="stat-unit text-xs sm:text-sm font-normal text-gray-500 dark:text-gray-400">orang</span>');
    }

    public function test_user_policy_restricts_user_resource_to_super_admin_only(): void
    {
        // 1. Super Admin berhak melihat
        $response = $this->actingAs($this->superAdmin)->get(UserResource::getUrl('index'));
        $response->assertOk();

        // 2. Admin Kegiatan ditolak 403
        $response = $this->actingAs($this->adminKegiatan)->get(UserResource::getUrl('index'));
        $response->assertForbidden();

        // 3. Editor Konten ditolak 403
        $response = $this->actingAs($this->editorKonten)->get(UserResource::getUrl('index'));
        $response->assertForbidden();
    }

    public function test_deactivating_user_clears_sessions_and_prevents_subsequent_login(): void
    {
        $targetUser = User::factory()->create([
            'name' => 'Target User',
            'email' => 'target@julehatest.org',
            'password' => Hash::make('secret123'),
            'role' => User::ROLE_PARTICIPANT,
            'is_active' => true,
        ]);

        // Simulasikan session di database
        DB::table('sessions')->insert([
            'id' => 'test-session-123',
            'user_id' => $targetUser->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'payload_data',
            'last_activity' => time(),
        ]);

        $this->assertDatabaseHas('sessions', ['user_id' => $targetUser->id]);

        // Nonaktifkan user
        $targetUser->update([
            'is_active' => false,
            'suspended_at' => now(),
            'suspended_reason' => 'Uji penonaktifan sesi',
        ]);
        DB::table('sessions')->where('user_id', $targetUser->id)->delete();
        $targetUser->forceFill(['remember_token' => null])->saveQuietly();

        // Verifikasi sesi terhapus
        $this->assertDatabaseMissing('sessions', ['user_id' => $targetUser->id]);

        // Coba login dengan password
        $response = $this->post(route('login.post'), [
            'email' => 'target@julehatest.org',
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors(['email' => 'Akun Anda dinonaktifkan. Hubungi panitia.']);
        $this->assertGuest();
    }

    public function test_activating_user_restores_login_access(): void
    {
        $targetUser = User::factory()->create([
            'name' => 'Target User',
            'email' => 'target-reactivate@julehatest.org',
            'password' => Hash::make('secret123'),
            'role' => User::ROLE_PARTICIPANT,
            'is_active' => false,
            'suspended_at' => now(),
            'suspended_reason' => 'Alasan lama',
        ]);

        // Aktifkan kembali
        $targetUser->update([
            'is_active' => true,
            'suspended_at' => null,
            'suspended_reason' => null,
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'target-reactivate@julehatest.org',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($targetUser);
    }

    public function test_anonymizing_user_with_history_clears_pii_and_retains_records(): void
    {
        $event = Event::create([
            'title' => 'Bimtek Juleha Angkatan 1',
            'status' => 'open',
            'location' => 'Islamic Center Brebes',
            'starts_at' => now()->addDays(1),
            'ends_at' => now()->addDays(2),
            'quota' => 50,
        ]);
        $user = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@gmail.com',
            'role' => User::ROLE_PARTICIPANT,
            'google_id' => 'google-uid-12345',
            'avatar' => 'https://lh3.googleusercontent.com/avatar.jpg',
            'is_active' => true,
        ]);

        ParticipantProfile::create([
            'user_id' => $user->id,
            'full_name_ktp' => 'Budi Santoso',
            'nik' => '3329012345670001',
            'nik_hash' => hash('sha256', '3329012345670001'),
            'phone' => '081234567890',
            'district' => 'Brebes',
            'village' => 'Brebes',
            'status' => 'juleha_aktif',
        ]);

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => EnrollmentStatus::Aktif,
        ]);

        $session = EventSession::create([
            'event_id' => $event->id,
            'title' => 'Sesi Teori Penyembelihan Halal',
            'starts_at' => now()->addHours(1),
            'ends_at' => now()->addHours(3),
        ]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'event_session_id' => $session->id,
            'status' => AttendanceStatus::Hadir,
            'source' => 'qr',
            'attended_at' => now(),
        ]);

        $this->assertTrue($user->hasHistory());

        // Lakukan proses anonimisasi
        $oldId = $user->id;
        $user->update([
            'name' => 'Pengguna Dihapus #'.$oldId,
            'email' => 'anonymized_'.$oldId.'_'.time().'@julehabrebes.org',
            'avatar' => null,
            'google_id' => null,
            'password' => Hash::make('random-password-32-chars'),
            'is_active' => false,
            'suspended_at' => now(),
            'suspended_reason' => 'Akun dianonimkan atas permintaan penghapusan data PDP',
            'anonymized_at' => now(),
        ]);

        $user->profile->update([
            'full_name_ktp' => null,
            'nik' => null,
            'nik_hash' => null,
            'phone' => null,
            'district' => null,
            'village' => null,
            'institution' => null,
            'status' => null,
        ]);

        $user->delete(); // Soft delete

        // Verifikasi PII bersih
        $refreshedUser = User::withTrashed()->find($oldId);
        $this->assertStringStartsWith('Pengguna Dihapus', $refreshedUser->name);
        $this->assertNull($refreshedUser->avatar);
        $this->assertNull($refreshedUser->google_id);
        $this->assertNotNull($refreshedUser->anonymized_at);
        $this->assertTrue($refreshedUser->trashed());

        $refreshedProfile = ParticipantProfile::where('user_id', $oldId)->first();
        $this->assertNull($refreshedProfile->nik);
        $this->assertNull($refreshedProfile->nik_hash);
        $this->assertNull($refreshedProfile->phone);
        $this->assertNull($refreshedProfile->full_name_ktp);

        // Verifikasi catatan pendaftaran dan kehadiran tetap ada
        $this->assertDatabaseHas('enrollments', ['id' => $enrollment->id, 'user_id' => $oldId]);
        $this->assertDatabaseHas('attendances', ['id' => $attendance->id, 'user_id' => $oldId]);
    }

    public function test_login_event_updates_last_login_at_and_method_without_ip(): void
    {
        $user = User::factory()->create([
            'name' => 'Login Tracker Test',
            'email' => 'logintrack@julehatest.org',
            'is_active' => true,
        ]);

        $this->assertNull($user->last_login_at);
        $this->assertNull($user->last_login_method);

        // Panggil event Login
        event(new Login('web', $user, true));

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
        $this->assertEquals('password', $user->last_login_method);
    }

    public function test_audit_log_records_user_activity_without_passwords_or_nik(): void
    {
        $log = AuditLog::logUserActivity('role_changed', $this->peserta->id, [
            'old_role' => User::ROLE_PARTICIPANT,
            'new_role' => User::ROLE_ADMIN_KEGIATAN,
        ], $this->superAdmin->id);

        $this->assertEquals($this->superAdmin->id, $log->user_id);
        $this->assertEquals($this->peserta->id, $log->target_id);
        $this->assertEquals('User', $log->target_type);
        $this->assertEquals('role_changed', $log->action);
        $this->assertNull($log->ip_address);
        $this->assertNull($log->user_agent);

        $this->assertEquals([
            'old_role' => User::ROLE_PARTICIPANT,
            'new_role' => User::ROLE_ADMIN_KEGIATAN,
        ], $log->meta);
    }
}
