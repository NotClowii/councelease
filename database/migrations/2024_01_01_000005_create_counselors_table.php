<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counselors', function (Blueprint $table) {
            $table->id('counselor_id');
            $table->foreignId('user_id')->unique()->constrained('users', 'user_id')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments', 'department_id')->nullOnDelete();
            $table->string('specialization', 100)->nullable();
            $table->string('license_number', 50)->nullable();
            $table->integer('max_appointments_per_day')->default(8);
            $table->json('available_days')->nullable()->comment('Array of day numbers: 0=Sun,1=Mon,...6=Sat');
            $table->time('available_from')->default('08:00:00');
            $table->time('available_until')->default('17:00:00');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counselors');
    }
};
