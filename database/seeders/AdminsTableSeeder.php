<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin role
        $adminRole = \DB::table('roles')->where('type', 'admin')->first();
        
        if (!$adminRole) {
            $this->command->error('Admin role not found. Make sure RolesTableSeeder has been run first.');
            return;
        }

        $admins = [
            [
                'name' => 'Wafae Labib El Idrissi',
                'email' => 'wafae.elidrissi@um6p.ma',
            ],
            [
                'name' => 'Marya Joudani',
                'email' => 'marya.joudani@um6p.ma',
            ],
            [
                'name' => 'Taha Mennani',
                'email' => 'Taha.mennani@um6p.ma',
            ],
            [
                'name' => 'Mamoun Ghallab',
                'email' => 'mamoun.ghallab@um6p.ma',
            ]
        ];

        foreach ($admins as $admin) {
            // Create user for admin
            $userId = \DB::table('users')->insertGetId([
                'name' => $admin['name'],
                'email' => $admin['email'],
                'email_verified_at' => now(),
                'password' => \Hash::make('password123'),
                'role_id' => $adminRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create admin record linked to user
            \DB::table('admins')->insert([
                'email_um6p' => $admin['email'],
                'department' => 'Cultur.Ed',
                'program' => 'INSPIRE',
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Admin users and admin records created successfully.');
    }
}
