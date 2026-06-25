<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();

            $table->foreignUuid('campus_id')
                ->nullable()
                ->constrained('campuses')
                ->nullOnDelete();

            $table->foreignUuid('academic_year_id')
                ->constrained('academic_years')
                ->cascadeOnDelete();

            $table->foreignUuid('academic_term_id')
                ->nullable()
                ->constrained('academic_terms')
                ->nullOnDelete();

            $table->foreignUuid('class_level_id')
                ->constrained('class_levels')
                ->cascadeOnDelete();

            $table->foreignUuid('class_arm_id')
                ->nullable()
                ->constrained('class_arms')
                ->nullOnDelete();

            $table->foreignUuid('subject_id')
                ->nullable()
                ->constrained('subjects')
                ->nullOnDelete();

            $table->foreignUuid('teacher_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('assignment_type', 50)->default('subject_teacher');
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->string('status', 30)->default('active');
            $table->jsonb('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'teacher_user_id']);
            $table->index(['school_id', 'academic_year_id', 'academic_term_id']);
            $table->index(['school_id', 'class_level_id', 'class_arm_id']);
            $table->index(['school_id', 'subject_id']);
            $table->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_assignments');
    }
};
