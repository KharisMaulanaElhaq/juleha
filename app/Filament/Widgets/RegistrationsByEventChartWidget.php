<?php

namespace App\Filament\Widgets;

use App\Enums\EnrollmentStatus;
use App\Models\Event;
use Filament\Actions\Action;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class RegistrationsByEventChartWidget extends ChartWidget
{
    protected static ?int $sort = 6;

    protected ?string $heading = 'Pendaftaran per kegiatan';

    protected bool $isCollapsible = true;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'sm' => 'full',
        'md' => 'full',
        'lg' => 'full',
        'xl' => 'full',
    ];

    public ?string $filter = 'open';

    protected ?string $emptyStateHeading = 'Belum ada pendaftaran';

    protected ?string $emptyStateDescription = 'Belum ada pendaftaran kegiatan bimtek yang tercatat di sistem.';

    protected ?array $fullTitles = [];

    protected ?array $quotaInfo = [];

    public static function canView(): bool
    {
        $user = Auth::user();

        // Hanya untuk super_admin dan admin_kegiatan
        return $user && $user->isAdminKegiatan();
    }

    public function getMaxHeight(): ?string
    {
        $data = $this->getCachedData();
        $count = count($data['labels'] ?? []);

        if ($count <= 0) {
            return '200px';
        }

        // Tinggi grafik menyesuaikan jumlah kegiatan (56-64px per baris, minimal 200px)
        $height = max(200, ($count * 60) + 60);

        return "{$height}px";
    }

    protected function getFilters(): ?array
    {
        return [
            'open' => 'Hanya yang dibuka',
            'all' => 'Semua kegiatan · 90 hari terakhir',
        ];
    }

    public function getEmptyStateActions(): array
    {
        return [
            Action::make('jadwal')
                ->label('Lihat Jadwal Bimtek')
                ->url(route('filament.admin.resources.events.index'))
                ->button()
                ->color('primary')
                ->icon('heroicon-m-calendar'),
        ];
    }

    public function isEmpty(): bool
    {
        $data = $this->getCachedData();

        return empty($data) || empty($data['labels']);
    }

    /**
     * Potong judul secara cerdas dengan mempertahankan bagian akhir yang membedakan.
     */
    public static function formatShortTitle(string $title, ?string $shortTitle = null, int $maxLength = 28): string
    {
        if (! empty($shortTitle)) {
            return trim($shortTitle);
        }

        $title = trim($title);
        if (mb_strlen($title) <= $maxLength) {
            return $title;
        }

        // Sederhanakan prefix umum yang panjang
        $replacements = [
            '/^Bimbingan\s+Teknis\s+Juru\s+Sembelih\s+Halal\b/iu' => 'Bimtek',
            '/^Bimtek\s+Juru\s+Sembelih\s+Halal\b/iu' => 'Bimtek',
            '/^Pelatihan\s+Juru\s+Sembelih\s+Halal\b/iu' => 'Pelatihan',
            '/^Workshop\s+Juru\s+Sembelih\s+Halal\b/iu' => 'Workshop',
            '/^Sosialisasi\s+Juru\s+Sembelih\s+Halal\b/iu' => 'Sosialisasi',
            '/^Juru\s+Sembelih\s+Halal\b/iu' => 'JULEHA',
            '/\bKabupaten\s+Brebes\b/iu' => 'Brebes',
            '/\bKab\.\s*Brebes\b/iu' => 'Brebes',
            '/\bAngkatan\s+/iu' => 'Angk. ',
        ];

        $cleaned = preg_replace(array_keys($replacements), array_values($replacements), $title);
        $cleaned = preg_replace('/\s+/', ' ', trim($cleaned));

        if (mb_strlen($cleaned) <= $maxLength) {
            return $cleaned;
        }

        // Ambil jenis kegiatan di awal dan pertahankan kata-kata pembeda di ujung
        $words = explode(' ', $cleaned);
        if (count($words) >= 3) {
            $first = $words[0];
            $tail = [];
            for ($i = count($words) - 1; $i >= 1; $i--) {
                array_unshift($tail, $words[$i]);
                $test = $first.' '.implode(' ', $tail);
                if (mb_strlen($test) > $maxLength) {
                    array_shift($tail);
                    break;
                }
            }
            if (! empty($tail)) {
                return $first.' '.implode(' ', $tail);
            }
        }

        return mb_substr($cleaned, -$maxLength);
    }

    protected function getData(): array
    {
        $query = Event::query();

        if ($this->filter === 'all') {
            $query->where(function ($q) {
                $q->where('status', 'open')
                    ->orWhere('ends_at', '>=', now()->subDays(90));
            });
        } else {
            $query->where('status', 'open');
        }

        // Urutkan dari tanggal terdekat, maksimal 10 kegiatan
        $events = $query
            ->with(['enrollments'])
            ->orderByRaw('starts_at IS NULL, starts_at ASC')
            ->take(10)
            ->get();

        if ($events->isEmpty()) {
            $this->fullTitles = [];
            $this->quotaInfo = [];

            return [];
        }

        $labels = [];
        $diterimaData = [];
        $menungguData = [];
        $ditolakData = [];
        $quotaTexts = [];
        $fullTitles = [];

        foreach ($events as $event) {
            $short = static::formatShortTitle($event->title, $event->short_title);
            $dateStr = $event->starts_at ? ' · '.$event->starts_at->translatedFormat('d M') : '';
            $labels[] = $short.$dateStr;

            $aktifCount = (int) $event->enrollments->where('status', EnrollmentStatus::Aktif)->count();
            $menungguCount = (int) $event->enrollments->where('status', EnrollmentStatus::Menunggu)->count();
            $ditolakCount = (int) $event->enrollments->where('status', EnrollmentStatus::Ditolak)->count();

            $totalActive = $aktifCount + $menungguCount;
            $quotaText = $event->quota ? "{$totalActive} dari {$event->quota} kursi" : "{$totalActive} pendaftar";

            $diterimaData[] = $aktifCount;
            $menungguData[] = $menungguCount;
            $ditolakData[] = $ditolakCount;
            $quotaTexts[] = $quotaText;
            $fullTitles[] = $event->title;
        }

        $this->fullTitles = $fullTitles;
        $this->quotaInfo = $quotaTexts;

        return [
            'datasets' => [
                [
                    'label' => 'Diterima',
                    'data' => $diterimaData,
                    'backgroundColor' => '#5a3d9e',
                    'borderColor' => '#482f80',
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                    'maxBarThickness' => 28,
                    'barPercentage' => 0.65,
                ],
                [
                    'label' => 'Menunggu diperiksa',
                    'data' => $menungguData,
                    'backgroundColor' => '#b9a2f0',
                    'borderColor' => '#9333ea',
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                    'maxBarThickness' => 28,
                    'barPercentage' => 0.65,
                ],
                [
                    'label' => 'Tidak diterima',
                    'data' => $ditolakData,
                    'backgroundColor' => '#94a3b8',
                    'borderColor' => '#64748b',
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                    'maxBarThickness' => 28,
                    'barPercentage' => 0.65,
                ],
            ],
            'labels' => $labels,
            'quotaTexts' => $quotaTexts,
            'fullTitles' => $fullTitles,
        ];
    }

    protected function getOptions(): array
    {
        $cachedData = $this->getCachedData();
        $datasets = $cachedData['datasets'] ?? [];
        $totals = [];
        $rowCount = count($cachedData['labels'] ?? []);

        for ($i = 0; $i < $rowCount; $i++) {
            $sum = 0;
            foreach ($datasets as $ds) {
                $sum += ($ds['data'][$i] ?? 0);
            }
            $totals[] = $sum;
        }

        $maxTotal = ! empty($totals) ? max($totals) : 0;
        // Beri ruang di ujung (suggestedMax = nilai tertinggi + 1) agar batang tidak menempel di tepi
        $suggestedMax = max(2, $maxTotal + 1);

        return [
            'indexAxis' => 'y',
            'responsive' => true,
            'maintainAspectRatio' => false,
            'barPercentage' => 0.65,
            'categoryPercentage' => 0.8,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'align' => 'center',
                    'labels' => [
                        'boxWidth' => 12,
                        'boxHeight' => 12,
                        'padding' => 10,
                        'font' => [
                            'size' => 11,
                            'weight' => '600',
                        ],
                    ],
                ],
                'tooltip' => [
                    'enabled' => true,
                    'mode' => 'index',
                    'intersect' => false,
                    'padding' => 10,
                    'cornerRadius' => 8,
                ],
            ],
            'scales' => [
                'x' => [
                    'stacked' => true,
                    'beginAtZero' => true,
                    'suggestedMax' => $suggestedMax,
                    'ticks' => [
                        'precision' => 0,
                        'stepSize' => 1,
                    ],
                    'grid' => [
                        'drawBorder' => false,
                    ],
                ],
                'y' => [
                    'stacked' => true,
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'autoSkip' => false,
                        'font' => [
                            'size' => 11,
                            'weight' => '500',
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
