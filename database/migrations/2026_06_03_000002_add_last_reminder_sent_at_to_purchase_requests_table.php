<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_requests', 'last_reminder_sent_at')) {
                $table->timestamp('last_reminder_sent_at')
                    ->nullable()
                    ->after('current_status_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_requests', 'last_reminder_sent_at')) {
                $table->dropColumn('last_reminder_sent_at');
            }
        });
    }
};
