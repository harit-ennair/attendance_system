<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LinkStudentsToUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all students that don't have a user_id yet
        $students = DB::table('students')->whereNull('user_id')->get();
        
        // Get the student role ID
        $studentRole = DB::table('roles')->where('type', 'student')->first();
        
        if (!$studentRole) {
            $this->command->error('Student role not found. Make sure RolesTableSeeder has been run first.');
            return;
        }
        
        foreach ($students as $student) {
            // Check if a user with this email already exists
            $existingUser = DB::table('users')->where('email', $student->email_um6p)->first();
            
            if ($existingUser) {
                // Link existing user to student
                DB::table('students')
                    ->where('id', $student->id)
                    ->update(['user_id' => $existingUser->id]);
                    
                // Update user role if not set
                if (!$existingUser->role_id) {
                    DB::table('users')
                        ->where('id', $existingUser->id)
                        ->update(['role_id' => $studentRole->id]);
                }
            } else {
                // Create a new user for the student
                $userId = DB::table('users')->insertGetId([
                    'name' => $student->cin, // Using CIN as name since we don't have nom_complet
                    'email' => $student->email_um6p,
                    'email_verified_at' => now(),
                    'password' => Hash::make('password123'), // Default password
                    'role_id' => $studentRole->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Update the student with the user_id
                DB::table('students')
                    ->where('id', $student->id)
                    ->update(['user_id' => $userId]);
            }
        }
        
        $this->command->info('Students linked to users successfully.');
    }
}
