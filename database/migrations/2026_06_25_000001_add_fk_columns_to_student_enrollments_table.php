<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            // Make legacy string column nullable now that FK column carries this data
            $table->string('academic_year')->nullable()->change();

            $table->foreignUuid('academic_year_id')
                ->nullable()
                ->after('student_id')
                ->constrained('academic_years')
                ->nullOnDelete();

            $table->foreignUuid('academic_term_id')
                ->nullable()
                ->after('academic_year_id')
                ->constrained('academic_terms')
                ->nullOnDelete();

            $table->foreignUuid('class_level_id')
                ->nullable()
                ->after('academic_term_id')
                ->constrained('class_levels')
                ->nullOnDelete();

            $table->foreignUuid('class_arm_id')
                ->nullable()
                ->after('class_level_id')
                ->constrained('class_arms')
                ->nullOnDelete();

            $table->index(['school_id', 'academic_year_id', 'academic_term_id']);
            $table->index(['school_id', 'class_level_id', 'class_arm_id']);
        });
    }

    public function down(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['academic_term_id']);
            $table->dropForeign(['class_level_id']);
            $table->dropForeign(['class_arm_id']);
            $table->dropIndex(['school_id', 'academic_year_id', 'academic_term_id']);
            $table->dropIndex(['school_id', 'class_level_id', 'class_arm_id']);
            $table->dropColumn(['academic_year_id', 'academic_term_id', 'class_level_id', 'class_arm_id']);
        });
    }
};
