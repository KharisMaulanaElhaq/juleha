@extends('layouts.app')

@section('title', 'Kontak & Sekretariat – DPD JULEHA Kabupaten Brebes')
@section('meta_description', 'Alamat sekretariat, nomor WhatsApp resmi, dan formulir komunikasi DPD Juru Sembelih Halal (JULEHA) Kabupaten Brebes.')

@section('content')
<!-- ===================== HERO KONTAK ===================== -->
<section class="relative py-10 sm:py-16 md:py-20 overflow-hidden" style="background: var(--hero-gradient);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <span class="inline-flex items-center gap-2 bg-brand/20 text-brand-highlight border border-brand-light/30 text-xs font-semibold px-4 py-1.5 sm:py-2 rounded-full uppercase tracking-wide mb-3 sm:mb-4" data-aos="fade-up">
            <i data-lucide="phone" class="w-3.5 h-3.5"></i> Layanan Komunikasi
        </span>
        <h1 class="font-heading font-extrabold text-2xl sm:text-4xl md:text-5xl text-white mb-3 sm:mb-4 leading-tight" data-aos="fade-up" data-aos-delay="100">
            Hubungi DPD JULEHA Brebes
        </h1>
        <p class="text-slate-200 max-w-2xl mx-auto text-sm sm:text-base md:text-lg leading-relaxed line-clamp-3 sm:line-clamp-none" data-aos="fade-up" data-aos-delay="200">
            Silakan hubungi sekretariat kami untuk pertanyaan seputar bimbingan teknis, permohonan narasumber, atau konsultasi penyembelihan kurban masjid.
        </p>
    </div>
</section>

