<!-- ===================== FOOTER ===================== -->
<footer class="text-white overflow-hidden relative bg-[#190e29] border-t border-purple-950/40">
    <!-- Decorative Ambient Glow -->
    <div class="absolute top-0 left-0 w-[500px] h-[500px] rounded-full opacity-[0.08] pointer-events-none"
        style="background: radial-gradient(circle, #7248b8, transparent); transform: translate(-40%, -40%);"></div>
    <div class="absolute bottom-0 right-0 w-[400px] h-[400px] rounded-full opacity-[0.06] pointer-events-none"
        style="background: radial-gradient(circle, #d4a017, transparent); transform: translate(30%, 30%);"></div>

    <!-- Decorative Top Line -->
    <div class="w-full h-1 bg-gradient-to-r from-transparent via-[#7248b8] to-transparent opacity-60"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-8 sm:pt-16 sm:pb-10">
        <!-- TOP ROW: Brand Profile & Sekretariat -->
        <div class="grid lg:grid-cols-2 gap-8 lg:gap-10 items-start mb-8 pb-8 sm:mb-14 sm:pb-14 border-b border-white/10">
            <!-- Brand Block -->
            <div>
                <div class="flex items-center gap-3 mb-5">
                    <img src="{{ asset('assets/logo-juleha-brebes.png') }}" alt="Logo DPD JULEHA Brebes" class="h-12 w-auto object-contain flex-shrink-0" />
                    <div>
                        <div class="font-heading font-extrabold text-2xl text-white tracking-tight">DPD JULEHA BREBES</div>
                        <div class="text-xs text-brand-highlight font-semibold uppercase tracking-widest">Juru Sembelih Halal Kabupaten Brebes</div>
                    </div>
                </div>
                <p class="text-slate-300 text-sm leading-relaxed mb-6 max-w-xl">
                    Dewan Pengurus Daerah Juru Sembelih Halal (DPD JULEHA) Kabupaten Brebes merupakan wadah pembinaan, edukasi, dan standardisasi juru sembelih halal untuk memastikan kehalalan, kebersihan, serta higienitas daging sembelihan sesuai syariat Islam dan standar perundangan.
                </p>

                <!-- Nilai Dasar Row -->
                <div class="flex flex-wrap gap-3 mb-6">
                    <div class="flex items-center gap-2 bg-white/5 border border-white/10 rounded-lg px-3 py-2">
                        <div class="w-7 h-7 bg-brand/30 rounded-full flex items-center justify-center flex-shrink-0">
                            <i data-lucide="check-check" class="w-3.5 h-3.5 text-brand-dark"></i>
                        </div>
                        <span class="text-slate-200 text-xs font-medium">Syar'i &amp; Halal</span>
                    </div>
                    <div class="flex items-center gap-2 bg-white/5 border border-white/10 rounded-lg px-3 py-2">
                        <div class="w-7 h-7 bg-brand-accent/20 rounded-full flex items-center justify-center flex-shrink-0">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-brand-dark-accent"></i>
                        </div>
                        <span class="text-slate-200 text-xs font-medium">Higienis &amp; Aman</span>
                    </div>
                    <div class="flex items-center gap-2 bg-white/5 border border-white/10 rounded-lg px-3 py-2">
                        <div class="w-7 h-7 bg-amber-500/20 rounded-full flex items-center justify-center flex-shrink-0">
                            <i data-lucide="award" class="w-3.5 h-3.5 text-amber-300"></i>
                        </div>
                        <span class="text-slate-200 text-xs font-medium">Standar Kompetensi</span>
                    </div>
                </div>
            </div>

            <!-- Sekretariat & Kontak Singkat -->
            <div class="bg-gradient-to-br from-brand-hero/50 to-slate-900 border border-brand/30 rounded-2xl p-5 sm:p-7">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-brand rounded-xl flex items-center justify-center flex-shrink-0">
                        <i data-lucide="map-pin" class="w-5 h-5 text-brand-highlight"></i>
                    </div>
                    <div>
                        <h4 class="font-heading font-bold text-lg text-white">Sekretariat Daerah</h4>
                        <p class="text-slate-300 text-xs">Pusat Informasi &amp; Koordinasi DPD</p>
                    </div>
                </div>

                <div class="space-y-3 text-sm text-slate-300">
                    @if(setting('office_address'))
                        <div class="flex items-start gap-2.5">
                            <i data-lucide="home" class="w-4 h-4 text-brand-dark mt-1 flex-shrink-0"></i>
                            <span>{{ setting('office_address') }}</span>
                        </div>
                    @endif

                    @if(setting('sk_legalitas'))
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="shield-check" class="w-4 h-4 text-brand-dark flex-shrink-0"></i>
                            <span class="text-xs">Dasar Hukum: <strong class="text-white font-medium">{{ setting('sk_legalitas') }}</strong></span>
                        </div>
                    @endif

                    @if(setting('whatsapp_number'))
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="phone" class="w-4 h-4 text-brand-dark flex-shrink-0"></i>
                            <a href="{{ setting_wa_link() }}" target="_blank" rel="noopener noreferrer" class="hover:text-brand-highlight transition-colors">
                                WhatsApp: <span class="text-white font-medium">{{ setting_wa_display('whatsapp_number') }}</span>
                            </a>
                        </div>
                    @endif

                    @if(setting('admin_email'))
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="mail" class="w-4 h-4 text-brand-dark flex-shrink-0"></i>
                            <a href="mailto:{{ setting('admin_email') }}" class="hover:text-brand-highlight transition-colors">
                                Email: <span class="text-white font-medium">{{ setting('admin_email') }}</span>
                            </a>
                        </div>
                    @endif

                    @if(setting('office_hours'))
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="clock" class="w-4 h-4 text-brand-dark flex-shrink-0"></i>
                            <span class="text-xs">Layanan: {{ setting('office_hours') }}</span>
                        </div>
                    @endif
                </div>

                <div class="mt-6 pt-5 border-t border-white/10 flex flex-wrap gap-3">
                    <a href="{{ route('bimtek.index') }}" class="btn-primary text-xs py-2.5 px-4">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                        Informasi Bimtek
                    </a>
                    <a href="{{ route('kontak') }}" class="btn-outline text-xs py-2 px-4">
                        Hubungi Sekretariat
                    </a>
                </div>
            </div>
        </div>

        <!-- MAIN LINKS GRID: 4 columns -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8 mb-8 pb-8 sm:mb-14 sm:pb-14 border-b border-white/10">
            <!-- Profil Organisasi -->
            <div>
                <h4 class="font-heading font-semibold text-sm text-white mb-5 flex items-center gap-2">
                    <span class="w-1 h-4 bg-brand-accent rounded-full inline-block"></span>
                    Profil
                </h4>
                <ul class="space-y-3">
                    <li><a href="{{ route('tentang') }}" class="text-slate-400 text-sm hover:text-brand-highlight transition-colors">Tentang JULEHA Brebes</a></li>
                    <li><a href="{{ route('pengurus') }}" class="text-slate-400 text-sm hover:text-brand-highlight transition-colors">Struktur Pengurus</a></li>
                    <li><a href="{{ route('tentang') }}#visi-misi" class="text-slate-400 text-sm hover:text-brand-highlight transition-colors">Visi &amp; Misi</a></li>
                    <li><a href="{{ route('tentang') }}#legalitas" class="text-slate-400 text-sm hover:text-brand-highlight transition-colors">Legalitas &amp; SK</a></li>
                </ul>
            </div>

            <!-- Program Kerja -->
            <div>
                <h4 class="font-heading font-semibold text-sm text-white mb-5 flex items-center gap-2">
                    <span class="w-1 h-4 bg-brand-dark rounded-full inline-block"></span>
                    Program Kerja
                </h4>
                <ul class="space-y-3">
                    <li><a href="{{ route('program.show', 'penyembelihan-aqiqah') }}" class="text-slate-400 text-sm hover:text-brand-highlight transition-colors">Penyembelihan Aqiqah</a></li>
                    <li><a href="{{ route('program.show', 'qurban') }}" class="text-slate-400 text-sm hover:text-brand-highlight transition-colors">Pelayanan Qurban</a></li>
                    <li><a href="{{ route('program.show', 'bimtek-pelatihan') }}" class="text-slate-400 text-sm hover:text-brand-highlight transition-colors">Bimtek &amp; Pelatihan</a></li>
                    <li><a href="{{ route('program.show', 'sertifikasi-kompetensi-bnsp') }}" class="text-slate-400 text-sm hover:text-brand-highlight transition-colors">Sertifikasi Kompetensi BNSP</a></li>
                </ul>
            </div>

            <!-- Publikasi & Edukasi -->
            <div>
                <h4 class="font-heading font-semibold text-sm text-white mb-5 flex items-center gap-2">
                    <span class="w-1 h-4 bg-brand-accent rounded-full inline-block"></span>
                    Informasi &amp; Edukasi
                </h4>
                <ul class="space-y-3">
                    <li><a href="{{ route('berita.index') }}" class="text-slate-400 text-sm hover:text-brand-highlight transition-colors">Berita &amp; Kegiatan</a></li>
                    <li><a href="{{ route('bimtek.index') }}" class="text-slate-400 text-sm hover:text-brand-highlight transition-colors">Jadwal Bimtek Terdekat</a></li>
                    <li><a href="{{ route('galeri') }}" class="text-slate-400 text-sm hover:text-brand-highlight transition-colors">Galeri Dokumentasi</a></li>
                    <li><a href="{{ route('faq') }}" class="text-slate-400 text-sm hover:text-brand-highlight transition-colors">Tanya Jawab (FAQ)</a></li>
                    <li><a href="{{ route('login') }}" class="text-slate-300 text-sm hover:text-brand-highlight transition-colors flex items-center gap-1.5"><i data-lucide="log-in" class="w-3.5 h-3.5 text-brand-highlight"></i><span>Portal Peserta (Masuk)</span></a></li>
                </ul>
            </div>

            <!-- Kontak & Layanan -->
            <div>
                <h4 class="font-heading font-semibold text-sm text-white mb-5 flex items-center gap-2">
                    <span class="w-1 h-4 bg-brand-dark rounded-full inline-block"></span>
                    Bantuan &amp; Konsultasi
                </h4>
                <p class="text-slate-400 text-xs leading-relaxed mb-4">
                    Membutuhkan juru sembelih halal bersertifikat untuk kegiatan kurban atau RPH? Silakan hubungi sekretariat.
                </p>
                <a href="{{ route('kontak') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-brand-dark hover:text-brand-highlight">
                    <span>Hubungi Layanan Terpadu</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        <!-- BOTTOM BAR -->
        <div class="flex flex-col lg:flex-row items-center justify-between gap-5 text-center lg:text-left">
            <!-- Copyright & Template Attribution -->
            <div>
                <p class="text-slate-400 text-sm">
                    &copy; {{ date('Y') }} <span class="text-slate-200 font-semibold">DPD JULEHA Kabupaten Brebes</span>. Hak cipta dilindungi.
                </p>
                <!-- Atribusi Template K29Solutions & ThemeWagon (Dipertahankan) -->
                <p class="text-slate-400 text-xs mt-1">
                    Template design based on work by <a href="https://www.templatemonster.com/authors/k29solutions/" target="_blank" rel="noopener" class="text-slate-300 hover:underline">K29Solutions</a> &bull; Distributed by <a href="https://www.themewagon.com/" target="_blank" rel="noopener" class="text-slate-300 hover:underline">ThemeWagon</a>.
                </p>
            </div>

            <!-- Legal Links -->
            <div class="flex flex-wrap justify-center gap-x-5 gap-y-2 text-xs">
                <a href="{{ route('kebijakan-privasi') }}" class="text-slate-400 hover:text-brand-highlight transition-colors">Kebijakan Privasi</a>
                <a href="{{ route('syarat-ketentuan') }}" class="text-slate-400 hover:text-brand-highlight transition-colors">Syarat &amp; Ketentuan</a>
                <a href="{{ route('penyimpanan-perangkat') }}" class="text-slate-400 hover:text-brand-highlight transition-colors">Penyimpanan Perangkat</a>
                <a href="{{ route('disclaimer') }}" class="text-slate-400 hover:text-brand-highlight transition-colors">Disclaimer</a>
                <a href="{{ route('hak-cipta') }}" class="text-slate-400 hover:text-brand-highlight transition-colors">Hak Cipta &amp; Atribusi</a>
                <a href="{{ route('data-rights.create') }}" class="text-slate-400 hover:text-brand-highlight transition-colors">Permintaan Hak Data</a>
            </div>
        </div>
    </div>
</footer>
