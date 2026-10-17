<x-filament-widgets::widget>
    <div class="quick-actions-box">
        {{-- Judul dan Ikon Sejajar Satu Baris (tanpa ikon petir terpisah di atas) --}}
        <div class="quick-actions-header">
            <x-filament::icon icon="heroicon-o-bolt" class="w-5 h-5 text-[#5a3d9e] dark:text-[#b9a2f0]" />
            <h3 class="quick-actions-title">Aksi Cepat Pengurus</h3>
        </div>

        @php
            $user = auth()->user();
            $isAdminKegiatan = $user && $user->isAdminKegiatan();
        @endphp

        {{-- Grid Tombol: 2 kolom di HP, 4 kolom di laptop --}}
        <div class="quick-actions-grid">
            @if($isAdminKegiatan)
                <!-- 1. Buat Jadwal Bimtek -->
                <a href="{{ route('filament.admin.resources.events.create') }}" class="quick-action-card group">
                    <div class="quick-action-icon-box">
                        <x-filament::icon icon="heroicon-o-calendar-days" class="w-5 h-5" />
                    </div>
                    <div class="quick-action-text-box">
                        <div class="quick-action-item-title">Buat jadwal bimtek</div>
                        <div class="quick-action-item-desc">Buka kegiatan baru</div>
                    </div>
                </a>

                <!-- 2. Unggah Materi -->
                <a href="{{ route('filament.admin.resources.materials.create') }}" class="quick-action-card group">
                    <div class="quick-action-icon-box">
                        <x-filament::icon icon="heroicon-o-arrow-up-tray" class="w-5 h-5" />
                    </div>
                    <div class="quick-action-text-box">
                        <div class="quick-action-item-title">Unggah materi</div>
                        <div class="quick-action-item-desc">Modul & materi bimtek</div>
                    </div>
                </a>
            @endif

            <!-- 3. Tulis Berita -->
            <a href="{{ route('filament.admin.resources.posts.create') }}" class="quick-action-card group">
                <div class="quick-action-icon-box">
                    <x-filament::icon icon="heroicon-o-pencil-square" class="w-5 h-5" />
                </div>
                <div class="quick-action-text-box">
                    <div class="quick-action-item-title">Tulis berita</div>
                    <div class="quick-action-item-desc">Warta & edukasi halal</div>
                </div>
            </a>

            <!-- 4. Unggah Foto -->
            <a href="{{ route('filament.admin.resources.gallery-items.create') }}" class="quick-action-card group">
                <div class="quick-action-icon-box">
                    <x-filament::icon icon="heroicon-o-photo" class="w-5 h-5" />
                </div>
                <div class="quick-action-text-box">
                    <div class="quick-action-item-title">Unggah foto</div>
                    <div class="quick-action-item-desc">Dokumentasi kegiatan</div>
                </div>
            </a>
        </div>
    </div>
</x-filament-widgets::widget>
