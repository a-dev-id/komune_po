<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PurchasingLiteUserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = '1234@1234';

        $users = [
            [
                'name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@purchasing-lite.local',
                'role' => 'admin',
                'department_name' => null,
            ],
            [
                'name' => 'IT Requester',
                'username' => 'it',
                'email' => 'it@purchasing-lite.local',
                'role' => 'requester',
                'department_name' => 'IT',
            ],
            [
                'name' => 'Housekeeping Requester',
                'username' => 'housekeeping',
                'email' => 'housekeeping@purchasing-lite.local',
                'role' => 'requester',
                'department_name' => 'Housekeeping & Garden',
            ],
            [
                'name' => 'Engineering Requester',
                'username' => 'engineering',
                'email' => 'engineering@purchasing-lite.local',
                'role' => 'requester',
                'department_name' => 'Engineering',
            ],
            [
                'name' => 'F&B Product Requester',
                'username' => 'fbproduct',
                'email' => 'fbproduct@purchasing-lite.local',
                'role' => 'requester',
                'department_name' => 'F&B Product',
            ],
            [
                'name' => 'F&B Service Requester',
                'username' => 'fbservice',
                'email' => 'fbservice@purchasing-lite.local',
                'role' => 'requester',
                'department_name' => 'F&B Service',
            ],
            [
                'name' => 'Essence Spa Requester',
                'username' => 'spa',
                'email' => 'spa@purchasing-lite.local',
                'role' => 'requester',
                'department_name' => 'Essence Spa',
            ],
            [
                'name' => 'Sales & Marketing Requester',
                'username' => 'sales',
                'email' => 'sales@purchasing-lite.local',
                'role' => 'requester',
                'department_name' => 'Sales & Marketing',
            ],
            [
                'name' => 'Purchasing',
                'username' => 'purchasing',
                'email' => 'purchasing@purchasing-lite.local',
                'role' => 'purchasing',
                'department_name' => 'Purchasing',
            ],
            [
                'name' => 'Cost Control',
                'username' => 'costcontrol',
                'email' => 'costcontrol@purchasing-lite.local',
                'role' => 'cost_control',
                'department_name' => 'Cost Control',
            ],
            [
                'name' => 'General Manager',
                'username' => 'gm',
                'email' => 'gm@purchasing-lite.local',
                'role' => 'gm',
                'department_name' => 'Management',
            ],
            [
                'name' => 'OR',
                'username' => 'owner',
                'email' => 'owner@purchasing-lite.local',
                'role' => 'owner',
                'department_name' => 'OR',
            ],
            [
                'name' => 'Financial Controller',
                'username' => 'fc',
                'email' => 'fc@purchasing-lite.local',
                'role' => 'financial_controller',
                'department_name' => 'Finance',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['username' => $user['username']],
                [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'password' => Hash::make($defaultPassword),
                    'role' => $user['role'],
                    'department_name' => $user['department_name'],
                    'is_active' => true,
                ]
            );
        }
    }
}
