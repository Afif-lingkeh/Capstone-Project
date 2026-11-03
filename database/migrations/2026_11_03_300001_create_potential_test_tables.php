<?php
// database/migrations/2026_11_03_300001_create_potential_test_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('potential_tests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('dimension', 20); // academic / interest
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('estimated_minutes')->default(15);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('potential_test_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->string('type', 10);              // choice / likert
            $table->string('pathway', 20)->nullable(); // jalur yang diukur (khusus likert)
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->json('weights'); // {"saintek":3,"soshum":0,"bahasa":1,"keagamaan":0}
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('test_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('potential_test_id')->constrained()->cascadeOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->json('scores')->nullable(); // persentase per jalur, diisi saat dikumpulkan
            $table->timestamps();

            $table->index(['user_id', 'submitted_at']);
        });

        Schema::create('attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_option_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('value')->nullable(); // 1–5 untuk Likert
            $table->timestamps();

            $table->unique(['test_attempt_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempt_answers');
        Schema::dropIfExists('test_attempts');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('potential_tests');
    }
};
