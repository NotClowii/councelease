<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->id('session_id');
            $table->foreignId('appointment_id')->unique()->constrained('appointments', 'appointment_id')->restrictOnDelete();
            $table->foreignId('student_id')->constrained('students', 'student_id')->restrictOnDelete();
            $table->foreignId('counselor_id')->constrained('counselors', 'counselor_id')->restrictOnDelete();
            $table->dateTime('session_datetime');
            $table->integer('actual_duration_minutes')->nullable();
            $table->enum('session_status', ['ongoing', 'completed', 'cancelled'])->default('ongoing');
            $table->string('session_type', 50)->default('individual')->comment('individual, group, follow_up');
            $table->text('session_summary')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'session_datetime']);
            $table->index(['counselor_id', 'session_datetime']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
