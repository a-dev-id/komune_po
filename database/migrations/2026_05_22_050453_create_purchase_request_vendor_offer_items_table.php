<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_request_vendor_offer_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('purchase_request_vendor_offer_id');
            $table->unsignedBigInteger('purchase_request_item_id');

            // Price from this vendor for this item
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('total_price', 15, 2)->default(0);

            // Optional item-specific offer detail
            $table->string('brand', 191)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('purchase_request_vendor_offer_id', 'prvoi_offer_id_idx');
            $table->index('purchase_request_item_id', 'prvoi_item_id_idx');

            $table->unique(
                ['purchase_request_vendor_offer_id', 'purchase_request_item_id'],
                'prvoi_offer_item_unique'
            );

            $table->foreign('purchase_request_vendor_offer_id', 'prvoi_offer_fk')
                ->references('id')
                ->on('purchase_request_vendor_offers')
                ->cascadeOnDelete();

            $table->foreign('purchase_request_item_id', 'prvoi_item_fk')
                ->references('id')
                ->on('purchase_request_items')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_request_vendor_offer_items');
    }
};
