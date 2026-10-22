<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Models\AuditLog;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateService
{
    /**
     * Catat atau perbarui hasil kompetensi peserta.
     */
    public static function recordResult(Enrollment $enrollment, array $data, ?int $adminId = null): Certificate
    {
        $result = $data['result'] ?? Certificate::RESULT_KOMPETEN;
        $schemeName = trim($data['scheme_name'] ?? '');

        if (empty($schemeName)) {
            throw ValidationException::withMessages([
                'scheme_name' => 'Nama skema atau unit kompetensi dari LSP wajib diisi.',
            ]);
        }

        $isCompetent = ($result === Certificate::RESULT_KOMPETEN);

        $certNumber = null;
        $issuedAt = null;
        $validUntil = null;

        if ($isCompetent) {
            $certNumber = trim($data['certificate_number'] ?? '');
            if (empty($certNumber)) {
                throw ValidationException::withMessages([
                    'certificate_number' => 'Nomor sertifikat wajib diisi jika hasil asesmen Kompeten.',
                ]);
            }

            // Validasi unik nomor sertifikat (kecuali untuk sertifikat ini sendiri)
            $existing = Certificate::where('certificate_number', $certNumber)
                ->where('enrollment_id', '!=', $enrollment->id)
                ->first();

            if ($existing) {
                throw ValidationException::withMessages([
                    'certificate_number' => 'Nomor sertifikat sudah terdaftar untuk peserta lain.',
                ]);
            }

            if (empty($data['issued_at'])) {
                throw ValidationException::withMessages([
                    'issued_at' => 'Tanggal terbit wajib diisi jika hasil asesmen Kompeten.',
                ]);
            }

            $issuedAt = Carbon::parse($data['issued_at'])->startOfDay();
            if ($issuedAt->isFuture() && ! $issuedAt->isToday()) {
                throw ValidationException::withMessages([
                    'issued_at' => 'Tanggal terbit tidak boleh di masa depan.',
                ]);
            }

            if (! empty($data['valid_until'])) {
                $validUntil = Carbon::parse($data['valid_until'])->startOfDay();
            } else {
                $years = (int) Setting::get('cert_validity_years', 3);
                $validUntil = $issuedAt->copy()->addYears($years);
            }
        }

        $certificate = $enrollment->certificate;
        $isNew = false;
        $oldResult = null;

        if (! $certificate) {
            $isNew = true;
            $certificate = new Certificate;
            $certificate->enrollment_id = $enrollment->id;
            $certificate->user_id = $enrollment->user_id;
            $certificate->event_id = $enrollment->event_id;
            $certificate->issuer_name = Setting::get('cert_issuer_name', 'LSP Pertanian RI');
            $certificate->public_code = Certificate::generateUniquePublicCode();
            $certificate->created_by = $adminId ?? auth()->id();
        } else {
            $oldResult = $certificate->result;
            $certificate->updated_by = $adminId ?? auth()->id();
        }

        $certificate->result = $result;
        $certificate->scheme_name = $schemeName;
        $certificate->certificate_number = $certNumber;
        $certificate->issued_at = $issuedAt;
        $certificate->valid_until = $validUntil;
        $certificate->internal_note = $data['internal_note'] ?? $certificate->internal_note;

        // Jika diubah menjadi belum_kompeten, hapus file PDF jika ada
        if (! $isCompetent && $certificate->file_path) {
            Storage::disk('private')->delete($certificate->file_path);
            $certificate->file_path = null;
            $certificate->file_name = null;
            $certificate->file_size = null;
            $certificate->file_sha256 = null;
        }

        $certificate->save();

        // Audit Log
        if ($isNew) {
            AuditLog::logCertificateActivity('created', $certificate->id, [
                'result' => $result,
                'scheme_name' => $schemeName,
                'certificate_number' => $certNumber,
            ]);
        } elseif ($oldResult !== $result) {
            AuditLog::logCertificateActivity('result_changed', $certificate->id, [
                'old_result' => $oldResult,
                'new_result' => $result,
            ]);
        }

        // Tandai enrollment selesai jika kompeten dan file sudah ada
        if ($isCompetent && $certificate->file_path && $enrollment->status === EnrollmentStatus::Aktif) {
            $enrollment->update(['status' => EnrollmentStatus::Selesai]);
        }

        return $certificate;
    }

    /**
     * Validasi keaslian file PDF berdasarkan Magic Bytes (%PDF-).
     */
    public static function verifyPdfMagicBytes(UploadedFile $file): bool
    {
        $handle = fopen($file->getRealPath(), 'rb');
        if (! $handle) {
            return false;
        }

        $header = fread($handle, 5);
        fclose($handle);

        return $header === '%PDF-';
    }

    /**
     * Unggah atau ganti file PDF sertifikat dari LSP.
     */
    public static function uploadPdf(
        Certificate $certificate,
        UploadedFile $file,
        ?int $adminId = null,
        bool $isReplacement = false
    ): Certificate {
        if (! $certificate->isCompetent()) {
            throw ValidationException::withMessages([
                'file' => 'Sertifikat hanya dapat diunggah jika hasil asesmen dinyatakan Kompeten.',
            ]);
        }

        // Validasi ekstensi dan mime type
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();

        if ($extension !== 'pdf' || ! in_array($mime, ['application/pdf', 'application/x-pdf'], true)) {
            throw ValidationException::withMessages([
                'file' => 'Hanya file dengan format PDF yang diizinkan.',
            ]);
        }

        // Ukuran maksimal 5 MB (5 * 1024 * 1024 bytes)
        if ($file->getSize() > 5 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'file' => 'Ukuran file sertifikat tidak boleh melebihi 5 MB.',
            ]);
        }

        // Validasi Magic Bytes
        if (! self::verifyPdfMagicBytes($file)) {
            throw ValidationException::withMessages([
                'file' => 'File yang diunggah bukan dokumen PDF asli yang valid (magic bytes tidak sesuai).',
            ]);
        }

        $disk = Storage::disk('private');

        // Hapus file lama jika ada penggantian
        if ($certificate->file_path && $disk->exists($certificate->file_path)) {
            $disk->delete($certificate->file_path);
        }

        // Simpan dengan nama acak aman
        $randomFileName = Str::random(40).'.pdf';
        $storedPath = $disk->putFileAs('certificates', $file, $randomFileName);

        $sha256 = hash_file('sha256', $file->getRealPath());

        $certificate->update([
            'file_path' => $storedPath,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'file_sha256' => $sha256,
            'updated_by' => $adminId ?? auth()->id(),
        ]);

        $action = $isReplacement ? 'file_replaced' : 'file_uploaded';
        AuditLog::logCertificateActivity($action, $certificate->id, [
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'file_sha256' => $sha256,
        ]);

        // Tandai enrollment selesai jika pendaftaran masih aktif
        $enrollment = $certificate->enrollment;
        if ($enrollment && $enrollment->status === EnrollmentStatus::Aktif) {
            $enrollment->update(['status' => EnrollmentStatus::Selesai]);
        }

        return $certificate;
    }

    /**
     * Cabut sertifikat yang diterbitkan.
     */
    public static function revoke(Certificate $certificate, string $reason, ?int $adminId = null): Certificate
    {
        $reason = trim($reason);
        if (empty($reason)) {
            throw ValidationException::withMessages([
                'revoked_reason' => 'Alasan pencabutan sertifikat wajib diisi.',
            ]);
        }

        $certificate->update([
            'revoked_at' => now(),
            'revoked_reason' => $reason,
            'updated_by' => $adminId ?? auth()->id(),
        ]);

        AuditLog::logCertificateActivity('revoked', $certificate->id, [
            'reason' => $reason,
        ]);

        return $certificate;
    }

    /**
     * Pulihkan sertifikat yang dicabut.
     */
    public static function restoreRevoked(Certificate $certificate, ?int $adminId = null): Certificate
    {
        $certificate->update([
            'revoked_at' => null,
            'revoked_reason' => null,
            'updated_by' => $adminId ?? auth()->id(),
        ]);

        AuditLog::logCertificateActivity('restored', $certificate->id);

        return $certificate;
    }

    /**
     * Unduh file sertifikat oleh Admin (mencatat log audit).
     */
    public static function downloadForAdmin(Certificate $certificate): StreamedResponse
    {
        $disk = Storage::disk('private');

        if (! $certificate->file_path || ! $disk->exists($certificate->file_path)) {
            abort(404, 'File sertifikat fisik tidak ditemukan di penyimpanan server.');
        }

        AuditLog::logCertificateActivity('downloaded_by_admin', $certificate->id);

        $headers = [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        return $disk->download($certificate->file_path, $certificate->download_file_name, $headers);
    }

    /**
     * Unduh file sertifikat oleh Peserta Pemiliknya.
     */
    public static function downloadForParticipant(Certificate $certificate, User $user): StreamedResponse
    {
        if ($certificate->user_id !== $user->id) {
            abort(403, 'Anda tidak berhak mengakses sertifikat ini.');
        }

        if ($certificate->isRevoked()) {
            abort(403, 'Sertifikat ini telah dicabut oleh pengurus.');
        }

        if (! $certificate->isCompetent()) {
            abort(403, 'Hasil kompetensi belum dinyatakan kompeten.');
        }

        $disk = Storage::disk('private');

        if (! $certificate->file_path || ! $disk->exists($certificate->file_path)) {
            abort(404, 'File sertifikat digital belum diunggah atau tidak tersedia.');
        }

        $headers = [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        return $disk->download($certificate->file_path, $certificate->download_file_name, $headers);
    }
}