<!-- ===================== DETAIL KONTAK & FORMULIR ===================== -->
<section class="section-padding bg-white dark:bg-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12">
            <!-- Left Info Sekretariat -->
            <div data-aos="fade-right" class="space-y-8">
                <div>
                    <span class="section-tag mb-3">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                        Sekretariat Daerah
                    </span>
                    <h2 class="font-heading font-extrabold text-3xl text-slate-800 dark:text-white mb-4">
                        Pusat Koordinasi &amp; Layanan Umat
                    </h2>
                    <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base leading-relaxed">
                        Kami siap melayani kebutuhan konsultasi seputar sembelih halal dan kemitraan dengan pengurus masjid, lembaga keagamaan, serta instansi di 17 kecamatan se-Kabupaten Brebes.
                    </p>
                </div>

                <div class="space-y-4">
                    @if(setting('office_address'))
                    <div class="flex items-start gap-4 p-5 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-gray-100 dark:border-slate-700">
                        <div class="w-12 h-12 bg-brand-light dark:bg-brand-hero-dark/60 rounded-xl flex items-center justify-center text-brand dark:text-brand-dark flex-shrink-0">
                            <i data-lucide="home" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Alamat Sekretariat</div>
                            <div class="font-heading font-bold text-base text-slate-800 dark:text-white mt-0.5">
                                {{ setting('office_address') }}
                            </div>
                            <div class="text-xs text-slate-500 mt-1">Kabupaten Brebes, Jawa Tengah</div>
                        </div>
                    </div>
                    @endif

                    @if(setting('office_hours'))
                    <div class="flex items-start gap-4 p-5 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-gray-100 dark:border-slate-700">
                        <div class="w-12 h-12 bg-amber-500/10 rounded-xl flex items-center justify-center text-amber-500 flex-shrink-0">
                            <i data-lucide="clock" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Hari &amp; Jam Layanan</div>
                            <div class="font-heading font-bold text-base text-slate-800 dark:text-white mt-0.5">
                                {{ setting('office_hours') }}
                            </div>
                            <div class="text-xs text-slate-500 mt-1">Layanan informasi &amp; administrasi kantor</div>
                        </div>
                    </div>
                    @endif

                    @if(setting('sk_legalitas'))
                    <div class="flex items-start gap-4 p-5 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-gray-100 dark:border-slate-700">
                        <div class="w-12 h-12 bg-emerald-500/10 rounded-xl flex items-center justify-center text-emerald-500 flex-shrink-0">
                            <i data-lucide="shield-check" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Dasar Hukum &amp; SK</div>
                            <div class="font-heading font-bold text-base text-slate-800 dark:text-white mt-0.5">
                                {{ setting('sk_legalitas') }}
                            </div>
                            <div class="text-xs text-slate-500 mt-1">SK Pengukuhan Kemenkumham RI</div>
                        </div>
                    </div>
                    @endif

                    @if(setting('whatsapp_number'))
                    <a href="{{ setting_wa_link() }}" target="_blank" rel="noopener noreferrer" class="flex items-start gap-4 p-5 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-gray-100 dark:border-slate-700 hover:border-brand-accent/50 dark:hover:border-brand-dark-accent/50 transition-colors">
                        <div class="w-12 h-12 bg-brand-accent-light dark:bg-brand-hero-dark rounded-xl flex items-center justify-center text-brand-accent dark:text-brand-dark-accent flex-shrink-0">
                            <i data-lucide="message-square" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">WhatsApp Resmi Utama</div>
                            <div class="font-heading font-bold text-base text-slate-800 dark:text-white mt-0.5 flex items-center gap-2">
                                {{ setting_wa_display('whatsapp_number') }}
                                <i data-lucide="external-link" class="w-3.5 h-3.5 text-brand-accent dark:text-brand-dark-accent"></i>
                            </div>
                            <div class="text-xs text-slate-500 mt-1">Layanan pesan cepat pengurus &amp; admin</div>
                        </div>
                    </a>
                    @endif

                    @if(setting('whatsapp_secondary'))
                    <a href="{{ setting_wa_link(null, 'whatsapp_secondary') }}" target="_blank" rel="noopener noreferrer" class="flex items-start gap-4 p-5 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-gray-100 dark:border-slate-700 hover:border-brand-accent/50 dark:hover:border-brand-dark-accent/50 transition-colors">
                        <div class="w-12 h-12 bg-brand-light dark:bg-brand-hero-dark/60 rounded-xl flex items-center justify-center text-brand dark:text-brand-dark flex-shrink-0">
                            <i data-lucide="phone-call" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">WhatsApp Cadangan</div>
                            <div class="font-heading font-bold text-base text-slate-800 dark:text-white mt-0.5 flex items-center gap-2">
                                {{ setting_wa_display('whatsapp_secondary') }}
                                <i data-lucide="external-link" class="w-3.5 h-3.5 text-brand-accent dark:text-brand-dark-accent"></i>
                            </div>
                            <div class="text-xs text-slate-500 mt-1">Kontak alternatif pengurus</div>
                        </div>
                    </a>
                    @endif

                    @if(setting('admin_email'))
                    <div class="flex items-start gap-4 p-5 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-gray-100 dark:border-slate-700">
                        <div class="w-12 h-12 bg-brand-light dark:bg-brand-hero-dark/60 rounded-xl flex items-center justify-center text-brand dark:text-brand-dark flex-shrink-0">
                            <i data-lucide="mail" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Email Resmi</div>
                            <div class="font-heading font-bold text-base text-slate-800 dark:text-white mt-0.5">
                                <a href="mailto:{{ setting('admin_email') }}" class="hover:underline">
                                    {{ setting('admin_email') }}
                                </a>
                            </div>
                            <div class="text-xs text-slate-500 mt-1">Korespondensi surat &amp; kemitraan</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Right Form Kirim Pesan -->
            <div data-aos="fade-left">
                <div class="p-8 sm:p-10 rounded-3xl bg-slate-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 shadow-soft">
                    <h3 class="font-heading font-extrabold text-2xl text-slate-800 dark:text-white mb-2">Kirim Pesan Cepat</h3>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mb-6">
                        Tinggalkan pesan Anda, pengurus DPD JULEHA Brebes akan segera merespons via WhatsApp atau email.
                    </p>

                    <form onsubmit="handleContactWhatsApp(event)" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap *</label>
                            <input id="contact_name" type="text" required placeholder="Nama Anda"
                                class="w-full bg-white dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-brand dark:focus:border-brand-dark" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor WhatsApp / HP *</label>
                            <input id="contact_phone" type="tel" required placeholder="08xxxxxxxxxx"
                                class="w-full bg-white dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-brand dark:focus:border-brand-dark" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kategori Kepentingan *</label>
                            <select id="contact_category" class="w-full bg-white dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-brand dark:focus:border-brand-dark" required>
                                <option value="Informasi Bimtek">Informasi Bimbingan Teknis</option>
                                <option value="Pendampingan Kurban">Pendampingan Sembelih Kurban Masjid</option>
                                <option value="Sertifikasi BNSP">Informasi Uji Kompetensi BNSP</option>
                                <option value="Kerjasama RPH">Kerjasama / Konsultasi RPH/RPU</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Isi Pesan *</label>
                            <textarea id="contact_message" rows="4" required placeholder="Tuliskan pesan atau pertanyaan Anda..."
                                class="w-full bg-white dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-brand dark:focus:border-brand-dark"></textarea>
                        </div>
                        <button type="submit" class="btn-primary w-full justify-center text-sm py-3 mt-2 flex items-center gap-2">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            Kirim Pesan via WhatsApp
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    function handleContactWhatsApp(e) {
        e.preventDefault();
        const name = document.getElementById('contact_name').value;
        const phone = document.getElementById('contact_phone').value;
        const category = document.getElementById('contact_category').value;
        const message = document.getElementById('contact_message').value;

        const adminPhone = "{{ normalize_wa_number(setting('whatsapp_number', '6282324448886')) }}";
        const text = `Halo Pengurus DPD JULEHA Brebes,\n\nNama: ${name}\nNo HP: ${phone}\nKategori: ${category}\nPesan:\n${message}`;

        window.open(`https://wa.me/${adminPhone}?text=${encodeURIComponent(text)}`, '_blank');
    }
</script>
@endpush

<!-- ===================== SECTION CTA ===================== -->
<x-cta variant="strip" />
@endsection
