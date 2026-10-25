<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Avery Coleman', 'email' => 'admin@projectflow.test', 'role' => 'super-admin', 'job_title' => 'Founder & Owner', 'avatar_color' => '#84CC16'],
            ['name' => 'Jordan Reyes', 'email' => 'manager@projectflow.test', 'role' => 'project-manager', 'job_title' => 'Senior Project Manager', 'avatar_color' => '#0EA5E9'],
            ['name' => 'Priya Nandakumar', 'email' => 'staff@projectflow.test', 'role' => 'staff', 'job_title' => 'Product Designer', 'avatar_color' => '#A855F7'],
            ['name' => 'Marcus Bell', 'email' => 'finance@projectflow.test', 'role' => 'finance', 'job_title' => 'Finance Controller', 'avatar_color' => '#F59E0B'],
            ['name' => 'Dana Whitfield', 'email' => 'dana@projectflow.test', 'role' => 'staff', 'job_title' => 'Backend Engineer', 'avatar_color' => '#22C55E'],
            ['name' => 'Tobias Lang', 'email' => 'tobias@projectflow.test', 'role' => 'staff', 'job_title' => 'QA Specialist', 'avatar_color' => '#F97316'],
            ['name' => 'Nina Okafor', 'email' => 'nina@projectflow.test', 'role' => 'project-manager', 'job_title' => 'Delivery Manager', 'avatar_color' => '#6366F1'],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'job_title' => $data['job_title'],
                    'avatar_color' => $data['avatar_color'],
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]
            );

            $user->syncRoles([$data['role']]);
        }
    }
}
