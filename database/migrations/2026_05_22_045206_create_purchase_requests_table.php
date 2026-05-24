<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();

            // PR identity
            $table->string('pr_number', 100)->unique();
            $table->string('title', 191)->nullable();

            // Requester info
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('department_name', 100);

            // Current workflow status
            $table->string('status', 100)->default('draft');

            /*
                Status examples:
                draft
                submitted_to_purchasing
                returned_to_requester_by_purchasing
                submitted_to_cost_control
                returned_to_purchasing_by_cost_control
                returned_to_requester_by_cost_control
                submitted_to_gm
                approved_by_gm
                partially_approved_by_gm
                rejected_by_gm
                on_hold_by_gm
                auto_rejected_from_hold
                reactivated_by_requester
                submitted_to_owner
                approved_by_owner
                rejected_by_owner
                submitted_to_financial_controller
                pending
                on_progress
                waiting_payment
                paid_to_vendor
                on_shipping
                received_by_requester
                done
                cancelled
            */

            // Tracking current approval step
            $table->string('current_step', 100)->default('requester');

            /*
                Step examples:
                requester
                purchasing
                cost_control
                gm
                owner
                financial_controller
                completed
            */

            // Remarks
            $table->text('requester_remarks')->nullable();
            $table->text('purchasing_remarks')->nullable();
            $table->text('cost_control_remarks')->nullable();
            $table->text('gm_remarks')->nullable();
            $table->text('owner_remarks')->nullable();
            $table->text('financial_controller_remarks')->nullable();

            // GM hold logic
            $table->timestamp('hold_until')->nullable();
            $table->timestamp('hold_expired_at')->nullable();

            // Rejection tracking
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Split PR tracking
            $table->foreignId('split_from_id')->nullable()->constrained('purchase_requests')->nullOnDelete();
            $table->string('split_from_pr_number', 100)->nullable();

            // Final received tracking
            $table->string('received_by_name', 191)->nullable();
            $table->date('received_date')->nullable();

            // General timestamps
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('current_status_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'current_step']);
            $table->index('department_name');
            $table->index('requested_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
