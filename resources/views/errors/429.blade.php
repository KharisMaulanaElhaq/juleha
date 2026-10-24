@extends('layouts.app')

@section('title', '429 - Terlalu Banyak Percobaan | DPD JULEHA Brebes')

@section('content')
<section class="min-h-[60vh] flex items-center justify-center py-16 px-4">
    <div class="max-w-md w-full text-center space-y-4">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl font-black">
            429
        </div>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white">Terlalu Banyak Percobaan</h1>
        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
            {{ $exception->getMessage() ?: 'Terlalu banyak percobaan, coba lagi nanti.' }}
        </p>
        <div class="pt-4">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition shadow-sm">
                Kembali ke Beranda
            </a>
        </div>
    </div>
</section>
@endsection
