<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id('report_id');
            $table->string('report_title', 150);
            $table->string('report_type', 50)->comment('session_summary, counselor_workload, student_visit, monthly, annual');
            $table->foreignId('generated_by')->constrained('users', 'user_id')->restrictOnDelete();
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->json('filters_applied')->nullable();
            $table->string('file_path')->nullable();
            $table->enum('status', ['generating', 'completed', 'failed'])->default('completed');
            $table->timestamps();

            $table->index(['generated_by', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
