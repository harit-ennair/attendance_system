<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'type' => 'super_admin',
                'display_name' => 'Super Administrator',
                'description' => 'Highest level administrator with full system access',
                'permissions' => json_encode([
                    'manage_users',
                    'manage_roles',
                    'manage_students',
                    'view_all_attendance',
                    'manage_system_settings',
                    'generate_reports'
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'System administrator with limited privileges',
                'permissions' => json_encode([
                    'manage_students',
                    'view_attendance',
                    'mark_attendance',
                    'generate_basic_reports'
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'student',
                'display_name' => 'Student',
                'description' => 'Student user with basic access',
                'permissions' => json_encode([
                    'view_own_attendance',
                    'update_own_profile'
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('roles')->insert($roles);
    }
}
