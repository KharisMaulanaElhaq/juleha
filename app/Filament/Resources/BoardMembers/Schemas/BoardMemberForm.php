<?php

namespace App\Filament\Resources\BoardMembers\Schemas;

use App\Models\BoardMember;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BoardMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Struktur & Keanggotaan Pengurus')
                    ->description('Kelola data lembaga pelindung/pembina, pimpinan harian, dan tim bidang kepengurusan.')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Grid::make(2)->schema([
                            Radio::make('kind')
                                ->label('Jenis Pengurus')
                                ->options([
                                    BoardMember::KIND_PERSON => 'Perorangan (Personal)',
                                    BoardMember::KIND_INSTITUTION => 'Lembaga / Instansi',
                                ])
                                ->default(BoardMember::KIND_PERSON)
                                ->required()
                                ->inline()
                                ->live()
                                ->columnSpanFull(),

                            Select::make('group')
                                ->label('Kelompok Kepengurusan')
                                ->options([
                                    BoardMember::GROUP_PELINDUNG_PEMBINA => 'Pelindung & Pembina (Lembaga)',
                                    BoardMember::GROUP_PIMPINAN_HARIAN => 'Pimpinan Harian (Ketua, Sekretaris, Bendahara)',
                                    BoardMember::GROUP_BIDANG => 'Bidang-Bidang Kepengurusan',
                                ])
                                ->default(BoardMember::GROUP_BIDANG)
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state === BoardMember::GROUP_PELINDUNG_PEMBINA) {
                                        $set('kind', BoardMember::KIND_INSTITUTION);
                                        $set('division', null);
                                    } elseif ($state === BoardMember::GROUP_PIMPINAN_HARIAN) {
                                        $set('kind', BoardMember::KIND_PERSON);
                                        $set('division', null);
                                    } else {
                                        $set('kind', BoardMember::KIND_PERSON);
                                        $set('position', null);
                                    }
                                })
                                ->columnSpan(1),

                            Select::make('division')
                                ->label('Nama Bidang')
                                ->options(function () {
                                    $defaults = [
                                        'Bidang Pendidikan' => 'Bidang Pendidikan',
                                        "Bidang Da'wah" => "Bidang Da'wah",
                                        'Bidang Pengembangan Program' => 'Bidang Pengembangan Program',
                                        'Bidang Tim Ahli' => 'Bidang Tim Ahli',
                                        'Bidang Usaha' => 'Bidang Usaha',
                                        'Bidang Humas' => 'Bidang Humas',
                                        'Bidang Adat' => 'Bidang Adat',
                                        'Bidang Perlengkapan/Umum' => 'Bidang Perlengkapan/Umum',
                                        'Bidang Sosial & Kebencanaan' => 'Bidang Sosial & Kebencanaan',
                                    ];

                                    $fromDb = BoardMember::whereNotNull('division')
                                        ->distinct()
                                        ->pluck('division', 'division')
                                        ->toArray();

                                    return array_merge($defaults, $fromDb);
                                })
                                ->searchable()
                                ->createOptionForm([
                                    TextInput::make('new_division')
                                        ->label('Nama Bidang Baru')
                                        ->required()
                                        ->placeholder('Contoh: Bidang Advokasi & Hukum'),
                                ])
                                ->createOptionUsing(fn (array $data): string => $data['new_division'])
                                ->helperText('Pilih rumpun bidang kerja atau tambahkan bidang baru.')
                                ->visible(fn ($get) => $get('group') === BoardMember::GROUP_BIDANG)
                                ->required(fn ($get) => $get('group') === BoardMember::GROUP_BIDANG)
                                ->columnSpan(1),

                            TextInput::make('position')
                                ->label('Kedudukan / Jabatan')
                                ->placeholder('Contoh: Pelindung, Ketua, Sekretaris I, Bendahara I')
                                ->helperText('Untuk lembaga isi kedudukan (mis. Pelindung). Untuk anggota bidang boleh dikosongkan.')
                                ->required(fn ($get) => in_array($get('group'), [BoardMember::GROUP_PELINDUNG_PEMBINA, BoardMember::GROUP_PIMPINAN_HARIAN], true))
                                ->maxLength(255)
                                ->columnSpan(1),

                            TextInput::make('name')
                                ->label('Nama Lengkap / Nama Lembaga')
                                ->placeholder('Tulis nama lengkap pengurus dengan gelar / nama instansi')
                                ->helperText('Tulis lengkap dengan gelar sesuai SK')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(1),

                            TextInput::make('sort_order')
                                ->label('Nomor Urut Tampil')
                                ->numeric()
                                ->default(0)
                                ->helperText('Angka urutan tampilan di daftar susunan pengurus.')
                                ->columnSpan(1),

                            Toggle::make('is_active')
                                ->label('Status Aktif Ditampilkan')
                                ->helperText('Aktifkan agar pengurus tampil di halaman website Struktur Pengurus.')
                                ->default(true)
                                ->columnSpan(1),

                            FileUpload::make('photo')
                                ->label('Foto Profil Pengurus')
                                ->image()
                                ->imageEditor()
                                ->imageCropAspectRatio('1:1')
                                ->imageResizeMode('cover')
                                ->imageResizeTargetWidth('600')
                                ->imageResizeTargetHeight('600')
                                ->directory('board-members')
                                ->maxSize(2048)
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                ->helperText('Pasang foto hanya dengan izin yang bersangkutan. Otomatis dipotong persegi maks. 600px.')
                                ->visible(fn ($get) => $get('kind') !== BoardMember::KIND_INSTITUTION)
                                ->columnSpanFull(),

                            Textarea::make('internal_note')
                                ->label('Catatan Internal (Rahasia)')
                                ->placeholder('Contoh: Perlu dikonfirmasi: orang yang sama?')
                                ->helperText('Catatan rahasia/internal kepengurusan, TIDAK AKAN PERNAH tampil di halaman publik.')
                                ->rows(2)
                                ->columnSpanFull(),
                        ]),
                    ]),
            ]);
    }
}
