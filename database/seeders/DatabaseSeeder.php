<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        if (app()->environment() !== 'production') {
            $admin = User::firstOrCreate(
                ['email' => 'admin@exam.test'],
                ['name' => 'Admin User', 'password' => Hash::make('password'), 'email_verified_at' => now()]
            );
            $admin->assignRole('admin');

            $proctor = User::firstOrCreate(
                ['email' => 'proctor@exam.test'],
                ['name' => 'Proctor User', 'password' => Hash::make('password'), 'email_verified_at' => now()]
            );
            $proctor->assignRole('proctor');

            $student = User::firstOrCreate(
                ['email' => 'student@exam.test'],
                ['name' => 'Student User', 'password' => Hash::make('password'), 'email_verified_at' => now()]
            );
            $student->assignRole('student');

            $teacher = User::firstOrCreate(
                ['email' => 'teacher@exam.test'],
                ['name' => 'Teacher User', 'password' => Hash::make('password'), 'email_verified_at' => now()]
            );
            $teacher->assignRole('teacher');
        }
    }
}
