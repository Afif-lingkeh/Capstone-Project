<?php
// database/migrations/2026_11_04_100001_create_documents_and_avatars.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Foto profil, cita-cita santri, dan berkas santri. Semua file disimpan di disk privat (storage/app/private),
// diunduh lewat route yang memeriksa hak akses, tidak pernah lewat URL publik.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_path')->nullable()->after('phone');
        });

        Schema::table('santri_profiles', function (Blueprint $table) {
            $table->string('aspiration', 500)->nullable()->after('guardian_name'); // cita-cita & kampus impian, ditulis santri
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();   // santri pemilik berkas
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category', 20);
            $table->string('title', 150);
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type', 100);
            $table->unsignedInteger('size'); // byte
            $table->string('note', 500)->nullable();
            $table->string('status', 15)->default('pending'); // pending / reviewed / needs_fix
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('review_note', 500)->nullable();
            $table->timestamps();

            $table->index(['owner_id', 'category']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::table('santri_profiles', fn (Blueprint $table) => $table->dropColumn('aspiration'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('avatar_path'));
    }
};
