<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_notes', function (Blueprint $table) {
            $table->id('note_id');
            $table->foreignId('session_id')->constrained('sessions', 'session_id')->cascadeOnDelete();
            $table->foreignId('counselor_id')->constrained('counselors', 'counselor_id')->restrictOnDelete();
            $table->text('notes_content');
            $table->string('note_type', 50)->default('progress')->comment('initial, progress, termination, referral');
            $table->boolean('is_confidential')->default(true);
            $table->json('interventions_used')->nullable();
            $table->text('follow_up_actions')->nullable();
            $table->date('next_session_recommended')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['session_id', 'counselor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_notes');
    }
};
