@extends('layouts.app')

@section('title', 'Galeri Dokumentasi Kegiatan – DPD JULEHA Kabupaten Brebes')
@section('meta_description', 'Dokumentasi kegiatan pelatihan bimbingan teknis, workshop pengasahan bilah, dan pendampingan sembelih kurban DPD JULEHA Brebes.')

@section('content')
<!-- ===================== HERO GALERI ===================== -->
<section class="relative py-20" style="background: var(--hero-gradient);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <span class="inline-flex items-center gap-2 bg-brand/20 text-brand-highlight border border-brand-light/30 text-xs font-semibold px-4 py-2 rounded-full uppercase tracking-wide mb-4" data-aos="fade-up">
            <i data-lucide="image" class="w-3.5 h-3.5"></i>
            Dokumentasi
        </span>
        <h1 class="font-heading font-extrabold text-3xl sm:text-5xl text-white mb-4" data-aos="fade-up" data-aos-delay="100">
            Galeri Kegiatan JULEHA Brebes
        </h1>
        <p class="text-slate-200 max-w-2xl mx-auto text-base sm:text-lg leading-relaxed" data-aos="fade-up" data-aos-delay="200">
            Kilas balik bimbingan teknis, workshop manajemen bilah, dan bakti pengawasan hewan kurban di berbagai penjuru Kabupaten Brebes.
        </p>
    </div>
</section>

<!-- ===================== GRID GALERI DOKUMENTASI ===================== -->
<section class="section-padding bg-white dark:bg-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse ($items as $item)
            @php
                $img = $item->image ?? $item->image_path;
                $desc = $item->caption ?? $item->description;
            @endphp
            <div class="card-juleha overflow-hidden p-0" data-aos="fade-up">
                @if ($img && (str_starts_with($img, 'http') || str_starts_with($img, '/')))
                    <div class="h-56 overflow-hidden bg-slate-900">
                        <img src="{{ $img }}" alt="{{ $item->title }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300" />
                    </div>
                @elseif ($img && Storage::disk('public')->exists($img))
                    <div class="h-56 overflow-hidden bg-slate-900">
                        <img src="{{ asset('storage/' . $img) }}" alt="{{ $item->title }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300" />
                    </div>
                @else
                    <div class="h-56 bg-gradient-to-br from-brand-hero/40 to-brand-hero-dark flex items-center justify-center p-6 text-center border-b border-gray-100 dark:border-slate-800">
                        <div class="space-y-2">
                            <div class="w-12 h-12 mx-auto rounded-full bg-white/10 flex items-center justify-center text-brand-highlight">
                                <i data-lucide="image" class="w-6 h-6"></i>
                            </div>
                            <span class="text-xs font-bold uppercase tracking-wider text-brand-highlight block">{{ $item->category ?? 'Dokumentasi' }}</span>
                            <div class="text-xs text-slate-300">{{ $item->title }}</div>
                        </div>
                    </div>
                @endif
                <div class="p-5">
                    <span class="text-xs font-semibold text-brand-accent dark:text-brand-dark-accent uppercase tracking-wider mb-1 block">{{ $item->category }}</span>
                    <h4 class="font-heading font-bold text-base text-slate-800 dark:text-white mb-1">{{ $item->title }}</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">{{ $desc }}</p>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-16 bg-slate-50 dark:bg-slate-800 rounded-3xl p-8 border border-gray-100 dark:border-slate-700">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-brand-light dark:bg-brand-hero-dark/60 text-brand dark:text-brand-dark flex items-center justify-center">
                    <i data-lucide="image" class="w-8 h-8"></i>
                </div>
                <h3 class="font-heading font-bold text-xl text-slate-800 dark:text-white mb-2">Belum Ada Dokumentasi Galeri</h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm max-w-md mx-auto">
                    Foto-foto kegiatan dan bimbingan teknis terbaru akan segera ditampilkan di halaman ini.
                </p>
            </div>
            @endforelse
        </div>
    </div>
</section>

<!-- ===================== SECTION CTA ===================== -->
<x-cta variant="strip" />
@endsection
