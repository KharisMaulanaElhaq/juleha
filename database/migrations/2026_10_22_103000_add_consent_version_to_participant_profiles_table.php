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
        Schema::table('participant_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('participant_profiles', 'consent_version')) {
                $table->string('consent_version')->nullable()->after('consent');
            }
            if (! Schema::hasColumn('participant_profiles', 'consent_at')) {
                $table->timestamp('consent_at')->nullable()->after('consent_version');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participant_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('participant_profiles', 'consent_version')) {
                $table->dropColumn('consent_version');
            }
        });
    }
};
