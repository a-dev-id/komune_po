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
        if (! Schema::hasColumn('purchase_requests', 'received_date')) {
            Schema::table('purchase_requests', function (Blueprint $table) {
                $table->date('received_date')->nullable();
            });
        }

        if (! Schema::hasColumn('purchase_requests', 'received_at')) {
            Schema::table('purchase_requests', function (Blueprint $table) {
                $table->timestamp('received_at')->nullable();
            });
        }

        if (! Schema::hasColumn('purchase_requests', 'received_remarks')) {
            Schema::table('purchase_requests', function (Blueprint $table) {
                $table->text('received_remarks')->nullable();
            });
        }

        if (! Schema::hasColumn('purchase_requests', 'handover_date')) {
            Schema::table('purchase_requests', function (Blueprint $table) {
                $table->date('handover_date')->nullable();
            });
        }

        if (! Schema::hasColumn('purchase_requests', 'handed_over_at')) {
            Schema::table('purchase_requests', function (Blueprint $table) {
                $table->timestamp('handed_over_at')->nullable();
            });
        }

        if (! Schema::hasColumn('purchase_requests', 'handover_remarks')) {
            Schema::table('purchase_requests', function (Blueprint $table) {
                $table->text('handover_remarks')->nullable();
            });
        }

        if (! Schema::hasColumn('purchase_requests', 'financial_controller_remarks')) {
            Schema::table('purchase_requests', function (Blueprint $table) {
                $table->text('financial_controller_remarks')->nullable();
            });
        }

        if (! Schema::hasColumn('purchase_requests', 'completed_at')) {
            Schema::table('purchase_requests', function (Blueprint $table) {
                $table->timestamp('completed_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = [
            'received_date',
            'received_at',
            'received_remarks',
            'handover_date',
            'handed_over_at',
            'handover_remarks',
            'financial_controller_remarks',
            'completed_at',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn('purchase_requests', $column)) {
                Schema::table('purchase_requests', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
