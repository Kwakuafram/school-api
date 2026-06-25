<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_arms', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();

            $table->foreignUuid('campus_id')
                ->nullable()
                ->constrained('campuses')
                ->nullOnDelete();

            $table->foreignUuid('class_level_id')
                ->constrained('class_levels')
                ->cascadeOnDelete();

            $table->string('name', 100);
            $table->string('code', 50);
            $table->unsignedInteger('capacity')->nullable();
            $table->string('status', 30)->default('active');
            $table->jsonb('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'class_level_id', 'code']);
            $table->index(['school_id', 'campus_id']);
            $table->index(['school_id', 'class_level_id']);
            $table->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_arms');
    }
};
