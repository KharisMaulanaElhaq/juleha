<?php

namespace App\Http\Controllers;

use App\Mail\DataRequestSubmittedMail;
use App\Models\AuditLog;
use App\Models\DataRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class DataRequestController extends Controller
{
    /**
     * Tampilkan formulir publik pengajuan hak data pribadi (/hak-data).
     */
    public function create(Request $request): View
    {
        $selectedType = $request->query('type', DataRequest::TYPE_ACCESS);
        if (! array_key_exists($selectedType, DataRequest::typeLabels())) {
            $selectedType = DataRequest::TYPE_ACCESS;
        }

        $slaDays = (int) setting('privacy_request_sla_days', 3);

        return view('pages.hak-data', [
            'types' => DataRequest::typeLabels(),
            'selectedType' => $selectedType,
            'slaDays' => $slaDays,
            'privacyEmail' => setting('privacy_contact_email') ?: setting('admin_email', 'dpdjulehabrebesofficial@gmail.com'),
            'privacyOfficer' => setting('privacy_officer_name', 'Sekretariat DPD JULEHA Brebes'),
            'isPortal' => false,
        ]);
    }

    /**
     * Tampilkan formulir pengajuan hak data pada portal peserta (/portal/hak-data).
     */
    public function portalCreate(Request $request): View
    {
        $user = Auth::user();
        $selectedType = $request->query('type', DataRequest::TYPE_ACCESS);
        if (! array_key_exists($selectedType, DataRequest::typeLabels())) {
            $selectedType = DataRequest::TYPE_ACCESS;
        }

        $slaDays = (int) setting('privacy_request_sla_days', 3);

        return view('pages.participant.hak-data-portal', [
            'user' => $user,
            'profile' => $user->profile,
            'types' => DataRequest::typeLabels(),
            'selectedType' => $selectedType,
            'slaDays' => $slaDays,
            'privacyEmail' => setting('privacy_contact_email') ?: setting('admin_email', 'dpdjulehabrebesofficial@gmail.com'),
            'privacyOfficer' => setting('privacy_officer_name', 'Sekretariat DPD JULEHA Brebes'),
            'isPortal' => true,
        ]);
    }

    /**
     * Simpan pengajuan hak data pribadi dengan proteksi honeypot dan rate limit.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Honeypot check: jika kolom website_url terisi, spambot terperangkap
        if (filled($request->input('website_url'))) {
            // Berikan respons sukses palsu agar bot tidak mengulangi permintaan
            return back()->with('success', 'Permintaan Anda telah tercatat dalam sistem kami.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact' => ['required', 'string', 'max:255'],
            'request_type' => ['required', 'string', 'in:akses,koreksi,hapus,tarik_persetujuan,keberatan,portabilitas'],
            'description' => ['required', 'string', 'min:10', 'max:3000'],
        ], [
            'name.required' => 'Nama lengkap pemohon wajib diisi.',
            'contact.required' => 'Kontak pemohon (nomor WhatsApp atau email aktif) wajib diisi.',
            'request_type.required' => 'Pilih jenis hak data yang diajukan.',
            'description.required' => 'Uraikan rincian permintaan hak data Anda.',
            'description.min' => 'Uraian permintaan minimal 10 karakter.',
        ]);

        $slaDays = (int) setting('privacy_request_sla_days', 3);
        if ($slaDays <= 0) {
            $slaDays = 3;
        }

        $dataRequest = DataRequest::create([
            'user_id' => Auth::id(),
            'name' => trim($validated['name']),
            'contact' => trim($validated['contact']),
            'request_type' => $validated['request_type'],
            'description' => trim($validated['description']),
            'status' => DataRequest::STATUS_NEW,
            'due_at' => now()->addDays($slaDays),
        ]);

        AuditLog::log('data_request_submitted', 'DataRequest', $dataRequest->id, [
            'request_number' => $dataRequest->request_number,
            'type' => $dataRequest->request_type,
        ]);

        // Kirim konfirmasi email jika kontak memuat email
        try {
            if (str_contains($dataRequest->contact, '@')) {
                Mail::to($dataRequest->contact)->send(new DataRequestSubmittedMail($dataRequest));
            }

            // Notifikasi ke petugas privasi / admin
            $adminEmail = setting('privacy_contact_email') ?: setting('admin_email');
            if ($adminEmail && $adminEmail !== $dataRequest->contact) {
                Mail::to($adminEmail)->send(new DataRequestSubmittedMail($dataRequest));
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirimkan email tanda terima hak data: '.$e->getMessage());
        }

        $message = "Permohonan hak data Anda berhasil diajukan dengan Nomor Registrasi: {$dataRequest->request_number}. Batas waktu tindak lanjut: {$slaDays} hari kalender.";

        if ($request->input('is_portal')) {
            return redirect()->route('participant.privacy.index')->with('success', $message);
        }

        return redirect()->route('data-rights.create')->with('success', $message);
    }
}
