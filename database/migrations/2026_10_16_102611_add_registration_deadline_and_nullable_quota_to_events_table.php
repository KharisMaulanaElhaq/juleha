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
        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'registration_deadline')) {
                $table->dateTime('registration_deadline')->nullable()->after('ends_at');
            }
            if (! Schema::hasColumn('events', 'ends_at')) {
                $table->dateTime('ends_at')->nullable()->after('starts_at');
            }
            $table->unsignedInteger('quota')->nullable()->default(30)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'registration_deadline')) {
                $table->dropColumn('registration_deadline');
            }
            $table->unsignedInteger('quota')->nullable(false)->default(30)->change();
        });
    }
};
