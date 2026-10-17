<?php

namespace App\Filament\Resources\Programs\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama Program')
                    ->description('Nama program, klasifikasi kategori, ikon visual, dan ringkasan.')
                    ->icon('heroicon-o-rectangle-stack')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label('Nama Program')
                                ->placeholder('Contoh: Penyembelihan Hewan Aqiqah')
                                ->helperText('Nama resmi program kerja yang tampil di header dan kartu.')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? '')))
                                ->columnSpan(1),

                            TextInput::make('slug')
                                ->label('Slug URL Website')
                                ->placeholder('penyembelihan-aqiqah')
                                ->helperText('Alamat tautan halaman detail program. Terisi otomatis dari nama program.')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255)
                                ->columnSpan(1),

                            Select::make('category')
                                ->label('Kategori Program')
                                ->options([
                                    'Layanan Masyarakat' => 'Layanan Masyarakat (Aqiqah, Qurban, Syiar)',
                                    'Pendidikan & Sertifikasi' => 'Pendidikan & Sertifikasi (Bimtek, BNSP, TOT, Instruktur)',
                                ])
                                ->helperText('Pengelompokan tampilan di halaman Program.')
                                ->required()
                                ->default('Layanan Masyarakat')
                                ->columnSpan(1),

                            Select::make('icon')
                                ->label('Ikon Program')
                                ->options([
                                    'heart' => 'heart (Aqiqah / Kasih Sayang)',
                                    'award' => 'award (Qurban / Keutamaan Ibadah)',
                                    'megaphone' => 'megaphone (Syiar & Edukasi Keliling)',
                                    'book-open' => 'book-open (Bimtek & Pelatihan)',
                                    'badge-check' => 'badge-check (Sertifikasi BNSP)',
                                    'users' => 'users (Training of Trainer TOT)',
                                    'graduation-cap' => 'graduation-cap (Instruktur BNSP)',
                                    'check-circle' => 'check-circle (Standar / Umum)',
                                    'shield-check' => 'shield-check (Jaminan Halal)',
                                ])
                                ->helperText('Ikon representatif untuk kartu dan judul program.')
                                ->default('check-circle')
                                ->required()
                                ->columnSpan(1),

                            TextInput::make('lsp_name')
                                ->label('Nama Lembaga Sertifikasi (LSP Mitra)')
                                ->placeholder('Contoh: LSP Mitra Terlisensi BNSP')
                                ->helperText('Bidang ini khusus program sertifikasi/instruktur BNSP. Kosongkan jika bukan uji sertifikasi.')
                                ->maxLength(255)
                                ->columnSpan(1),

                            FileUpload::make('image')
                                ->label('Foto / Banner Program (Opsional)')
                                ->image()
                                ->directory('programs')
                                ->maxSize(2048)
                                ->helperText('Gambar dokumentasi kegiatan (JPG, PNG, WebP, maks. 2MB).')
                                ->columnSpan(1),

                            Textarea::make('summary')
                                ->label('Ringkasan Singkat (1–2 Kalimat)')
                                ->placeholder('Tuliskan ringkasan padat dan jelas mengenai program ini...')
                                ->helperText('Tampil pada kartu pratinjau di Beranda dan daftar Program.')
                                ->rows(2)
                                ->columnSpanFull()
                                ->required(),

                            RichEditor::make('description')
                                ->label('Deskripsi Lengkap Program')
                                ->placeholder('Tuliskan deskripsi lengkap, latar belakang, dan cakupan program di sini...')
                                ->helperText('Penjelasan terperinci yang tampil pada halaman rincian program.')
                                ->columnSpanFull()
                                ->required(),
                        ]),
                    ]),

                Section::make('Keunggulan, Persyaratan & Cara Mengikuti')
                    ->description('Kelola poin cakupan keunggulan, syarat pendaftaran, dan alur partisipasi. Kolom yang kosong tidak akan ditampilkan ke publik.')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([
                        Repeater::make('features')
                            ->label('Poin Keunggulan / Cakupan Program')
                            ->simple(
                                TextInput::make('feature')
                                    ->placeholder('Contoh: Pemotongan tepat memutus 3 saluran sesuai syariat')
                                    ->required()
                            )
                            ->addActionLabel('+ Tambah Poin Keunggulan')
                            ->reorderable()
                            ->columnSpanFull(),

                        Repeater::make('requirements')
                            ->label('Syarat Mengikuti Program (Opsional)')
                            ->simple(
                                TextInput::make('requirement')
                                    ->placeholder('Contoh: Mengisi formulir pendaftaran')
                            )
                            ->addActionLabel('+ Tambah Persyaratan')
                            ->helperText('Kosongkan jika belum ada syarat khusus. Tidak akan muncul di tampilan publik jika kosong.')
                            ->reorderable()
                            ->columnSpanFull(),

                        Repeater::make('how_to_join')
                            ->label('Alur / Cara Mengikuti Program (Opsional)')
                            ->simple(
                                TextInput::make('step')
                                    ->placeholder('Contoh: Hubungi narahubung via WhatsApp untuk konfirmasi jadwal')
                            )
                            ->addActionLabel('+ Tambah Langkah / Alur')
                            ->helperText('Tahapan yang perlu dilakukan masyarakat untuk mengikuti program ini.')
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Aksi & Tombol Kontak (Call to Action)')
                    ->description('Tentukan jenis tombol aksi: tautan formulir Bimtek atau chat langsung ke WhatsApp sekretariat.')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('cta_type')
                                ->label('Tipe Tombol Aksi')
                                ->options([
                                    'whatsapp' => 'Chat WhatsApp Narahubung',
                                    'bimtek' => 'Halaman Pendaftaran Bimtek',
                                ])
                                ->helperText('Pilih tujuan saat pengunjung menekan tombol aksi.')
                                ->default('whatsapp')
                                ->required()
                                ->columnSpan(1),

                            TextInput::make('cta_text')
                                ->label('Label Teks Tombol (Opsional)')
                                ->placeholder('Contoh: Hubungi via WhatsApp / Daftar Bimtek Sekarang')
                                ->helperText('Jika dikosongkan, label otomatis menyesuaikan tipe tombol.')
                                ->maxLength(100)
                                ->columnSpan(1),

                            Textarea::make('cta_whatsapp_message')
                                ->label('Draf Pesan Pembuka WhatsApp (Opsional)')
                                ->placeholder('Contoh: Halo Admin, saya ingin menanyakan layanan...')
                                ->helperText('Pesan awal yang otomatis terisi saat pengunjung membuka link WhatsApp.')
                                ->rows(2)
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Status Penayangan & SEO')
                    ->description('Pengaturan status publikasi dan optimasi mesin pencari.')
                    ->icon('heroicon-o-globe-alt')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('sort_order')
                                ->label('Nomor Urut Tampil')
                                ->numeric()
                                ->default(0)
                                ->helperText('Urutan tampilan di halaman (1, 2, 3, ...). Anda juga dapat mengurutkan dengan drag & drop di tabel.')
                                ->columnSpan(1),

                            Toggle::make('is_active')
                                ->label('Aktif / Tayang di Website')
                                ->helperText('Jika dinonaktifkan, program disembunyikan dari publik.')
                                ->default(true)
                                ->columnSpan(2),

                            TextInput::make('meta_title')
                                ->label('Judul SEO (Meta Title)')
                                ->placeholder('Nama Program – DPD JULEHA Brebes')
                                ->helperText('Judul di tab browser dan hasil pencarian Google.')
                                ->columnSpan(1),

                            Textarea::make('meta_description')
                                ->label('Deskripsi SEO (Meta Description)')
                                ->placeholder('Tulis deskripsi 1-2 kalimat untuk pratinjau mesin pencari...')
                                ->rows(2)
                                ->columnSpan(2),
                        ]),
                    ]),
            ]);
    }
}
