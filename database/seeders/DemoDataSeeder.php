<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Project;
use App\Models\Report;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@projectflow.test')->first();
        $manager = User::where('email', 'manager@projectflow.test')->first();
        $manager2 = User::where('email', 'nina@projectflow.test')->first();
        $staff = User::whereIn('email', ['staff@projectflow.test', 'dana@projectflow.test', 'tobias@projectflow.test'])->get();
        $finance = User::where('email', 'finance@projectflow.test')->first();

        $customers = collect([
            ['company_name' => 'Northwind Traders', 'contact_name' => 'Elena Vasquez', 'email' => 'elena@northwind.example', 'city' => 'San Francisco', 'country' => 'USA'],
            ['company_name' => 'Vertex Health', 'contact_name' => 'Dr. Samir Haddad', 'email' => 'samir@vertexhealth.example', 'city' => 'Austin', 'country' => 'USA'],
            ['company_name' => 'Lumen Studios', 'contact_name' => 'Chloe Brandt', 'email' => 'chloe@lumen.example', 'city' => 'Berlin', 'country' => 'Germany'],
            ['company_name' => 'Orbit Logistics', 'contact_name' => 'Kenji Sato', 'email' => 'kenji@orbitlog.example', 'city' => 'Osaka', 'country' => 'Japan'],
            ['company_name' => 'Harbor & Co.', 'contact_name' => 'Mia Doyle', 'email' => 'mia@harborco.example', 'city' => 'Dublin', 'country' => 'Ireland'],
        ])->map(fn ($c) => Customer::firstOrCreate(['email' => $c['email']], $c + ['status' => 'active']));

        $projectBlueprints = [
            ['Web Platform Redesign', 0, 'in_progress', 'high', 78, 48500, 34],
            ['Mobile App MVP', 1, 'in_progress', 'urgent', 46, 72000, 12],
            ['Billing Automation', 3, 'planning', 'medium', 12, 26000, 58],
            ['Brand & Website Refresh', 2, 'on_hold', 'low', 33, 15000, -4],
            ['Logistics Dashboard', 3, 'in_progress', 'high', 61, 54000, 21],
            ['Patient Portal', 1, 'completed', 'medium', 100, 39000, -30],
            ['Marketing Site Revamp', 4, 'in_progress', 'medium', 55, 18500, 9],
            ['Data Warehouse Migration', 0, 'planning', 'high', 8, 87000, 74],
            ['Customer Loyalty App', 2, 'in_progress', 'low', 40, 31000, 27],
            ['API Gateway Hardening', 3, 'completed', 'high', 100, 22500, -55],
            ['Design System v2', 2, 'in_progress', 'medium', 66, 27500, 16],
            ['Field Ops Tablet App', 1, 'on_hold', 'low', 24, 44000, -11],
        ];

        $managers = [$manager, $manager2];

        foreach ($projectBlueprints as $i => [$name, $customerIdx, $status, $priority, $progress, $budget, $deadlineOffset]) {
            $project = Project::firstOrCreate(
                ['name' => $name],
                [
                    'description' => "End-to-end delivery for {$name} including discovery, build and handover.",
                    'customer_id' => $customers[$customerIdx]->id,
                    'manager_id' => $managers[$i % 2]->id,
                    'budget' => $budget,
                    'progress' => $progress,
                    'status' => $status,
                    'priority' => $priority,
                    'start_date' => Carbon::now()->subDays(60 - $i * 3),
                    'deadline' => Carbon::now()->addDays($deadlineOffset),
                ]
            );

            $project->staff()->syncWithoutDetaching(
                $staff->random(2)->pluck('id')->push($managers[$i % 2]->id)->all()
            );

            foreach (['Discovery & scope', 'Wireframes', 'Implementation', 'QA & testing', 'Client sign-off'] as $n => $title) {
                $taskStatus = match (true) {
                    $n < 2 => 'done',
                    $n === 2 && $progress > 50 => 'in_progress',
                    $n === 2 => 'todo',
                    default => 'todo',
                };

                Task::firstOrCreate(
                    ['project_id' => $project->id, 'title' => $title.' — '.$name],
                    [
                        'assignee_id' => $staff->random()->id,
                        'description' => 'Standard delivery checklist item.',
                        'status' => $taskStatus,
                        'priority' => $n >= 3 ? 'high' : 'medium',
                        'due_date' => Carbon::now()->addDays(rand(-5, 30)),
                        'estimate_hours' => rand(4, 24),
                        'completed_at' => $taskStatus === 'done' ? Carbon::now()->subDays(rand(1, 20)) : null,
                    ]
                );
            }

            // Revenue side: orders
            Order::firstOrCreate(
                ['reference' => 'ORD-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT)],
                [
                    'customer_id' => $project->customer_id,
                    'project_id' => $project->id,
                    'title' => 'Phase 1 invoice — '.$name,
                    'subtotal' => $budget * 0.6,
                    'tax' => $budget * 0.06,
                    'total' => $budget * 0.66,
                    'status' => ['paid', 'pending', 'overdue'][$i % 3],
                    'issued_at' => Carbon::now()->subDays(rand(5, 50)),
                    'due_at' => Carbon::now()->addDays(rand(-10, 25)),
                ]
            );

            // Cost side: expenses
            foreach ([['software', 'Licensing', 0.08], ['contractors', 'Freelance sprint support', 0.18], ['operations', 'Team expenses', 0.05]] as [$cat, $label, $ratio]) {
                Expense::firstOrCreate(
                    ['project_id' => $project->id, 'title' => $label.' — '.$name],
                    [
                        'user_id' => $finance->id,
                        'category' => $cat,
                        'amount' => round($budget * $ratio, 2),
                        'status' => 'approved',
                        'spent_on' => Carbon::now()->subDays(rand(2, 45)),
                    ]
                );
            }
        }

        foreach ([
            ['Q3 Financial Summary', 'finance', 'ready'],
            ['Project Delivery Performance', 'project', 'ready'],
            ['Team Task Throughput', 'tasks', 'processing'],
            ['Client Profitability Margin', 'finance', 'ready'],
            ['Sprint Velocity Report', 'performance', 'ready'],
        ] as $i => [$name, $type, $status]) {
            Report::firstOrCreate(['name' => $name], [
                'type' => $type,
                'period_start' => Carbon::now()->subMonths(3)->startOfMonth(),
                'period_end' => Carbon::now()->endOfMonth(),
                'status' => $status,
                'generated_by' => $i % 2 ? $admin->id : $finance->id,
                'generated_at' => Carbon::now()->subDays($i * 4 + 1),
            ]);
        }
    }
}
