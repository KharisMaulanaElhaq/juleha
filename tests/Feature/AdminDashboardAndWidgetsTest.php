<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Filament\AvatarProviders\PurpleUiAvatarsProvider;
use App\Filament\Resources\Enrollments\EnrollmentResource;
use App\Filament\Widgets\DashboardOverviewWidget;
use App\Filament\Widgets\PendingRegistrationsWidget;
use App\Filament\Widgets\RegistrationsByEventChartWidget;
use App\Filament\Widgets\TodaySessionsWidget;
use App\Mail\EnrollmentStatusChangedNotification;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\EventSession;
use App\Models\ParticipantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDashboardAndWidgetsTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $adminKegiatan;

    protected User $editorKonten;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@juleha.test',
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $this->adminKegiatan = User::factory()->create([
            'name' => 'Admin Kegiatan',
            'email' => 'kegiatan@juleha.test',
            'role' => User::ROLE_ADMIN_KEGIATAN,
        ]);

        $this->editorKonten = User::factory()->create([
            'name' => 'Editor Konten',
            'email' => 'editor@juleha.test',
            'role' => User::ROLE_EDITOR_KONTEN,
        ]);
    }

    public function test_super_admin_can_access_filament_dashboard(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/admin');
        $response->assertSuccessful();
    }

    public function test_custom_avatar_provider_generates_purple_themed_url(): void
    {
        $provider = new PurpleUiAvatarsProvider;
        $url = $provider->get($this->superAdmin);

        $this->assertStringContainsString('background=7248B8', $url);
        $this->assertStringContainsString('color=FFFFFF', $url);
        $this->assertStringContainsString('name=SA', $url);
    }

    public function test_pending_registrations_widget_can_be_viewed_by_super_admin_and_admin_kegiatan(): void
    {
        $this->actingAs($this->superAdmin);
        $this->assertTrue(PendingRegistrationsWidget::canView());
        $this->assertTrue(TodaySessionsWidget::canView());

        $this->actingAs($this->adminKegiatan);
        $this->assertTrue(PendingRegistrationsWidget::canView());
        $this->assertTrue(TodaySessionsWidget::canView());
    }

    public function test_pending_registrations_widget_is_hidden_from_editor_konten(): void
    {
        $this->actingAs($this->editorKonten);
        $this->assertFalse(PendingRegistrationsWidget::canView());
        $this->assertFalse(TodaySessionsWidget::canView());
        $this->assertFalse(EnrollmentResource::canViewAny());
    }

    public function test_pending_registrations_widget_approve_action_updates_status_and_notifies(): void
    {
        Mail::fake();

        $peserta = User::factory()->create([
            'name' => 'Peserta Uji',
            'email' => 'peserta.uji@juleha.test',
            'role' => User::ROLE_PARTICIPANT,
        ]);

        ParticipantProfile::create([
            'user_id' => $peserta->id,
            'full_name_ktp' => 'Peserta Uji KTP',
            'phone' => '081234567890',
            'nik' => '3329010101900001',
            'district' => 'Brebes',
            'village' => 'Brebes',
            'status' => 'calon_juleha',
            'consent' => true,
        ]);

        $event = Event::create([
            'title' => 'Bimtek JULEHA Brebes 2026',
            'slug' => 'bimtek-juleha-2026',
            'category' => 'Bimtek',
            'status' => 'open',
            'quota' => 50,
            'starts_at' => now()->addDays(3),
            'location' => 'Aula Islamic Centre Brebes',
        ]);

        $enrollment = Enrollment::create([
            'user_id' => $peserta->id,
            'event_id' => $event->id,
            'status' => EnrollmentStatus::Menunggu,
        ]);

        $this->actingAs($this->superAdmin);

        Livewire::test(PendingRegistrationsWidget::class)
            ->callTableAction('approve', $enrollment);

        $enrollment->refresh();

        $this->assertSame(EnrollmentStatus::Aktif, $enrollment->status);
        Mail::assertQueued(EnrollmentStatusChangedNotification::class);
    }

    public function test_pending_registrations_widget_reject_action_updates_status_with_notes(): void
    {
        Mail::fake();

        $peserta = User::factory()->create([
            'name' => 'Peserta Ditolak',
            'email' => 'peserta.tolak@juleha.test',
            'role' => User::ROLE_PARTICIPANT,
        ]);

        $event = Event::create([
            'title' => 'Bimtek Khusus Brebes',
            'slug' => 'bimtek-khusus-brebes',
            'category' => 'Bimtek',
            'status' => 'open',
            'quota' => 30,
            'starts_at' => now()->addDays(5),
            'location' => 'Gedung Korpri Brebes',
        ]);

        $enrollment = Enrollment::create([
            'user_id' => $peserta->id,
            'event_id' => $event->id,
            'status' => EnrollmentStatus::Menunggu,
        ]);

        $this->actingAs($this->superAdmin);

        Livewire::test(PendingRegistrationsWidget::class)
            ->callTableAction('reject', $enrollment, data: [
                'admin_notes' => 'Persyaratan NIK belum sesuai ketentuan.',
            ]);

        $enrollment->refresh();

        $this->assertSame(EnrollmentStatus::Ditolak, $enrollment->status);
        $this->assertSame('Persyaratan NIK belum sesuai ketentuan.', $enrollment->admin_notes);
        Mail::assertQueued(EnrollmentStatusChangedNotification::class);
    }

    public function test_today_sessions_widget_renders_today_sessions(): void
    {
        $event = Event::create([
            'title' => 'Bimtek Hari Ini',
            'slug' => 'bimtek-hari-ini',
            'category' => 'Bimtek',
            'status' => 'open',
            'quota' => 40,
            'starts_at' => now(),
            'location' => 'RPH Jatibarang Brebes',
        ]);

        $session = EventSession::create([
            'event_id' => $event->id,
            'title' => 'Sesi Praktik Sembelih Halal',
            'starts_at' => now()->setTime(8, 30),
            'ends_at' => now()->setTime(11, 30),
            'location' => 'RPH Brebes',
            'attendance_open' => true,
        ]);

        $this->actingAs($this->superAdmin);

        Livewire::test(TodaySessionsWidget::class)
            ->assertCanSeeTableRecords([$session])
            ->assertSee('Sesi Praktik Sembelih Halal')
            ->assertSee('RPH Brebes');
    }

    public function test_dashboard_overview_widget_shows_monochromatic_stats(): void
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(DashboardOverviewWidget::class)
            ->assertSuccessful();

        $this->actingAs($this->editorKonten);

        Livewire::test(DashboardOverviewWidget::class)
            ->assertSuccessful();
    }

    public function test_chart_widget_is_visible_for_admin_and_hidden_from_editor(): void
    {
        $this->actingAs($this->superAdmin);
        $this->assertTrue(RegistrationsByEventChartWidget::canView());

        $this->actingAs($this->adminKegiatan);
        $this->assertTrue(RegistrationsByEventChartWidget::canView());

        $this->actingAs($this->editorKonten);
        $this->assertFalse(RegistrationsByEventChartWidget::canView());
    }

    public function test_chart_widget_returns_empty_when_no_events(): void
    {
        $this->actingAs($this->superAdmin);

        $widget = new RegistrationsByEventChartWidget;
        $this->assertTrue($widget->isEmpty());
    }

    public function test_chart_widget_queries_data_with_integer_status_counts_and_caps_at_10_events(): void
    {
        $this->actingAs($this->superAdmin);

        $peserta = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        // Buat 12 kegiatan (harus dibatasi maksimal 10)
        $events = [];
        for ($i = 1; $i <= 12; $i++) {
            $events[] = Event::create([
                'title' => "Bimtek Angkatan {$i}",
                'slug' => "bimtek-angkatan-{$i}",
                'category' => 'Bimtek',
                'status' => 'open',
                'quota' => 30,
                'starts_at' => now()->addDays($i),
                'location' => 'Brebes',
            ]);
        }

        // Tambah pendaftaran dengan berbagai status ke event 1
        Enrollment::create([
            'user_id' => $peserta->id,
            'event_id' => $events[0]->id,
            'status' => EnrollmentStatus::Aktif,
        ]);
        Enrollment::create([
            'user_id' => User::factory()->create(['role' => User::ROLE_PARTICIPANT])->id,
            'event_id' => $events[0]->id,
            'status' => EnrollmentStatus::Menunggu,
        ]);
        Enrollment::create([
            'user_id' => User::factory()->create(['role' => User::ROLE_PARTICIPANT])->id,
            'event_id' => $events[0]->id,
            'status' => EnrollmentStatus::Ditolak,
        ]);

        $component = Livewire::test(RegistrationsByEventChartWidget::class);
        $component->assertSuccessful();

        $widget = $component->instance();
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('getData');
        $method->setAccessible(true);
        $data = $method->invoke($widget);

        // Maksimal 10 kegiatan
        $this->assertCount(10, $data['labels']);

        // 3 datasets: Diterima, Menunggu diperiksa, Tidak diterima
        $this->assertCount(3, $data['datasets']);
        $this->assertEquals('Diterima', $data['datasets'][0]['label']);
        $this->assertEquals('Menunggu diperiksa', $data['datasets'][1]['label']);
        $this->assertEquals('Tidak diterima', $data['datasets'][2]['label']);

        // Event pertama memiliki 1 aktif, 1 menunggu, 1 ditolak (semua integer)
        $this->assertSame(1, $data['datasets'][0]['data'][0]);
        $this->assertSame(1, $data['datasets'][1]['data'][0]);
        $this->assertSame(1, $data['datasets'][2]['data'][0]);

        // Label format "{nama singkat} · {tanggal}" tanpa "(2/30)"
        $this->assertStringNotContainsString('(2/30)', $data['labels'][0]);
        $this->assertStringNotContainsString('...', $data['labels'][0]);
        $this->assertEquals('2 dari 30 kursi', $data['quotaTexts'][0]);
    }

    public function test_chart_widget_uses_short_title_when_provided(): void
    {
        $this->actingAs($this->superAdmin);

        Event::create([
            'title' => 'Bimbingan Teknis Juru Sembelih Halal Angkatan 14 Wilayah Brebes',
            'short_title' => 'Bimtek Angk. 14',
            'slug' => 'bimtek-angk-14',
            'category' => 'Bimtek',
            'status' => 'open',
            'quota' => 50,
            'starts_at' => now()->addDays(3),
            'location' => 'Brebes',
        ]);

        $component = Livewire::test(RegistrationsByEventChartWidget::class);
        $widget = $component->instance();
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('getData');
        $method->setAccessible(true);
        $data = $method->invoke($widget);

        $this->assertStringStartsWith('Bimtek Angk. 14 · ', $data['labels'][0]);
    }

    public function test_chart_widget_smartly_shortens_verbose_titles(): void
    {
        $this->actingAs($this->superAdmin);

        Event::create([
            'title' => 'Bimtek Juru Sembelih Halal Angkatan 13',
            'slug' => 'bimtek-jsh-angk-13',
            'category' => 'Bimtek',
            'status' => 'open',
            'quota' => 35,
            'starts_at' => now()->addDays(7),
            'location' => 'Brebes',
        ]);

        $component = Livewire::test(RegistrationsByEventChartWidget::class);
        $widget = $component->instance();
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('getData');
        $method->setAccessible(true);
        $data = $method->invoke($widget);

        // Harus mempertahankan bagian akhir "Angk. 13" dan tidak terpotong "..."
        $this->assertStringContainsString('Angk. 13', $data['labels'][0]);
        $this->assertStringNotContainsString('...', $data['labels'][0]);
    }

    public function test_chart_widget_generates_distinct_labels_for_similarly_named_events(): void
    {
        $this->actingAs($this->superAdmin);

        Event::create([
            'title' => 'Bimtek Juru Sembelih Halal Terstandar BNSP',
            'slug' => 'bimtek-jsh-1',
            'category' => 'Bimtek',
            'status' => 'open',
            'quota' => 25,
            'starts_at' => now()->addDays(5),
            'location' => 'Brebes 1',
        ]);

        Event::create([
            'title' => 'Bimtek Juru Sembelih Halal Terstandar BNSP',
            'slug' => 'bimtek-jsh-2',
            'category' => 'Bimtek',
            'status' => 'open',
            'quota' => 25,
            'starts_at' => now()->addDays(12),
            'location' => 'Brebes 2',
        ]);

        $component = Livewire::test(RegistrationsByEventChartWidget::class);
        $widget = $component->instance();
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('getData');
        $method->setAccessible(true);
        $data = $method->invoke($widget);

        $this->assertCount(2, $data['labels']);
        // Label kedua event harus berbeda karena tanggalnya berbeda
        $this->assertNotEquals($data['labels'][0], $data['labels'][1]);
    }
}
