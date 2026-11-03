<?php
// database/migrations/2026_10_17_000002_create_advisor_assignments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advisor_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advisor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['advisor_id', 'santri_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advisor_assignments');
    }
};