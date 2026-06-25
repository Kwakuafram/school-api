<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_levels', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();

            $table->string('name', 100);
            $table->string('code', 50);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 30)->default('active');
            $table->jsonb('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code']);
            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_levels');
    }
};
