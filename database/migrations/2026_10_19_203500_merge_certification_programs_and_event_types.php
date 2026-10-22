<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ubah events.type = 'sertifikasi_bnsp' menjadi 'sertifikasi_kompetensi'
        DB::table('events')
            ->where('type', 'sertifikasi_bnsp')
            ->update(['type' => 'sertifikasi_kompetensi']);

        // 2. Gabungkan program di tabel programs
        $bnspProgram = DB::table('programs')->where('slug', 'sertifikasi-bnsp')->first();
        $kompetensiProgram = DB::table('programs')->where('slug', 'sertifikasi-kompetensi')->first();
        $targetProgram = DB::table('programs')->where('slug', 'sertifikasi-kompetensi-bnsp')->first();

        // Tentukan surviving program dan program yang akan dihapus
        $survivingId = null;
        $deletedId = null;

        if ($targetProgram) {
            $survivingId = $targetProgram->id;
            if ($bnspProgram && $bnspProgram->id !== $survivingId) {
                $deletedId = $bnspProgram->id;
            } elseif ($kompetensiProgram && $kompetensiProgram->id !== $survivingId) {
                $deletedId = $kompetensiProgram->id;
            }
        } elseif ($bnspProgram && $kompetensiProgram) {
            // Gunakan bnspProgram sebagai record yang dipertahankan
            $survivingId = $bnspProgram->id;
            $deletedId = $kompetensiProgram->id;
        } elseif ($bnspProgram) {
            $survivingId = $bnspProgram->id;
        } elseif ($kompetensiProgram) {
            $survivingId = $kompetensiProgram->id;
        }

        // Pindahkan relasi events.program_id ke surviving program sebelum menghapus program duplikat
        if ($survivingId && $deletedId) {
            DB::table('events')
                ->where('program_id', $deletedId)
                ->update(['program_id' => $survivingId]);

            // Hapus program duplikat
            DB::table('programs')->where('id', $deletedId)->delete();
        }

        // Perbarui data program yang dipertahankan dengan deskripsi netral dan slug baru
        if ($survivingId) {
            DB::table('programs')->where('id', $survivingId)->update([
                'title' => 'Sertifikasi Kompetensi BNSP',
                'slug' => 'sertifikasi-kompetensi-bnsp',
                'category' => 'Pendidikan & Sertifikasi',
                'summary' => 'Fasilitasi uji kompetensi juru sembelih halal melalui lembaga sertifikasi mitra. Informasi lengkap akan diumumkan oleh DPD.',
                'description' => '<p>Fasilitasi uji kompetensi juru sembelih halal melalui lembaga sertifikasi mitra. Informasi lengkap akan diumumkan oleh DPD.</p>',
                'icon' => 'badge-check',
                'lsp_name' => null, // Dikosongkan sampai diisi admin
                'features' => json_encode([
                    'Fasilitasi uji kompetensi juru sembelih halal bersama lembaga sertifikasi mitra',
                    'Standardisasi keahlian juru sembelih halal syar\'i dan higienis',
                ]),
                'requirements' => null,
                'how_to_join' => json_encode([
                    'Konsultasikan kesiapan portofolio dengan tim sertifikasi DPD JULEHA via WhatsApp',
                    'Pengumuman resmi angkatan uji kompetensi akan diumumkan di situs DPD JULEHA Brebes',
                ]),
                'cta_type' => 'whatsapp',
                'cta_text' => 'Tanya via WhatsApp',
                'cta_whatsapp_message' => 'Halo Admin, saya ingin menanyakan informasi fasilitasi Sertifikasi Kompetensi BNSP DPD JULEHA Brebes.',
                'sort_order' => 5,
                'is_active' => true,
                'meta_title' => 'Sertifikasi Kompetensi BNSP – DPD JULEHA Brebes',
                'meta_description' => 'Fasilitasi uji kompetensi juru sembelih halal melalui lembaga sertifikasi mitra di Kabupaten Brebes.',
                'updated_at' => now(),
            ]);
        }

        // Rapikan urutan program selanjutnya agar urut 1 sampai 7
        DB::table('programs')->where('slug', 'tot-juleha')->update(['sort_order' => 6]);
        DB::table('programs')->where('slug', 'instruktur-bnsp')->update(['sort_order' => 7]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Revert program yang digabungkan
        $mergedProgram = DB::table('programs')->where('slug', 'sertifikasi-kompetensi-bnsp')->first();

        if ($mergedProgram) {
            // Kembalikan ke sertifikasi-bnsp
            DB::table('programs')->where('id', $mergedProgram->id)->update([
                'title' => 'Sertifikasi BNSP',
                'slug' => 'sertifikasi-bnsp',
                'category' => 'Pendidikan & Sertifikasi',
                'summary' => 'Fasilitasi uji kompetensi juru sembelih halal melalui Lembaga Sertifikasi Profesi (LSP) mitra. Informasi lengkap akan diumumkan oleh DPD.',
                'description' => '<p>Program Sertifikasi BNSP memfasilitasi juru sembelih halal untuk mengikuti uji kompetensi standar nasional melalui Lembaga Sertifikasi Profesi (LSP) mitra. Informasi lengkap mengenai nama lembaga penyelenggara/LSP mitra, jadwal, dan persyaratan akan diumumkan resmi oleh DPD JULEHA Brebes.</p>',
                'icon' => 'badge-check',
                'lsp_name' => 'LSP Mitra Terlisensi BNSP',
                'features' => json_encode([
                    'Fasilitasi uji kompetensi standar nasional',
                    'Kerja sama dengan LSP mitra terlisensi resmi',
                ]),
                'requirements' => null,
                'how_to_join' => json_encode([
                    'Konsultasikan kesiapan portofolio dengan tim sertifikasi DPD JULEHA via WhatsApp',
                    'Pengumuman resmi angkatan uji kompetensi akan diumumkan di situs DPD JULEHA Brebes',
                ]),
                'cta_type' => 'whatsapp',
                'cta_text' => 'Tanya via WhatsApp',
                'cta_whatsapp_message' => 'Halo Admin, saya ingin menanyakan informasi fasilitasi Sertifikasi BNSP DPD JULEHA Brebes.',
                'sort_order' => 6,
                'is_active' => true,
                'meta_title' => 'Fasilitasi Sertifikasi BNSP – DPD JULEHA Brebes',
                'meta_description' => 'Fasilitasi sertifikasi BNSP resmi bersama LSP mitra di Kabupaten Brebes.',
                'updated_at' => now(),
            ]);

            // Buat kembali program sertifikasi-kompetensi jika belum ada
            $existsKompetensi = DB::table('programs')->where('slug', 'sertifikasi-kompetensi')->exists();
            if (! $existsKompetensi) {
                DB::table('programs')->insert([
                    'title' => 'Sertifikasi Kompetensi',
                    'slug' => 'sertifikasi-kompetensi',
                    'category' => 'Pendidikan & Sertifikasi',
                    'summary' => 'Standardisasi dan pengujian kompetensi juru sembelih halal di Kabupaten Brebes. Informasi lengkap akan diumumkan oleh DPD.',
                    'description' => '<p>Program Sertifikasi Kompetensi diselenggarakan untuk membina dan memastikan standar keahlian juru sembelih halal di Kabupaten Brebes. Informasi lengkap mengenai persyaratan teknis, biaya, dan jadwal pelaksanaan akan diumumkan resmi oleh DPD JULEHA Brebes.</p>',
                    'icon' => 'award',
                    'lsp_name' => null,
                    'features' => json_encode([
                        'Standardisasi keahlian juru sembelih halal',
                        'Penyelarasan standar kompetensi teknis sembelih syar\'i dan higienis',
                    ]),
                    'requirements' => null,
                    'how_to_join' => json_encode([
                        'Hubungi sekretariat DPD JULEHA Brebes via WhatsApp untuk konsultasi awal',
                        'Informasi pembukaan angkatan sertifikasi akan diumumkan secara berkala di portal resmi',
                    ]),
                    'cta_type' => 'whatsapp',
                    'cta_text' => 'Tanya via WhatsApp',
                    'cta_whatsapp_message' => 'Halo Admin, saya ingin menanyakan informasi program Sertifikasi Kompetensi DPD JULEHA Brebes.',
                    'sort_order' => 5,
                    'is_active' => true,
                    'meta_title' => 'Sertifikasi Kompetensi Juru Sembelih – DPD JULEHA Brebes',
                    'meta_description' => 'Program sertifikasi kompetensi juru sembelih halal DPD JULEHA Kabupaten Brebes.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Kembalikan urutan program
        DB::table('programs')->where('slug', 'tot-juleha')->update(['sort_order' => 7]);
        DB::table('programs')->where('slug', 'instruktur-bnsp')->update(['sort_order' => 8]);
    }
};
