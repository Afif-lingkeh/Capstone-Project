<?php
// database/migrations/2026_10_17_100002_create_institutions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 20)->index(); // ptn / pts / international / kedinasan
            $table->foreignId('country_id')->constrained()->restrictOnDelete();
            $table->string('city', 100)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['name', 'country_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
