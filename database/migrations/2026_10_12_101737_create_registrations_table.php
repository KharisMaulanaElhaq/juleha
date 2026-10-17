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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('full_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('district'); // kecamatan
            $table->string('village')->nullable(); // desa/kelurahan
            $table->string('participant_type'); // calon_juleha, juleha_aktif, pengurus_masjid, pekerja_rph, lainnya
            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // pending, confirmed, rejected, attended
            $table->boolean('consent')->default(true);
            $table->timestamp('consent_at')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
