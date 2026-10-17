<?php

namespace App\Filament\Widgets;

use App\Enums\EnrollmentStatus;
use App\Models\BoardMember;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Post;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class DashboardOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'sm' => 'full',
        'md' => 'full',
        'lg' => 'full',
        'xl' => 'full',
    ];

    protected int|array|null $columns = [
        'default' => 2,
        'sm' => 2,
        'md' => 4,
        'lg' => 4,
        'xl' => 4,
    ];

    protected function getStats(): array
    {
        $user = Auth::user();

        // Untuk Editor Konten: Tampilkan metrik ringkas konten publikasi
        if ($user && ($user->role === 'editor_konten' || $user->role === User::ROLE_CONTENT_EDITOR)) {
            $postCount = Post::count();
            $galleryCount = GalleryItem::count();
            $memberCount = BoardMember::where('is_active', true)->count();
            $faqCount = Faq::where('is_active', true)->count();

            return [
                Stat::make('Artikel Berita', new HtmlString('<span class="stat-number">'.$postCount.'</span> <span class="stat-unit text-xs sm:text-sm font-normal text-gray-500 dark:text-gray-400">artikel</span>'))
                    ->description('Warta & syiar')
                    ->descriptionIcon('heroicon-m-newspaper')
                    ->color('primary')
                    ->url(route('filament.admin.resources.posts.index')),

                Stat::make('Galeri Dokumentasi', new HtmlString('<span class="stat-number">'.$galleryCount.'</span> <span class="stat-unit text-xs sm:text-sm font-normal text-gray-500 dark:text-gray-400">foto</span>'))
                    ->description('Dokumentasi foto')
                    ->descriptionIcon('heroicon-m-camera')
                    ->color('primary')
                    ->url(route('filament.admin.resources.gallery-items.index')),

                Stat::make('Struktur Pengurus', new HtmlString('<span class="stat-number">'.$memberCount.'</span> <span class="stat-unit text-xs sm:text-sm font-normal text-gray-500 dark:text-gray-400">orang</span>'))
                    ->description('Pengurus aktif')
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color('primary')
                    ->url(route('filament.admin.resources.board-members.index')),

                Stat::make('Tanya Jawab (FAQ)', new HtmlString('<span class="stat-number">'.$faqCount.'</span> <span class="stat-unit text-xs sm:text-sm font-normal text-gray-500 dark:text-gray-400">tanya jawab</span>'))
                    ->description('Informasi publik')
                    ->descriptionIcon('heroicon-m-question-mark-circle')
                    ->color('primary')
                    ->url(route('filament.admin.resources.faqs.index')),
            ];
        }

        // Untuk Super Admin dan Admin Kegiatan: Tampilkan metrik kegiatan & peserta
        $pesertaCount = User::participants()->count();
        $pendingCount = Enrollment::where('status', EnrollmentStatus::Menunggu)->count();
        $todayCount = Enrollment::whereDate('created_at', today())->count();

        $openEvents = Event::where('status', 'open')->get()->filter(fn (Event $e) => $e->canRegister());
        $openEventCount = $openEvents->count();
        $totalQuotaRemaining = $openEvents->sum(fn (Event $e) => $e->seatsLeft() ?? 0);

        return [
            Stat::make('Peserta Terdaftar', new HtmlString('<span class="stat-number">'.$pesertaCount.'</span> <span class="stat-unit text-xs sm:text-sm font-normal text-gray-500 dark:text-gray-400">orang</span>'))
                ->description('Akun terdaftar')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->url(route('filament.admin.resources.enrollments.index')),

            Stat::make('Menunggu Diperiksa', new HtmlString('<span class="stat-number">'.$pendingCount.'</span> <span class="stat-unit text-xs sm:text-sm font-normal text-gray-500 dark:text-gray-400">pendaftaran</span>'))
                ->description($pendingCount > 0 ? 'Perlu tindakan' : 'Semua sudah diperiksa')
                ->descriptionIcon('heroicon-m-inbox-stack')
                ->color('primary')
                ->url(route('filament.admin.resources.enrollments.index', ['tableFilters[status][value]' => 'menunggu'])),

            Stat::make('Kegiatan Dibuka', new HtmlString('<span class="stat-number">'.$openEventCount.'</span> <span class="stat-unit text-xs sm:text-sm font-normal text-gray-500 dark:text-gray-400">kegiatan</span>'))
                ->description('Sisa '.$totalQuotaRemaining.' kursi')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary')
                ->url(route('filament.admin.resources.events.index')),

            Stat::make('Pendaftaran Hari Ini', new HtmlString('<span class="stat-number">'.$todayCount.'</span> <span class="stat-unit text-xs sm:text-sm font-normal text-gray-500 dark:text-gray-400">pendaftaran</span>'))
                ->description('Masuk hari ini')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('primary')
                ->url(route('filament.admin.resources.enrollments.index')),
        ];
    }
}
