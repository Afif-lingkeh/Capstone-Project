<?php
// database/migrations/2026_11_03_200002_create_guidance_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Slot waktu yang dibuka pembimbing akademik. Satu slot = satu santri.
        Schema::create('guidance_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advisor_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('mode', 10);           // online / offline
            $table->string('location')->nullable(); // ruang atau tautan rapat
            $table->timestamps();

            $table->index(['advisor_id', 'starts_at']);
        });

        Schema::create('guidance_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slot_id')->constrained('guidance_slots')->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('users')->cascadeOnDelete();
            $table->string('topic', 150);
            $table->string('message', 1000)->nullable();
            $table->string('status', 15)->default('booked');
            $table->timestamp('cancelled_at')->nullable();
            $table->text('outcome_notes')->nullable(); // catatan hasil bimbingan oleh pembimbing
            $table->text('next_steps')->nullable();
            $table->timestamps();

            $table->index(['santri_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guidance_bookings');
        Schema::dropIfExists('guidance_slots');
    }
};
