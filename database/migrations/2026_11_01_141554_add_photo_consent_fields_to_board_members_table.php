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
        Schema::table('board_members', function (Blueprint $table) {
            $table->date('photo_consent_at')->nullable()->after('photo');
            $table->string('photo_consent_note')->nullable()->after('photo_consent_at');
            $table->unsignedInteger('photo_version')->default(1)->after('photo_consent_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('board_members', function (Blueprint $table) {
            $table->dropColumn(['photo_consent_at', 'photo_consent_note', 'photo_version']);
        });
    }
};
