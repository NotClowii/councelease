<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id('appointment_id');
            $table->foreignId('student_id')->constrained('students', 'student_id')->restrictOnDelete();
            $table->foreignId('counselor_id')->constrained('counselors', 'counselor_id')->restrictOnDelete();
            $table->dateTime('appointment_datetime');
            $table->integer('duration_minutes')->default(60);
            $table->enum('appointment_status', [
                'pending',
                'approved',
                'rescheduled',
                'cancelled',
                'completed',
                'no_show'
            ])->default('pending');
            $table->string('concern_type', 100)->nullable()->comment('Academic, Personal, Career, etc.');
            $table->text('concern_description')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Prevent double-booking: same counselor can't have two appointments at the same time
            $table->index(['counselor_id', 'appointment_datetime']);
            $table->index(['student_id', 'appointment_datetime']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
