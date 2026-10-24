@extends('layouts.portal')

@section('title', 'Ajukan Permohonan Hak Data – Portal Peserta DPD JULEHA Brebes')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <div class="flex items-center justify-between pb-6 border-b border-purple-900/30">
        <div>
            <a href="{{ route('participant.privacy.index') }}" class="inline-flex items-center gap-1.5 text-xs text-purple-400 hover:underline mb-2">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Kembali ke Privasi dan Data Saya
            </a>
            <h1 class="text-2xl sm:text-3xl font-heading font-extrabold text-white">
                Ajukan Permohonan Hak Data Pribadi
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 mt-1">
                Layanan pelaksanaan hak subjek data sesuai UU No. 27 Tahun 2022 (UU Perlindungan Data Pribadi).
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-950/40 border border-emerald-800/60 text-emerald-300 text-xs sm:text-sm flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400 flex-shrink-0"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <div class="grid md:grid-cols-12 gap-8 items-start">
        <div class="md:col-span-4 space-y-4">
            <div class="portal-card p-6 rounded-3xl space-y-3 text-xs text-slate-300">
                <h2 class="font-heading font-bold text-sm text-white flex items-center gap-2">
                    <i data-lucide="shield" class="w-4 h-4 text-purple-400"></i>
                    Informasi Layanan
                </h2>
                <p>
                    Sebagai peserta terdaftar, identitas akun Anda telah terverifikasi sehingga proses pemrosesan permohonan dapat dilakukan lebih cepat.
                </p>
                <div class="p-3 rounded-xl bg-purple-950/40 border border-purple-800/40">
                    <div class="font-bold text-white mb-0.5">Tenggat Waktu:</div>
                    <div>Maksimal {{ $slaDays }} hari kalender.</div>
                </div>
                <div class="text-[11px] text-slate-400">
                    Kontak: {{ $privacyEmail }}
                </div>
            </div>
        </div>

        <div class="md:col-span-8">
            <div class="portal-card p-6 sm:p-8 rounded-3xl">
                <form method="POST" action="{{ route('data-rights.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="is_portal" value="1">

                    <div style="display: none !important;" aria-hidden="true">
                        <label for="website_url">Website</label>
                        <input type="text" name="website_url" id="website_url" tabindex="-1" autocomplete="off">
                    </div>

                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-300 mb-1">
                            Nama Pemohon *
                        </label>
                        <input type="text" name="name" id="name" required value="{{ old('name', $profile?->full_name_ktp ?? $user->name) }}"
                               class="w-full bg-slate-800 border border-purple-900/40 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-purple-500" />
                        @error('name')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="contact" class="block text-xs font-semibold text-slate-300 mb-1">
                            Kontak (WhatsApp / Email) *
                        </label>
                        <input type="text" name="contact" id="contact" required value="{{ old('contact', $profile?->phone ? $profile->formatted_phone : $user->email) }}"
                               class="w-full bg-slate-800 border border-purple-900/40 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-purple-500" />
                        @error('contact')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="request_type" class="block text-xs font-semibold text-slate-300 mb-1">
                            Jenis Permohonan *
                        </label>
                        <select name="request_type" id="request_type" required
                                class="w-full bg-slate-800 border border-purple-900/40 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-purple-500">
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}" {{ old('request_type', $selectedType) === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('request_type')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-xs font-semibold text-slate-300 mb-1">
                            Rincian Permohonan *
                        </label>
                        <textarea name="description" id="description" rows="5" required
                                  placeholder="Jelaskan kebutuhan Anda (misal: permohonan penghapusan akun atau koreksi data)..."
                                  class="w-full bg-slate-800 border border-purple-900/40 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-purple-500">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="btn-primary w-full justify-center text-sm py-3 flex items-center gap-2">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            Kirimkan Permohonan Hak Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
