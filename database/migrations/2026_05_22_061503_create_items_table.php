<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            $table->string('name', 191);
            $table->string('sku', 100)->nullable();
            $table->string('category', 100)->nullable();
            $table->string('brand', 100)->nullable();
            $table->string('default_unit', 50)->nullable();
            $table->text('default_specification')->nullable();

            $table->decimal('last_price', 15, 2)->nullable();
            $table->string('currency', 10)->default('IDR');

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('name');
            $table->index('sku');
            $table->index('category');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
