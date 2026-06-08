<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_guardian', function (Blueprint $table) {
            $table->foreignUuid('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->foreignUuid('guardian_id')
                ->constrained('guardians')
                ->cascadeOnDelete();

            $table->string('relationship', 50);
            $table->boolean('is_primary')->default(false);
            $table->boolean('can_pick_up')->default(false);
            $table->boolean('receives_sms')->default(true);
            $table->boolean('receives_email')->default(true);

            $table->timestamps();

            $table->primary(['student_id', 'guardian_id']);
            $table->index(['guardian_id']);
            $table->index(['student_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_guardian');
    }
};