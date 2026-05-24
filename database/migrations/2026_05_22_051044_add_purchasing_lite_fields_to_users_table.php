<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 100)->default('requester')->after('password');
            $table->string('department_name', 100)->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('department_name');

            $table->index('role', 'users_role_idx');
            $table->index('department_name', 'users_department_name_idx');
            $table->index(['role', 'department_name'], 'users_role_department_idx');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_idx');
            $table->dropIndex('users_department_name_idx');
            $table->dropIndex('users_role_department_idx');

            $table->dropColumn([
                'role',
                'department_name',
                'is_active',
            ]);
        });
    }
};
