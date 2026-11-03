<?php
// database/migrations/2026_10_17_100004_create_link_check_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('link_check_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('official_link_id')->constrained()->cascadeOnDelete();
            $table->timestamp('checked_at')->index();
            $table->unsignedSmallInteger('http_status')->nullable(); // null = tidak tersambung
            $table->text('final_url')->nullable();                    // alamat akhir setelah redirect
            $table->string('result', 20);
            $table->unsignedInteger('response_ms')->nullable();
            $table->string('error_message')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('link_check_logs');
    }
};
