<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ParticipantProfile;
use App\Rules\ValidNik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Daftar 17 Kecamatan resmi di Kabupaten Brebes.
     */
    public const BREBES_DISTRICTS = [
        'Banjarharjo',
        'Bantarkawung',
        'Brebes',
        'Bulakamba',
        'Bumiayu',
        'Jatibarang',
        'Kersana',
        'Ketanggungan',
        'Larangan',
        'Losari',
        'Paguyangan',
        'Salem',
        'Sirampog',
        'Songgom',
        'Tanjung',
        'Tonjong',
        'Wanasari',
    ];

    /**
     * Tampilkan formulir lengkapi / ubah profil peserta (mendukung alur 2 langkah).
     */
    public function edit(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user && $user->isStaff()) {
            return redirect()->to('/admin')->with('info', 'Anda masuk sebagai pengurus. Panel pengurus ada di sini.');
        }

        $profile = ParticipantProfile::where('user_id', $user->id)->first();

        $myAttendances = Attendance::where('user_id', $user->id)
            ->with(['session.event'])
            ->latest('attended_at')
            ->get();

        // Tentukan langkah aktif:
        // 1. Jika query string ?step=1 atau ?step=2 spesifik
        // 2. Jika profil sudah ada kontak & domisili tapi NIK kosong, arahkan langsung ke Langkah 2
        // 3. Default ke Langkah 1
        $requestedStep = (int) $request->query('step', 0);
        if (in_array($requestedStep, [1, 2], true)) {
            $currentStep = $requestedStep;
        } elseif ($profile && filled($profile->phone) && filled($profile->district) && blank($profile->nik)) {
            $currentStep = 2;
        } else {
            $currentStep = 1;
        }

        $isCompleted = $profile && filled($profile->nik) && filled($profile->phone);

        return view('pages.participant.profile', [
            'user' => $user,
            'profile' => $profile,
            'districts' => self::BREBES_DISTRICTS,
            'isNikLocked' => $profile && filled($profile->nik) && $user->enrollments()->where('status', 'selesai')->exists(),
            'myAttendances' => $myAttendances,
            'currentStep' => $currentStep,
            'isCompleted' => $isCompleted,
        ]);
    }

    /**
     * Simpan pembaruan profil peserta (mendukung simpan per langkah 1 & 2 serta simpan penuh).
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user && $user->isStaff()) {
            return redirect()->to('/admin')->with('info', 'Anda masuk sebagai pengurus. Panel pengurus ada di sini.');
        }

        $profile = ParticipantProfile::where('user_id', $user->id)->first();
        $step = $request->input('step');

        // ==========================================
        // ALUR LANGKAH 1: Kontak & Wilayah Domisili
        // ==========================================
        if ($step == '1') {
            $validated = $request->validate([
                'phone' => ['required', 'string', 'max:20'],
                'district' => ['required', 'string', 'max:100'],
                'village' => ['required', 'string', 'max:100'],
                'institution' => ['nullable', 'string', 'max:255'],
                'status' => ['required', 'string', 'in:calon_juleha,juleha_aktif,pengurus_masjid,pekerja_rph'],
            ], [
                'phone.required' => 'Nomor WhatsApp aktif wajib diisi.',
                'district.required' => 'Pilih kecamatan domisili Anda di Kabupaten Brebes.',
                'village.required' => 'Nama desa atau kelurahan domisili wajib diisi.',
                'status.required' => 'Pilih status profesi atau peran Anda saat ini.',
            ]);

            $normalizedPhone = ParticipantProfile::normalizePhone($validated['phone']);

            // Cek duplikasi nomor telepon pada peserta lain
            $phoneExists = ParticipantProfile::where('phone', $normalizedPhone)
                ->where('user_id', '!=', $user->id)
                ->exists();

            if ($phoneExists) {
                return back()->withInput()->withErrors([
                    'phone' => 'Nomor WhatsApp ini telah terdaftar pada akun peserta lain.',
                ]);
            }

            $step1Data = [
                'phone' => $normalizedPhone,
                'district' => trim($validated['district']),
                'village' => trim($validated['village']),
                'institution' => filled($validated['institution'] ?? null) ? trim($validated['institution']) : null,
                'status' => $validated['status'],
            ];

            if ($profile) {
                $profile->update($step1Data);
            } else {
                $step1Data['full_name_ktp'] = $user->name;
                $user->profile()->create($step1Data);
            }

            return redirect()->route('participant.profile', ['step' => 2])
                ->with('success', 'Data kontak dan domisili berhasil disimpan. Lanjutkan ke langkah 2.');
        }

        // ==========================================
        // ALUR LANGKAH 2: Data untuk Sertifikat & NIK
        // ==========================================
        if ($step == '2') {
            $isNikLocked = $profile && filled($profile->nik) && $user->enrollments()->where('status', 'selesai')->exists();

            $rules = [
                'full_name_ktp' => ['required', 'string', 'max:255'],
                'consent' => ['required', 'accepted'],
            ];

            if (! $isNikLocked) {
                $rules['nik'] = ['required', 'string', new ValidNik];
            }

            $validated = $request->validate($rules, [
                'full_name_ktp.required' => 'Nama lengkap sesuai KTP wajib diisi.',
                'consent.accepted' => 'Anda wajib mencentang persetujuan penggunaan data pribadi untuk pencetakan sertifikat.',
                'nik.required' => 'NIK harus 16 angka untuk pencetakan sertifikat.',
            ]);

            $step2Data = [
                'full_name_ktp' => trim($validated['full_name_ktp']),
                'consent' => true,
                'consent_at' => now(),
            ];

            if (! $isNikLocked && filled($validated['nik'] ?? null)) {
                $rawNik = trim($validated['nik']);
                $nikHash = ParticipantProfile::hashNik($rawNik);

                $nikExists = ParticipantProfile::where('nik_hash', $nikHash)
                    ->where('user_id', '!=', $user->id)
                    ->exists();

                if ($nikExists) {
                    return back()->withInput()->withErrors([
                        'nik' => 'NIK ini sudah terdaftar pada akun peserta lain. Satu NIK hanya berlaku untuk satu akun resmi.',
                    ]);
                }

                $step2Data['nik'] = $rawNik;
                $step2Data['nik_hash'] = $nikHash;
            }

            if ($profile) {
                $profile->update($step2Data);
            } else {
                $step2Data['phone'] = ParticipantProfile::normalizePhone($user->phone ?? '080000000000');
                $step2Data['district'] = 'Brebes';
                $step2Data['village'] = 'Brebes';
                $step2Data['status'] = 'calon_juleha';
                $user->profile()->create($step2Data);
            }

            return redirect()->intended(route('dashboard'))->with('success', 'Profil peserta berhasil dilengkapi.');
        }

        // ==========================================
        // ALUR LENGKAP SEKADARNYA (COMPATIBILITY)
        // ==========================================
        $isNikLocked = $profile && filled($profile->nik) && $user->enrollments()->where('status', 'selesai')->exists();

        $rules = [
            'full_name_ktp' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'district' => ['required', 'string', 'max:100'],
            'village' => ['required', 'string', 'max:100'],
            'institution' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:calon_juleha,juleha_aktif,pengurus_masjid,pekerja_rph'],
            'consent' => ['required', 'accepted'],
        ];

        // Jika NIK belum pernah diisi atau belum terkunci sertifikat, wajib validasi NIK
        if (! $isNikLocked) {
            $rules['nik'] = ['required', 'string', new ValidNik];
        }

        $validated = $request->validate($rules, [
            'full_name_ktp.required' => 'Nama lengkap sesuai KTP wajib diisi.',
            'phone.required' => 'Nomor WhatsApp aktif wajib diisi.',
            'district.required' => 'Pilih kecamatan domisili Anda.',
            'village.required' => 'Nama desa atau kelurahan wajib diisi.',
            'status.required' => 'Pilih status profesi/peran Anda saat ini.',
            'consent.accepted' => 'Anda wajib menyetujui pernyataan persetujuan penggunaan data pribadi untuk penerbitan sertifikat.',
            'nik.required' => 'NIK harus 16 angka untuk pencetakan sertifikat.',
        ]);

        $normalizedPhone = ParticipantProfile::normalizePhone($validated['phone']);

        // Cek duplikasi nomor telepon pada peserta lain
        $phoneExists = ParticipantProfile::where('phone', $normalizedPhone)
            ->where('user_id', '!=', $user->id)
            ->exists();

        if ($phoneExists) {
            return back()->withInput()->withErrors([
                'phone' => 'Nomor WhatsApp ini telah terdaftar pada akun peserta lain.',
            ]);
        }

        $profileData = [
            'full_name_ktp' => trim($validated['full_name_ktp']),
            'phone' => $normalizedPhone,
            'district' => trim($validated['district']),
            'village' => trim($validated['village']),
            'institution' => filled($validated['institution'] ?? null) ? trim($validated['institution']) : null,
            'status' => $validated['status'],
            'consent' => true,
            'consent_at' => now(),
        ];

        if (! $isNikLocked && filled($validated['nik'] ?? null)) {
            $rawNik = trim($validated['nik']);
            $nikHash = ParticipantProfile::hashNik($rawNik);

            // Cek duplikasi NIK via hash HMAC tanpa membocorkan NIK mentah
            $nikExists = ParticipantProfile::where('nik_hash', $nikHash)
                ->where('user_id', '!=', $user->id)
                ->exists();

            if ($nikExists) {
                return back()->withInput()->withErrors([
                    'nik' => 'NIK ini sudah terdaftar pada akun peserta lain. Satu NIK hanya berlaku untuk satu akun resmi.',
                ]);
            }

            $profileData['nik'] = $rawNik;
            $profileData['nik_hash'] = $nikHash;
        }

        if ($profile) {
            $profile->update($profileData);
        } else {
            $user->profile()->create($profileData);
        }

        return redirect()->intended(route('dashboard'))->with('success', 'Profil peserta berhasil diperbarui.');
    }
}
