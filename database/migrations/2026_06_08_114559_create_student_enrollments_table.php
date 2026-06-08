<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();

            $table->foreignUuid('campus_id')
                ->nullable()
                ->constrained('campuses')
                ->nullOnDelete();

            $table->foreignUuid('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->string('academic_year');
            $table->string('term')->nullable();

            // Temporary string fields until we create formal classes/class_arms tables.
            $table->string('class_level')->nullable();
            $table->string('class_arm')->nullable();

            $table->date('enrolled_at')->nullable();
            $table->date('exited_at')->nullable();

            $table->string('status', 30)->default('active');
            $table->jsonb('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'campus_id']);
            $table->index(['school_id', 'academic_year', 'term']);
            $table->index(['school_id', 'student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};
