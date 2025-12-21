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
        Schema::create('imported_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('backlog_issue_id');
            $table->string('issue_key'); // BLG-1 形式
            $table->string('summary');
            $table->text('description')->nullable();
            $table->string('issue_type')->nullable();
            $table->string('issue_type_color')->nullable();
            $table->string('priority')->nullable();
            $table->string('status')->nullable();
            $table->string('status_color')->nullable();
            $table->date('due_date')->nullable();
            $table->date('start_date')->nullable();
            $table->decimal('estimated_hours', 5, 2)->nullable();
            $table->decimal('actual_hours', 5, 2)->nullable();
            $table->string('milestone')->nullable();
            $table->string('assignee_name')->nullable();
            $table->string('project_id')->nullable();
            $table->string('backlog_url')->nullable();
            $table->timestamp('backlog_created_at')->nullable();
            $table->timestamp('backlog_updated_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'backlog_issue_id']);
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imported_issues');
    }
};
