<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_request_vendor_offers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_request_id')
                ->constrained('purchase_requests')
                ->cascadeOnDelete();

            $table->foreignId('vendor_id')
                ->nullable()
                ->constrained('vendors')
                ->nullOnDelete();

            // Vendor snapshot, so old PR data does not change if vendor master data is updated
            $table->string('vendor_name_snapshot', 191);
            $table->string('vendor_phone_snapshot', 100)->nullable();
            $table->string('vendor_email_snapshot', 191)->nullable();
            $table->text('vendor_address_snapshot')->nullable();

            // Quotation / offer information
            $table->string('quotation_number', 100)->nullable();
            $table->string('quotation_file', 191)->nullable();

            $table->string('currency', 10)->default('IDR');
            $table->decimal('offer_total', 15, 2)->nullable();

            $table->unsignedSmallInteger('lead_time_days')->nullable();
            $table->text('notes')->nullable();

            // Cost Control selected vendor
            $table->boolean('is_selected_by_cost_control')->default(false);
            $table->foreignId('selected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('selected_at')->nullable();

            // Created by purchasing
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index('purchase_request_id');
            $table->index('vendor_id');
            $table->index('is_selected_by_cost_control');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_request_vendor_offers');
    }
};
