<?php
// database/migrations/2026_11_03_400001_create_counseling_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Fase 7: konseling & catatan kasus. Kolom bertanda (enc) disimpan terenkripsi lewat cast "encrypted" di model,
// sehingga isi cerita dan catatan tidak terbaca walau database bocor.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardian_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('users')->cascadeOnDelete();
            $table->string('guardian_name');
            $table->string('relationship', 50);
            $table->string('method', 20); // tertulis / lisan / daring
            $table->date('consented_at');
            $table->string('policy_version', 10);
            $table->string('notes', 500)->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('counselor_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counselor_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->timestamps();
            $table->index(['starts_at', 'ends_at']);
        });

        Schema::create('counseling_cases', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // K-2026-014
            $table->foreignId('santri_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('counselor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 10)->default('open');
            $table->string('urgency', 10)->default('medium');
            $table->timestamp('closed_at')->nullable();
            $table->text('closing_summary')->nullable(); // (enc)
            $table->timestamps();
            $table->index(['counselor_id', 'status']);
        });

        Schema::create('counseling_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('users')->cascadeOnDelete();
            $table->string('topic', 20);
            $table->text('story');                 // (enc)
            $table->string('preferred_time', 150)->nullable();
            $table->string('urgency', 10);         // menurut santri
            $table->string('status', 10)->default('pending');
            $table->boolean('is_crisis')->default(false);
            $table->foreignId('counseling_case_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'is_crisis']);
        });

        Schema::create('counseling_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counseling_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('counselor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('held_at');
            $table->string('mode', 10);
            $table->text('chief_complaint');      // (enc)
            $table->text('assessment');           // (enc)
            $table->text('intervention');         // (enc)
            $table->text('follow_up_plan');       // (enc)
            $table->string('urgency', 10);
            $table->dateTime('next_session_at')->nullable();
            $table->timestamps();
        });

        Schema::create('case_handovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counseling_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_counselor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('to_counselor_id')->constrained('users')->cascadeOnDelete();
            $table->text('reason');               // (enc)
            $table->string('status', 10)->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('crisis_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('users')->cascadeOnDelete();
            $table->string('source', 10);              // request / chat
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('status', 15)->default('open');
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
            $table->index('status');
        });

        // Log audit: hanya metadata (siapa, kapan, apa yang dibuka). Isi catatan tidak pernah disimpan di sini.
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 50);
            $table->string('subject_type', 50)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->foreignId('santri_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        foreach (['audit_logs', 'crisis_alerts', 'case_handovers', 'counseling_sessions', 'counseling_requests', 'counseling_cases', 'counselor_shifts', 'guardian_consents'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
