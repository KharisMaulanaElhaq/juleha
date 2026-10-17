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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('role')->index();
            $table->timestamp('suspended_at')->nullable()->after('is_active');
            $table->text('suspended_reason')->nullable()->after('suspended_at');
            $table->timestamp('last_login_at')->nullable()->after('suspended_reason');
            $table->string('last_login_method', 20)->nullable()->after('last_login_at');
            $table->timestamp('anonymized_at')->nullable()->after('last_login_method');
            $table->softDeletes()->after('anonymized_at');

            $table->index(['role', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'is_active']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'is_active',
                'suspended_at',
                'suspended_reason',
                'last_login_at',
                'last_login_method',
                'anonymized_at',
            ]);
        });
    }
};
