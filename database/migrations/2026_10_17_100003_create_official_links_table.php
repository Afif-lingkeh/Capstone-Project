<?php
// database/migrations/2026_10_17_100003_create_official_links_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('official_links', function (Blueprint $table) {
            $table->id();
            // restrict: institusi yang masih punya tautan tidak bisa dihapus tanpa sengaja.
            $table->foreignId('institution_id')->constrained()->restrictOnDelete();
            $table->string('label', 100);                  // Situs Utama, Penerimaan Mahasiswa Baru, ...
            $table->string('url', 500)->unique();
            $table->string('domain')->index();             // diisi otomatis dari url, tanpa "www."
            $table->string('status', 20)->default('pending')->index();

            $table->text('verification_source')->nullable(); // bukti: PDDikti, surat resmi, dsb.
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('review_note')->nullable();          // alasan ditolak / catatan admin

            $table->timestamp('last_checked_at')->nullable();
            $table->string('last_check_result', 20)->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('official_links');
    }
};
