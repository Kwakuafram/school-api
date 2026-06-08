<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();

            $table->foreignUuid('campus_id')
                ->nullable()
                ->constrained('campuses')
                ->nullOnDelete();

            $table->string('admission_number');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');

            $table->string('preferred_name')->nullable();
            $table->string('gender', 30)->nullable();
            $table->date('date_of_birth')->nullable();

            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->string('nationality')->nullable();
            $table->string('religion')->nullable();
            $table->string('blood_group', 10)->nullable();

            $table->text('address')->nullable();
            $table->string('photo_path')->nullable();

            $table->date('admission_date')->nullable();
            $table->string('status', 30)->default('active');

            $table->jsonb('medical_info')->nullable();
            $table->jsonb('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'admission_number']);
            $table->index(['school_id', 'campus_id']);
            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'last_name', 'first_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};