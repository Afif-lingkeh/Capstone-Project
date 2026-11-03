<?php
// database/migrations/2026_11_03_100002_create_programs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->restrictOnDelete();
            $table->foreignId('country_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type', 20);          // work / internship
            $table->string('summary', 500);
            $table->text('requirements');
            $table->text('costs');
            $table->string('min_education', 100)->nullable();
            $table->date('deadline')->nullable();
            $table->string('official_contact');
            $table->string('official_url')->nullable();
            $table->string('status', 10)->default('open'); // open / closed
            $table->timestamp('published_at')->nullable(); // null = draf, belum tampil ke santri
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });

        Schema::create('program_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['program_id', 'user_id']);
        });

        Schema::create('program_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('note', 500)->nullable();
            $table->timestamp('followed_up_at')->nullable();
            $table->timestamps();
            $table->unique(['program_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_interests');
        Schema::dropIfExists('program_bookmarks');
        Schema::dropIfExists('programs');
    }
};
