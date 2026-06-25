<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_terms', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();

            $table->foreignUuid('academic_year_id')
                ->constrained('academic_years')
                ->cascadeOnDelete();

            $table->string('name', 50);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->string('status', 30)->default('active');
            $table->jsonb('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['academic_year_id', 'name']);
            $table->index(['school_id', 'academic_year_id']);
            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_terms');
    }
};
