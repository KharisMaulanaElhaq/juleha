<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category'); // 'Layanan Masyarakat' atau 'Pendidikan & Sertifikasi'
            $table->text('summary')->nullable(); // Ringkasan singkat 1-2 kalimat
            $table->longText('description')->nullable(); // Deskripsi lengkap
            $table->string('icon')->default('check-circle');
            $table->string('image')->nullable();
            $table->string('lsp_name')->nullable(); // Nama LSP mitra untuk program sertifikasi BNSP
            $table->json('features')->nullable(); // Poin keunggulan / cakupan
            $table->json('requirements')->nullable(); // Syarat mengikuti program
            $table->json('how_to_join')->nullable(); // Alur / cara mengikuti
            $table->string('cta_type')->default('whatsapp'); // whatsapp, bimtek, custom
            $table->string('cta_text')->nullable();
            $table->string('cta_whatsapp_message')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
