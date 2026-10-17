<?php

namespace Tests\Feature;

use App\Models\Setting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSettingsPublicDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Memastikan ketika pengaturan situs diubah, halaman publik (top bar, footer, kontak, wa.me)
     * langsung menampilkan data baru secara reaktif berkat cache invalidation.
     */
    public function test_public_pages_display_updated_settings_immediately(): void
    {
        // 1. Ubah pengaturan situs (simulasi pengaturan disimpan oleh pengurus)
        Setting::set('site_name', 'DPD JULEHA Brebes Official');
        Setting::set('whatsapp_number', '082324448886');
        Setting::set('whatsapp_secondary', '0816333601');
        Setting::set('admin_email', 'sekretariat.juleha.brebes@gmail.com');
        Setting::set('office_hours', 'Senin – Jumat, 08.00 – 15.00 WIB');
        Setting::set('office_address', 'Jalan Kaliluwih Wanasari Brebes');
        Setting::set('sk_legalitas', 'AHU-0000118.AH.01.07.TAHUN 2019');

        // 2. Akses halaman Beranda (/)
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);

        // Top bar di navbar harus menampilkan nomor WhatsApp rapi, email, dan hari/jam kerja
        $homeResponse->assertSee('0823-2444-8886');
        $homeResponse->assertSee('sekretariat.juleha.brebes@gmail.com');
        $homeResponse->assertSee('Senin – Jumat, 08.00 – 15.00 WIB');

        // Link wa.me harus otomatis ternormalisasi ke 6282324448886 tanpa simbol/spasi/awalan 0
        $homeResponse->assertSee('wa.me/6282324448886');

        // Footer harus menampilkan alamat dan nomor SK
        $homeResponse->assertSee('Jalan Kaliluwih Wanasari Brebes');
        $homeResponse->assertSee('AHU-0000118.AH.01.07.TAHUN 2019');

        // 3. Akses halaman Kontak (/kontak)
        $kontakResponse = $this->get('/kontak');
        $kontakResponse->assertStatus(200);
        $kontakResponse->assertSee('Senin – Jumat, 08.00 – 15.00 WIB');
        $kontakResponse->assertSee('Jalan Kaliluwih Wanasari Brebes');
        $kontakResponse->assertSee('0823-2444-8886');
        $kontakResponse->assertSee('0816-333-601');
        $kontakResponse->assertSee('sekretariat.juleha.brebes@gmail.com');
        $kontakResponse->assertSee('AHU-0000118.AH.01.07.TAHUN 2019');

        // 4. Ubah lagi pengaturan (misal jam layanan baru & nomor WA baru) untuk menguji cache invalidation otomatis
        Setting::set('office_hours', 'Sabtu – Ahad, 09.00 – 12.00 WIB');
        Setting::set('whatsapp_number', '081234567890');

        $updatedHome = $this->get('/');
        $updatedHome->assertStatus(200);
        $updatedHome->assertSee('Sabtu – Ahad, 09.00 – 12.00 WIB');
        $updatedHome->assertDontSee('Senin – Jumat, 08.00 – 15.00 WIB');
        $updatedHome->assertSee('0812-3456-7890');
        $updatedHome->assertSee('wa.me/6281234567890');

        // 5. Pastikan TIDAK ADA teks placeholder [ISI: ...] yang muncul ke publik
        $this->assertStringNotContainsString('[ISI:', $homeResponse->getContent());
        $this->assertStringNotContainsString('[ISI:', $kontakResponse->getContent());
        $this->assertStringNotContainsString('[ISI:', $updatedHome->getContent());
    }

    /**
     * Memastikan bila ada field yang kosong, elemen di publik tidak merender teks placeholder aneh.
     */
    public function test_empty_settings_do_not_render_placeholders(): void
    {
        Setting::set('office_hours', '');
        Setting::set('whatsapp_secondary', '');

        $kontakResponse = $this->get('/kontak');
        $kontakResponse->assertStatus(200);

        $this->assertStringNotContainsString('[ISI:', $kontakResponse->getContent());
    }
}
