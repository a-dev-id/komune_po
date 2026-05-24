<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_request_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_request_id')
                ->constrained('purchase_requests')
                ->cascadeOnDelete();

            // Item order inside the PR
            $table->unsignedInteger('sort_order')->default(1);

            // Requested item information
            $table->string('item_name', 191);
            $table->text('specification')->nullable();
            $table->decimal('quantity', 12, 2)->default(1);
            $table->string('unit', 50)->nullable();
            $table->date('needed_date')->nullable();

            // Optional requester estimated price
            $table->decimal('estimated_unit_price', 15, 2)->nullable();
            $table->decimal('estimated_total_price', 15, 2)->nullable();

            // Item remarks
            $table->text('requester_remarks')->nullable();
            $table->text('purchasing_remarks')->nullable();
            $table->text('cost_control_remarks')->nullable();
            $table->text('gm_remarks')->nullable();

            // GM item decision
            $table->string('gm_status', 50)->default('pending');

            /*
                GM item status examples:
                pending
                approved
                not_approved
                rejected
            */

            $table->foreignId('gm_decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('gm_decided_at')->nullable();

            // Split tracking
            $table->foreignId('split_from_item_id')->nullable()->constrained('purchase_request_items')->nullOnDelete();
            $table->foreignId('split_to_purchase_request_id')->nullable()->constrained('purchase_requests')->nullOnDelete();

            $table->timestamps();

            $table->index('purchase_request_id');
            $table->index(['purchase_request_id', 'gm_status']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_request_items');
    }
};
