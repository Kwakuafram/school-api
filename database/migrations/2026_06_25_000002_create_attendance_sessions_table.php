<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
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

            $table->date('session_date');
            $table->string('period', 30)->default('morning');
            $table->string('status', 30)->default('open');

            $table->foreignUuid('submitted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignUuid('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->text('notes')->nullable();
            $table->jsonb('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'academic_year_id', 'academic_term_id']);
            $table->index(['school_id', 'class_arm_id', 'status']);
            $table->index(['school_id', 'session_date']);
            $table->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
