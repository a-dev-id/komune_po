<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (! Schema::hasColumn('vendors', 'category')) {
                $table->string('category', 100)->nullable()->after('name');
                $table->index('category', 'vendors_category_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (Schema::hasColumn('vendors', 'category')) {
                $table->dropIndex('vendors_category_idx');
                $table->dropColumn('category');
            }
        });
    }
};
