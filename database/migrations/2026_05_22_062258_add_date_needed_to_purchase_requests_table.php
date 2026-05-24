<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_requests', 'date_needed')) {
                $table->date('date_needed')->nullable()->after('department_name');
                $table->index('date_needed', 'purchase_requests_date_needed_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_requests', 'date_needed')) {
                $table->dropIndex('purchase_requests_date_needed_idx');
                $table->dropColumn('date_needed');
            }
        });
    }
};
