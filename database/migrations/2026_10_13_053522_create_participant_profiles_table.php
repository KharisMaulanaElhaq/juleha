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
        Schema::create('participant_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('phone')->unique(); // Format 62xxxxxxxxxx
            $table->string('full_name_ktp')->nullable();
            $table->text('nik')->nullable(); // Encrypted text via Laravel cast
            $table->string('nik_hash', 64)->unique()->nullable(); // HMAC-SHA256 hash for deduplication
            $table->string('district'); // Kecamatan
            $table->string('village'); // Desa / Kelurahan
            $table->string('institution')->nullable(); // Asal / Instansi
            $table->string('status'); // calon_juleha, juleha_aktif, pengurus_masjid, pekerja_rph
            $table->boolean('consent')->default(true);
            $table->timestamp('consent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participant_profiles');
    }
};
