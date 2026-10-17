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
            $table->string('kind')->default('person')->after('id');
            $table->string('group')->nullable()->after('kind'); // pelindung_pembina, pimpinan_harian, bidang
            $table->string('division')->nullable()->after('group');
            $table->string('position')->nullable()->change();
            $table->text('internal_note')->nullable()->after('bio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('board_members', function (Blueprint $table) {
            $table->dropColumn(['kind', 'group', 'division', 'internal_note']);
        });
    }
};
