<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_request_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('purchase_request_id');
            $table->unsignedBigInteger('user_id')->nullable();

            // User role snapshot when doing the action
            $table->string('role_name', 100)->nullable();

            // Action information
            $table->string('action', 100);
            $table->string('from_status', 100)->nullable();
            $table->string('to_status', 100)->nullable();

            $table->string('from_step', 100)->nullable();
            $table->string('to_step', 100)->nullable();

            // Remarks for return, reject, hold, split, etc.
            $table->text('remarks')->nullable();

            // Optional extra data as JSON
            $table->json('payload')->nullable();

            $table->timestamp('acted_at')->nullable();

            $table->timestamps();

            $table->index('purchase_request_id', 'pr_logs_pr_id_idx');
            $table->index('user_id', 'pr_logs_user_id_idx');
            $table->index('action', 'pr_logs_action_idx');
            $table->index('acted_at', 'pr_logs_acted_at_idx');

            $table->foreign('purchase_request_id', 'pr_logs_pr_fk')
                ->references('id')
                ->on('purchase_requests')
                ->cascadeOnDelete();

            $table->foreign('user_id', 'pr_logs_user_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_request_logs');
    }
};
