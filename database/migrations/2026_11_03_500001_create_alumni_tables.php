<?php
// database/migrations/2026_11_03_500001_create_alumni_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumni_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->year('graduation_year');
            $table->string('institution_name');          // kampus atau tempat kerja
            $table->string('field', 150)->nullable();    // bidang/jurusan
            $table->string('pathway', 20)->nullable();   // saintek/soshum/bahasa/keagamaan
            $table->string('city', 100)->nullable();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->string('activity', 20)->default('studying'); // studying / working / other
            $table->string('bio', 600)->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('public_email')->nullable();
            $table->boolean('show_linkedin')->default(false);
            $table->boolean('show_email')->default(false);
            $table->string('whatsapp', 20)->nullable(); // hanya untuk admin; tidak pernah tampil ke santri
            $table->boolean('accepts_consultations')->default(true);
            $table->string('verification_status', 10)->default('pending'); // pending / verified / rejected
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('rejection_note', 500)->nullable();
            $table->timestamps();

            $table->index('verification_status');
        });

        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('alumni_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject', 150);
            $table->string('status', 10)->default('pending'); // pending / accepted / declined / closed
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });

        Schema::create('consultation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('consultation_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reporter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason', 1000);
            $table->string('status', 10)->default('open'); // open / resolved
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->string('resolution_note', 1000)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_reports');
        Schema::dropIfExists('consultation_messages');
        Schema::dropIfExists('consultations');
        Schema::dropIfExists('alumni_profiles');
    }
};
