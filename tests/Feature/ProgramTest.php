<?php

namespace Tests\Feature;

use App\Models\Program;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Memastikan halaman /program menampilkan 8 program resmi dalam dua kategori.
     */
    public function test_program_index_displays_eight_active_programs_grouped_by_category(): void
    {
        $response = $this->get('/program');
        $response->assertStatus(200);

        // Kategori utama
        $response->assertSeeText('Layanan Masyarakat');
        $response->assertSeeText('Pendidikan & Sertifikasi');

        // 8 Program resmi DPD JULEHA Brebes
        $expectedPrograms = [
            'Penyembelihan Hewan Aqiqah',
            'Qurban',
            'Syiar dan Edukasi Keliling',
            'Bimtek dan Pelatihan',
            'Sertifikasi Kompetensi',
            'Sertifikasi BNSP',
            'Training of Trainer (TOT) Juleha',
            'Instruktur BNSP',
        ];

        foreach ($expectedPrograms as $title) {
            $response->assertSeeText($title);
        }
    }

    /**
     * Memastikan tiap halaman detail /program/{slug} terbuka sukses dan memuat konten yang sesuai.
     */
    public function test_all_eight_program_detail_pages_load_successfully(): void
    {
        $slugs = [
            'penyembelihan-aqiqah',
            'qurban',
            'syiar-edukasi',
            'bimtek-pelatihan',
            'sertifikasi-kompetensi',
            'sertifikasi-bnsp',
            'tot-juleha',
            'instruktur-bnsp',
        ];

        foreach ($slugs as $slug) {
            $program = Program::where('slug', $slug)->first();
            $this->assertNotNull($program, "Program dengan slug {$slug} harus ada.");

            $response = $this->get("/program/{$slug}");
            $response->assertStatus(200);
            $response->assertSeeText($program->title);
            $response->assertSeeText($program->summary);

            // Cek tombol CTA
            if ($program->cta_type === 'bimtek') {
                $response->assertSee(route('bimtek.index'));
            } else {
                $response->assertSee('wa.me/');
            }
        }
    }

    /**
     * Memastikan program nonaktif (is_active = false) tidak tampil di /program dan beranda,
     * serta mengembalikan status 404 ketika halaman detailnya diakses langsung.
     */
    public function test_inactive_program_is_hidden_from_public_and_returns_404(): void
    {
        $program = Program::where('slug', 'tot-juleha')->first();
        $this->assertNotNull($program);

        // Nonaktifkan program
        $program->update(['is_active' => false]);

        // Halaman index tidak boleh menampilkan program nonaktif
        $indexResponse = $this->get('/program');
        $indexResponse->assertStatus(200);
        $indexResponse->assertDontSeeText($program->title);

        // Halaman beranda tidak boleh menampilkan program nonaktif
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertDontSeeText($program->title);

        // Halaman detail harus mengembalikan 404
        $detailResponse = $this->get("/program/{$program->slug}");
        $detailResponse->assertStatus(404);
    }

    /**
     * Memastikan beranda menampilkan program dari database secara dinamis.
     */
    public function test_home_page_displays_active_programs_from_database(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSeeText('Program Kerja Unggulan');
        $response->assertSeeText('Penyembelihan Hewan Aqiqah');
        $response->assertSeeText('Qurban');
    }
}
