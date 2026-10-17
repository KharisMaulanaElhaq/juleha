<?php

namespace Database\Seeders;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\EventSession;
use App\Models\ParticipantProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DashboardDemoSeeder extends Seeder
{
    public function run(): void
    {
        $event = Event::firstOrCreate(
            ['slug' => 'bimtek-juleha-2026'],
            [
                'title' => 'Bimtek Juru Sembelih Halal Brebes 2026',
                'type' => 'bimtek',
                'status' => 'open',
                'quota' => 50,
                'starts_at' => now()->setTime(8, 0),
                'ends_at' => now()->setTime(16, 0),
                'location' => 'Gedung Islamic Centre Brebes',
            ]
        );

        $peserta = User::firstOrCreate(
            ['email' => 'peserta.contoh@juleha.test'],
            [
                'name' => 'Ahmad Maulana Syarif',
                'role' => 'peserta',
                'password' => Hash::make('password'),
            ]
        );

        ParticipantProfile::firstOrCreate(
            ['user_id' => $peserta->id],
            [
                'full_name_ktp' => 'Ahmad Maulana Syarif',
                'phone' => '081234567890',
                'nik' => '3329012304950001',
                'district' => 'Brebes',
                'village' => 'Brebes',
                'status' => 'calon_juleha',
                'consent' => true,
            ]
        );

        Enrollment::firstOrCreate(
            ['user_id' => $peserta->id, 'event_id' => $event->id],
            ['status' => EnrollmentStatus::Menunggu]
        );

        EventSession::firstOrCreate(
            ['event_id' => $event->id, 'title' => 'Sesi 1: Fiqih & Anatomi Leher Hewan'],
            [
                'starts_at' => now()->setTime(8, 30),
                'ends_at' => now()->setTime(11, 30),
                'location' => 'Aula Utama Islamic Centre Brebes',
                'attendance_open' => true,
            ]
        );
    }
}
