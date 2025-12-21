<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('study_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('imported_issue_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('plan_type', ['study', 'work', 'break', 'review'])->default('study');
            $table->date('scheduled_date');
            $table->time('scheduled_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('duration_minutes')->default(60);
            $table->integer('priority')->default(5); // 1-10
            $table->text('ai_reason')->nullable();
            $table->enum('status', ['planned', 'in_progress', 'completed', 'skipped'])->default('planned');
            $table->timestamps();

            $table->index(['user_id', 'scheduled_date']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_plans');
    }
};
