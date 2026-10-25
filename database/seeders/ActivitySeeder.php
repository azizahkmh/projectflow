<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        $samples = [
            ['created', 'Created project "Web Platform Redesign"', 'folder-plus', 0],
            ['updated', 'Marked task "QA & testing" as In Progress', 'check-circle', 1],
            ['logged_in', 'Signed in from San Francisco, CA', 'log-in', 2],
            ['approved', 'Approved expense claim of $1,240.00', 'wallet', 3],
            ['created', 'Issued invoice ORD-0004 for Northwind Traders', 'receipt', 4],
            ['updated', 'Moved project "Mobile App MVP" to In Progress', 'refresh', 5],
            ['deleted', 'Removed duplicate task in "Billing Automation"', 'trash', 6],
            ['exported', 'Exported Q3 Financial Summary as PDF', 'download', 7],
        ];

        foreach ($samples as $i => [$action, $description, $icon, $userIdx]) {
            Activity::create([
                'user_id' => $users[$userIdx % $users->count()]->id,
                'action' => $action,
                'description' => $description,
                'icon' => $icon,
                'ip_address' => '10.0.0.'.(10 + $i),
                'created_at' => Carbon::now()->subMinutes(($i + 1) * 47),
                'updated_at' => Carbon::now()->subMinutes(($i + 1) * 47),
            ]);
        }
    }
}
