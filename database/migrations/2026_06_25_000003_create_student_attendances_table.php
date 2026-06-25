<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();

            $table->foreignUuid('attendance_session_id')
                ->constrained('attendance_sessions')
                ->cascadeOnDelete();

            $table->foreignUuid('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->string('status', 30); // present, absent, late, excused

            $table->text('notes')->nullable();

            $table->foreignUuid('recorded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->jsonb('metadata')->nullable();

            $table->timestamps();

            $table->unique(['attendance_session_id', 'student_id']);

            $table->index(['school_id', 'student_id', 'status']);
            $table->index(['school_id', 'attendance_session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_attendances');
    }
};
