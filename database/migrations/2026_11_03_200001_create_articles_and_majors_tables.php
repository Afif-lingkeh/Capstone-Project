<?php
// database/migrations/2026_11_03_200001_create_articles_and_majors_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category', 20);
            $table->string('excerpt', 300);
            $table->text('body');
            $table->string('source_url')->nullable(); // sumber resmi (mis. snpmb.id) untuk dicek santri
            $table->timestamp('published_at')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['category', 'published_at']);
        });

        // Jurusan: dipakai rekomendasi hasil tes potensi (Fase 6).
        Schema::create('majors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('pathway', 20);
            $table->text('description')->nullable();
            $table->string('career_prospects', 500)->nullable();
            $table->timestamps();

            $table->unique(['name', 'pathway']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('majors');
        Schema::dropIfExists('articles');
    }
};
