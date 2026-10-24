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
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('version')->default('1.0');
            $table->date('effective_date')->nullable();
            $table->longText('content');
            $table->string('status')->default('draft'); // draft, published
            $table->text('change_summary')->nullable();
            $table->boolean('is_mandatory_consent')->default(false);
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('legal_document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_document_id')->constrained('legal_documents')->cascadeOnDelete();
            $table->string('version');
            $table->date('effective_date')->nullable();
            $table->longText('content');
            $table->text('change_summary')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_document_versions');
        Schema::dropIfExists('legal_documents');
    }
};
