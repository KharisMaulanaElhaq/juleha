<?php

namespace Tests\Feature;

use App\Models\Program;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_home_page_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('JULEHA');
        $response->assertSee('Kabupaten Brebes');
        $response->assertSee('Daftar Bimtek');
    }

    public function test_tentang_page_loads_successfully(): void
    {
        $response = $this->get('/tentang');
        $response->assertStatus(200);
        $response->assertSee('Tentang DPD JULEHA');
        $response->assertSee('Visi');
        $response->assertSee('Misi');
    }

    public function test_pengurus_page_loads_successfully(): void
    {
        $response = $this->get('/pengurus');
        $response->assertStatus(200);
        $response->assertSee('Struktur Pengurus');
    }

    public function test_program_index_page_loads_successfully(): void
    {
        $response = $this->get('/program');
        $response->assertStatus(200);
        $response->assertSee('Program Kerja');
    }

    public function test_all_program_detail_pages_load_successfully(): void
    {
        $programs = Program::active()->get();

        $this->assertNotEmpty($programs);

        foreach ($programs as $prog) {
            $response = $this->get("/program/{$prog->slug}");
            $response->assertStatus(200);
            $response->assertSee($prog->title);
        }
    }

    public function test_bimtek_index_page_loads_successfully(): void
    {
        $response = $this->get('/bimtek');
        $response->assertStatus(200);
        $response->assertSee('Bimtek');
    }

    public function test_bimtek_detail_page_loads_successfully(): void
    {
        $response = $this->get('/bimtek/bimtek-angkatan-1');
        $response->assertStatus(200);
        $response->assertSee('Pendaftaran Bimtek Online');
    }

    public function test_berita_index_page_loads_successfully(): void
    {
        $response = $this->get('/berita');
        $response->assertStatus(200);
        $response->assertSee('Berita &amp; Artikel', false);
    }

    public function test_berita_detail_page_loads_successfully(): void
    {
        $response = $this->get('/berita/syarat-sah-penyembelihan-halal');
        $response->assertStatus(200);
        $response->assertSee('Memahami 3 Saluran');
    }

    public function test_galeri_page_loads_successfully(): void
    {
        $response = $this->get('/galeri');
        $response->assertStatus(200);
        $response->assertSee('Galeri Kegiatan');
    }

    public function test_faq_page_loads_successfully(): void
    {
        $response = $this->get('/faq');
        $response->assertStatus(200);
        $response->assertSee('Pertanyaan yang Sering Diajukan');
    }

    public function test_kontak_page_loads_successfully(): void
    {
        $response = $this->get('/kontak');
        $response->assertStatus(200);
        $response->assertSee('Hubungi DPD JULEHA Brebes');
        $response->assertSee('Sekretariat');
    }

    public function test_kebijakan_privasi_page_loads_successfully(): void
    {
        $response = $this->get('/kebijakan-privasi');
        $response->assertStatus(200);
        $response->assertSee('Kebijakan Privasi');
    }

    public function test_legacy_routes_redirect_correctly(): void
    {
        $redirects = [
            '/blog' => '/berita',
            '/blog.html' => '/berita',
            '/faq.html' => '/faq',
            '/privacy-policy' => '/kebijakan-privasi',
            '/terms-and-conditions' => '/tentang',
            '/program/pelatihan-dasar' => '/program/bimtek-pelatihan',
        ];

        foreach ($redirects as $url => $destination) {
            $response = $this->get($url);
            $response->assertRedirect($destination);
        }
    }
}
