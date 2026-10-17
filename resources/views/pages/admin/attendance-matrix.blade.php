<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekap Kehadiran: {{ $event->title }} – DPD JULEHA Brebes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen">

    <!-- Top Navigation -->
    <header class="bg-[#33204f] text-white px-6 py-4 border-b border-purple-900 shadow-md">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold text-amber-400 uppercase tracking-widest block">
                    DPD JULEHA KABUPATEN BREBES &bull; REKAPITULASI PRESENSI
                </span>
                <h1 class="text-xl font-bold text-white leading-tight">
                    {{ $event->title }}
                </h1>
                <p class="text-xs text-purple-200 mt-0.5">
                    {{ $event->location ?? 'Brebes' }} &bull; {{ $event->starts_at?->format('d M Y') ?? '-' }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.attendance.export', $event->id) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-md transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Ekspor ke Excel (CSV)</span>
                </a>

                <a href="{{ url('/admin/events/' . $event->id . '/edit') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white/10 hover:bg-white/20 text-white transition border border-white/20">
                    &larr; Kembali ke Admin
                </a>
            </div>
        </div>
    </header>

    <!-- Content Matrix Table -->
    <main class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6">

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                <span class="text-xs text-slate-500 uppercase font-semibold">Total Peserta Terdaftar</span>
                <p class="text-2xl font-bold text-purple-950 mt-1">{{ $enrollments->count() }} Orang</p>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                <span class="text-xs text-slate-500 uppercase font-semibold">Jumlah Sesi Pelatihan</span>
                <p class="text-2xl font-bold text-purple-950 mt-1">{{ $sessions->count() }} Sesi</p>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                <span class="text-xs text-slate-500 uppercase font-semibold">Zona Waktu</span>
                <p class="text-2xl font-bold text-emerald-700 mt-1">WIB (Asia/Jakarta)</p>
            </div>
        </div>

        <!-- Matriks Table Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-bold text-base text-slate-900">
                    Matriks Kehadiran (Peserta &times; Sesi)
                </h2>
                <span class="text-xs text-slate-500">
                    H = Hadir &bull; I = Izin &bull; S = Sakit &bull; A = Alpa
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700 divide-y divide-slate-200">
                    <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-3 py-3 w-12 text-center">No</th>
                            <th class="px-4 py-3 min-w-[180px]">Nama Peserta</th>
                            <th class="px-3 py-3 min-w-[120px]">NIK</th>
                            <th class="px-3 py-3 min-w-[110px]">Domisili</th>
                            @foreach($sessions as $idx => $s)
                                <th class="px-3 py-3 text-center min-w-[130px] border-l border-slate-200">
                                    <div class="truncate font-bold text-purple-900">Sesi {{ $idx + 1 }}</div>
                                    <div class="text-[10px] text-slate-500 font-normal truncate max-w-[120px]">{{ $s->title }}</div>
                                </th>
                            @endforeach
                            <th class="px-3 py-3 text-center min-w-[80px] border-l border-slate-200 bg-purple-50 text-purple-950 font-bold">Hadir</th>
                            <th class="px-3 py-3 text-center min-w-[90px] bg-purple-50 text-purple-950 font-bold">% Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($enrollments as $i => $enrollment)
                            @php
                                $u = $enrollment->user;
                                $p = $u?->profile;
                                $userHadir = 0;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-3 py-3 text-center text-slate-400 font-mono">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-900">
                                    {{ $p?->full_name_ktp ?: ($u?->name ?: '-') }}
                                </td>
                                <td class="px-3 py-3 font-mono text-slate-600">
                                    {{ $p?->masked_nik ?: '-' }}
                                </td>
                                <td class="px-3 py-3 text-slate-600">
                                    {{ $p?->district ? 'Kec. ' . $p->district : 'Brebes' }}
                                </td>

                                @foreach($sessions as $session)
                                    @php
                                        $key = ($u?->id ?? 0) . '_' . $session->id;
                                        $att = $attendances->get($key)?->first();
                                    @endphp
                                    <td class="px-3 py-3 text-center border-l border-slate-100">
                                        @if($att)
                                            @if($att->isHadir())
                                                @php $userHadir++; @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200" title="Hadir via {{ strtoupper($att->source) }} pukul {{ $att->attended_at?->format('H:i') }}">
                                                    Hadir
                                                </span>
                                            @elseif($att->status->value === 'izin')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                                    Izin
                                                </span>
                                            @elseif($att->status->value === 'sakit')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                    Sakit
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                                    Alpa
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-slate-300 font-bold">&ndash;</span>
                                        @endif
                                    </td>
                                @endforeach

                                @php
                                    $pct = $sessions->count() > 0 ? round(($userHadir / $sessions->count()) * 100) : 0;
                                @endphp
                                <td class="px-3 py-3 text-center border-l border-slate-100 font-mono font-bold text-slate-900 bg-purple-50/40">
                                    {{ $userHadir }} / {{ $sessions->count() }}
                                </td>
                                <td class="px-3 py-3 text-center bg-purple-50/40 font-mono font-bold {{ $pct >= 75 ? 'text-emerald-700' : ($pct >= 50 ? 'text-amber-700' : 'text-rose-700') }}">
                                    {{ $pct }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $sessions->count() + 6 }}" class="py-10 text-center text-slate-400">
                                    Belum ada peserta berstatus aktif pada kegiatan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

</body>
</html>
