<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Event;
use App\Support\CsvSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceReportController extends Controller
{
    /**
     * Tampilkan matriks rekap kehadiran peserta x sesi untuk satu kegiatan.
     */
    public function matrix(Request $request, Event $event)
    {
        $this->ensureAdmin();

        $sessions = $event->sessions()->orderBy('starts_at')->orderBy('id')->get();
        $enrollments = $event->enrollments()
            ->whereIn('status', [EnrollmentStatus::Aktif, EnrollmentStatus::Selesai])
            ->with(['user.profile'])
            ->get();

        $sessionIds = $sessions->pluck('id');
        $attendances = Attendance::whereIn('event_session_id', $sessionIds)
            ->get()
            ->groupBy(fn ($item) => $item->user_id.'_'.$item->event_session_id);

        return view('pages.admin.attendance-matrix', [
            'event' => $event,
            'sessions' => $sessions,
            'enrollments' => $enrollments,
            'attendances' => $attendances,
        ]);
    }

    /**
     * Ekspor rekap kehadiran ke format CSV/Excel (UTF-8 BOM kompatibel dengan Microsoft Excel).
     */
    public function export(Request $request, Event $event): StreamedResponse
    {
        $this->ensureAdmin();

        $sessions = $event->sessions()->orderBy('starts_at')->orderBy('id')->get();
        $enrollments = $event->enrollments()
            ->whereIn('status', [EnrollmentStatus::Aktif, EnrollmentStatus::Selesai])
            ->with(['user.profile'])
            ->get();

        $sessionIds = $sessions->pluck('id');
        $attendances = Attendance::whereIn('event_session_id', $sessionIds)
            ->get()
            ->groupBy(fn ($item) => $item->user_id.'_'.$item->event_session_id);

        $filename = 'rekap-kehadiran-'.$event->slug.'-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($event, $sessions, $enrollments, $attendances) {
            $handle = fopen('php://output', 'w');

            // Tulis UTF-8 BOM agar Microsoft Excel Windows mengenali huruf dan format teks
            fwrite($handle, "\xEF\xBB\xBF");

            // Header Judul Laporan (disanitasi mencegah formula injection)
            fputcsv($handle, CsvSanitizer::sanitizeRow(['REKAPITULASI KEHADIRAN PESERTA PELATIHAN & BIMTEK']));
            fputcsv($handle, CsvSanitizer::sanitizeRow(['DPD JURU SEMBELIH HALAL (JULEHA) KABUPATEN BREBES']));
            fputcsv($handle, CsvSanitizer::sanitizeRow(['Nama Kegiatan', $event->title]));
            fputcsv($handle, CsvSanitizer::sanitizeRow(['Waktu Pelaksanaan', $event->starts_at?->format('d/m/Y H:i').' WIB']));
            fputcsv($handle, CsvSanitizer::sanitizeRow(['Lokasi', $event->location]));
            fputcsv($handle, CsvSanitizer::sanitizeRow(['Tanggal Ekspor', now()->timezone('Asia/Jakarta')->format('d/m/Y H:i:s').' WIB']));
            fputcsv($handle, []); // Baris Kosong

            // Baris Judul Kolom Matriks (TIDAK memuat NIK demi privasi)
            $columnHeaders = [
                'No',
                'Nama Peserta',
                'Nomor WhatsApp',
                'Kecamatan',
                'Desa / Kelurahan',
            ];

            foreach ($sessions as $idx => $session) {
                $columnHeaders[] = 'Sesi '.($idx + 1).': '.$session->title;
            }

            $columnHeaders[] = 'Total Hadir';
            $columnHeaders[] = 'Total Sesi';
            $columnHeaders[] = 'Persentase (%)';

            fputcsv($handle, CsvSanitizer::sanitizeRow($columnHeaders));

            // Baris Data Tiap Peserta
            $totalSessionCount = $sessions->count();
            $sessionHadirTotals = array_fill(0, $totalSessionCount, 0);

            foreach ($enrollments as $index => $enrollment) {
                $user = $enrollment->user;
                $profile = $user?->profile;

                $row = [
                    $index + 1,
                    $profile?->full_name_ktp ?: ($user?->name ?: '-'),
                    $profile?->phone ? "'".$profile->phone : '-',
                    $profile?->district ?: 'Brebes',
                    $profile?->village ?: '-',
                ];

                $userHadirCount = 0;

                foreach ($sessions as $sIndex => $session) {
                    $key = ($user?->id ?? 0).'_'.$session->id;
                    $att = $attendances->get($key)?->first();

                    if ($att) {
                        $label = $att->status->label();
                        if ($att->isHadir()) {
                            $userHadirCount++;
                            $sessionHadirTotals[$sIndex]++;
                        }
                        $row[] = $label.($att->attended_at ? ' ('.$att->attended_at->timezone('Asia/Jakarta')->format('H:i').')' : '');
                    } else {
                        $row[] = '–';
                    }
                }

                $pct = $totalSessionCount > 0 ? round(($userHadirCount / $totalSessionCount) * 100, 1) : 0;

                $row[] = $userHadirCount;
                $row[] = $totalSessionCount;
                $row[] = $pct.'%';

                fputcsv($handle, CsvSanitizer::sanitizeRow($row));
            }

            // Baris Ringkasan Total Hadir per Sesi
            fputcsv($handle, []);
            $summaryRow = ['', 'TOTAL PESERTA HADIR', '', '', ''];
            foreach ($sessionHadirTotals as $totalHadir) {
                $summaryRow[] = $totalHadir.' orang';
            }
            $summaryRow[] = '';
            $summaryRow[] = '';
            $summaryRow[] = '';
            fputcsv($handle, CsvSanitizer::sanitizeRow($summaryRow));

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function ensureAdmin(): void
    {
        if (! Auth::check() || ! Auth::user()->isAdminKegiatan()) {
            abort(403, 'Akses ditolak. Rekapitulasi kehadiran khusus pengurus/admin kegiatan.');
        }
    }
}
