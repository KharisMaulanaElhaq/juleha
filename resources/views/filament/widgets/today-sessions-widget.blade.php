<x-filament-widgets::widget class="fi-wi-table">
    {{-- Tampilan Desktop (md ke atas: tetap menggunakan tabel Filament bawaan) --}}
    <div class="today-sessions-desktop-wrapper">
        {{ $this->table }}
    </div>

    {{-- Tampilan Mobile (di bawah md: tampilan kartu per sesi tanpa scroll horizontal) --}}
    <div class="today-sessions-mobile-wrapper">
        @php
            $sessions = $this->getTodaySessions();
        @endphp

        <div class="today-sessions-card-box">
            {{-- Header Widget Mobile --}}
            <div class="today-sessions-header">
                <div>
                    <h3 class="today-sessions-title">Sesi Hari Ini</h3>
                    <p class="today-sessions-subtitle">Jadwal sesi bimtek dan presensi hari ini</p>
                </div>
                @if($sessions->isNotEmpty())
                    <span class="today-sessions-badge-count">
                        {{ $sessions->count() }} Sesi
                    </span>
                @endif
            </div>

            {{-- Isi Sesi atau Empty State --}}
            @if($sessions->isEmpty())
                <div class="today-sessions-empty">
                    <div class="today-sessions-empty-icon">
                        <x-filament::icon icon="heroicon-o-calendar-days" class="w-6 h-6" />
                    </div>
                    <h4 class="today-sessions-empty-heading">Tidak ada sesi hari ini</h4>
                    <p class="today-sessions-empty-text">
                        Tidak ada sesi pelatihan atau bimtek yang dijadwalkan berlangsung pada hari ini.
                    </p>
                    <div class="today-sessions-empty-action">
                        <a href="{{ route('filament.admin.resources.events.index') }}"
                            class="today-session-btn-screen">
                            <x-filament::icon icon="heroicon-m-calendar" class="w-4 h-4" />
                            <span>Lihat Jadwal Kegiatan</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="today-sessions-list">
                    @foreach($sessions as $session)
                        @php
                            $start = $session->starts_at ? $session->starts_at->timezone('Asia/Jakarta')->format('H:i') : null;
                            $end = $session->ends_at ? $session->ends_at->timezone('Asia/Jakarta')->format('H:i') : null;
                            $timeStr = $start ? ($start . ($end ? ' – ' . $end : '') . ' WIB') : 'Waktu belum diatur';
                        @endphp
                        <div class="today-session-item">
                            {{-- Baris Atas: Judul Sesi & Badge Hadir --}}
                            <div class="today-session-top-row">
                                <div class="today-session-titles">
                                    <h4 class="today-session-name">
                                        {{ $session->title }}
                                    </h4>
                                    <div class="today-session-event">
                                        {{ $session->event?->title ?? 'Kegiatan Bimtek' }}
                                    </div>
                                </div>
                                <span class="today-session-badge-hadir">
                                    {{ $session->hadir_count }} Hadir
                                </span>
                            </div>

                            {{-- Info Waktu dan Lokasi --}}
                            <div class="today-session-meta">
                                <div class="today-session-meta-item">
                                    <x-filament::icon icon="heroicon-o-clock" class="w-3.5 h-3.5" />
                                    <span>{{ $timeStr }}</span>
                                </div>
                                @if($session->location)
                                    <div class="today-session-meta-item">
                                        <x-filament::icon icon="heroicon-o-map-pin" class="w-3.5 h-3.5" />
                                        <span>{{ $session->location }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Dua Tombol Selebar Penuh (Stack Vertikal) --}}
                            <div class="today-session-actions">
                                <a href="{{ route('admin.attendance.screen', $session) }}" target="_blank"
                                    class="today-session-btn-screen">
                                    <x-filament::icon icon="heroicon-m-qr-code" class="w-4 h-4" />
                                    <span>Buka layar absen</span>
                                </a>

                                @if($session->event)
                                    <a href="{{ route('admin.attendance.matrix', $session->event) }}"
                                        class="today-session-btn-rekap">
                                        <x-filament::icon icon="heroicon-m-chart-bar-square" class="w-4 h-4" />
                                        <span>Rekap</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-filament-widgets::widget>
