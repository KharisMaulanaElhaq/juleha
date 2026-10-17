@extends('layouts.portal')

@section('title', 'Materi: ' . $event->title . ' – DPD JULEHA Brebes')
@section('meta_description', 'Materi pembelajaran resmi kelas pelatihan dan bimbingan teknis DPD JULEHA Kabupaten Brebes.')

@section('content')
<div class="space-y-6">
    
    <!-- Top Nav / Back -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-xs sm:text-sm portal-text-muted min-w-0">
            <a href="{{ route('dashboard') }}" class="hover:portal-text flex items-center gap-1 transition">
                <i data-lucide="home" class="w-3.5 h-3.5"></i>
                <span>Beranda</span>
            </a>
            <span>/</span>
            <span class="portal-text truncate font-semibold">{{ $event->title }}</span>
            <span>/</span>
            <span class="portal-text font-semibold">Materi</span>
        </div>

        <a href="{{ route('dashboard') }}#materi"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold portal-surface portal-text hover:bg-slate-50 dark:hover:bg-slate-800 border portal-border transition shadow-xs shrink-0">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Header Ringkasan Kegiatan -->
    <div class="portal-card p-5 sm:p-6 space-y-3">
        <div class="flex flex-wrap items-center gap-2">
            @if($event->type)
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider portal-badge-neutral">
                    {{ $event->type->label() }}
                </span>
            @endif
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold portal-badge-success">
                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                <span>Peserta Terdaftar</span>
            </span>
        </div>

        <div>
            <h1 class="text-xl sm:text-2xl font-heading font-bold portal-text leading-snug">
                {{ $event->title }}
            </h1>
            <p class="text-xs sm:text-sm portal-text-muted mt-0.5">
                Modul resmi, bahan tayang materi, referensi fiqih sembelih, serta panduan praktis pelatihan.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-4 pt-2 border-t portal-border text-xs portal-text-muted">
            @if($event->starts_at)
                <span class="flex items-center gap-1.5">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                    <span>{{ $event->starts_at->translatedFormat('l, d F Y') }}</span>
                </span>
            @endif
            @if($event->location)
                <span class="flex items-center gap-1.5">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                    <span>{{ $event->location }}</span>
                </span>
            @endif
            <span class="flex items-center gap-1.5">
                <i data-lucide="files" class="w-3.5 h-3.5"></i>
                <span>{{ $materials->count() }} Materi Tersedia</span>
            </span>
        </div>
    </div>

    <!-- Daftar Materi Pembelajaran -->
    <div class="space-y-3">
        <h2 class="text-base sm:text-lg font-bold portal-text flex items-center gap-2">
            <i data-lucide="files" class="w-4 h-4 portal-text-muted"></i>
            <span>Daftar Modul & Bahan Pelatihan</span>
        </h2>

        @if($materials->isEmpty())
            <div class="portal-card p-8 text-center text-xs sm:text-sm portal-text-muted space-y-1">
                <i data-lucide="folder-open" class="w-8 h-8 mx-auto portal-text-muted mb-2"></i>
                <p class="font-semibold portal-text">Belum Ada Materi yang Diterbitkan</p>
                <p class="text-[11px] portal-text-muted">Panitia sedang menyiapkan bahan tayang pelatihan untuk angkatan ini.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($materials as $index => $item)
                    <div class="portal-card p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        
                        <!-- Left: Info & Icon -->
                        <div class="flex items-start gap-3.5 flex-1 min-w-0">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 portal-icon-box">
                                @if($item->isPdf())
                                    <i data-lucide="file-text" class="w-5 h-5"></i>
                                @elseif($item->isVideo())
                                    <i data-lucide="video" class="w-5 h-5"></i>
                                @elseif($item->isLink())
                                    <i data-lucide="external-link" class="w-5 h-5"></i>
                                @else
                                    <i data-lucide="file" class="w-5 h-5"></i>
                                @endif
                            </div>

                            <div class="space-y-1 min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-[11px] font-bold portal-text-muted">
                                        #{{ $loop->iteration }}
                                    </span>
                                    @if(in_array($item->id, $downloadedMaterialIds, true))
                                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold portal-badge-success px-2 py-0.2 rounded">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                            Sudah Diakses
                                        </span>
                                    @endif
                                </div>

                                <h3 class="font-bold text-sm sm:text-base portal-text leading-snug">
                                    {{ $item->title }}
                                </h3>

                                @if(filled($item->description))
                                    <p class="text-xs portal-text-muted leading-relaxed line-clamp-2">
                                        {{ $item->description }}
                                    </p>
                                @endif

                                <div class="text-[11px] portal-text-muted flex items-center gap-2 pt-0.5">
                                    @if($item->isFile())
                                        <span class="font-mono uppercase">{{ $item->extension }}</span>
                                        @if($item->size)
                                            <span>&bull;</span>
                                            <span>{{ $item->formatted_size }}</span>
                                        @endif
                                    @else
                                        <span>Tautan Materi</span>
                                    @endif
                                    @if($item->published_at)
                                        <span>&bull;</span>
                                        <span>{{ $item->published_at->translatedFormat('d M Y') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Right: Actions -->
                        <div class="flex items-center gap-2 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 portal-border">
                            @if($item->isPdf())
                                <a href="{{ route('participant.material.preview', [$event->slug, $item->id]) }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="h-9 px-3.5 rounded-xl text-xs font-semibold portal-btn-outline inline-flex items-center gap-1.5 transition">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    <span>Buka PDF</span>
                                </a>
                            @endif

                            @if($item->isFile())
                                <a href="{{ route('participant.material.download', [$event->slug, $item->id]) }}"
                                   class="h-9 px-3.5 rounded-xl text-xs font-semibold portal-btn-primary inline-flex items-center gap-1.5 shadow-xs transition">
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                    <span>Unduh File</span>
                                </a>
                            @else
                                <a href="{{ $item->external_url }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="h-9 px-3.5 rounded-xl text-xs font-semibold portal-btn-primary inline-flex items-center gap-1.5 shadow-xs transition">
                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                    <span>Buka Tautan</span>
                                </a>
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
