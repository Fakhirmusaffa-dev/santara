<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('instruction')->nullable();
            $table->string('status', 20)->default('baru'); // baru|diterima|ditolak|selesai
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['complaint_id']);
            $table->index(['assigned_to', 'status']);
        });

        Schema::create('handling_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 180)->nullable();
            $table->text('note');
            $table->unsignedTinyInteger('progress')->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->timestamps();
            $table->index(['complaint_id', 'created_at']);
        });

        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 30)->default('penyelesaian'); // penyelesaian|eskalasi|akses_sensitif
            $table->string('status', 20)->default('menunggu'); // menunggu|disetujui|ditolak
            $table->text('request_note')->nullable();
            $table->text('decision_note')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
            $table->index(['complaint_id', 'status']);
            $table->index(['status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvals');
        Schema::dropIfExists('handling_logs');
        Schema::dropIfExists('assignments');
    }
};
