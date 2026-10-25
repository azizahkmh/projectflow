<x-layouts.app title="Dashboard">

    <div class="mb-6">
        <x-page-header
            eyebrow="Overview"
            title="Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }}, {{ explode(' ', auth()->user()->name)[0] }}"
            subtitle="Here's how your projects and finances are tracking today."
        >
            <x-slot:actions>
                <button type="button" @click="$store.modals.show('new-task')" class="btn-ghost">
                    <x-icon name="plus" class="size-4" /> New task
                </button>
                <button type="button" @click="$store.modals.show('new-project')" class="btn-primary">
                    <x-icon name="folder-plus" class="size-4" /> New project
                </button>
            </x-slot:actions>
        </x-page-header>
    </div>

    {{-- Stat cards --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Active Projects" :value="$stats['active_projects']" icon="folder-kanban" delta="+3" trend="up" hint="vs. last month" tone="brand" />
        <x-stat-card label="Pending Tasks" :value="$stats['pending_tasks']" icon="list-checks" delta="-6%" trend="down" hint="backlog shrinking" tone="dark" />
        <x-stat-card label="Revenue" :value="'$'.number_format($stats['revenue'] / 1000, 1).'k'" icon="trending-up" delta="+18%" trend="up" hint="paid orders" tone="amber" />
        <x-stat-card label="Expenses" :value="'$'.number_format($stats['expenses'] / 1000, 1).'k'" icon="wallet" delta="+4%" trend="up" hint="approved only" tone="rose" />
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-3">

        @unless ($hideCharts)
        {{-- Timeline chart --}}
        <div class="card xl:col-span-2">
            <div class="flex items-start justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="text-[14px] font-bold text-heading">Delivery velocity</h2>
                    <p class="mt-0.5 text-[12px] text-muted">Tasks created vs. completed, last 6 months</p>
                </div>
                <div class="flex items-center gap-3 text-[11px] font-semibold">
                    <span class="inline-flex items-center gap-1.5 text-slate-600">
                        <span class="size-2 rounded-full bg-brand-500"></span> Completed
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-slate-400">
                        <span class="size-2 rounded-full bg-slate-300"></span> Created
                    </span>
                </div>
            </div>
            <div class="px-3 py-4">
                <div class="h-[280px]">
                    <canvas id="velocityChart"></canvas>
                </div>
            </div>
        </div>
        @endunless

        @unless ($hideCharts)
        {{-- Task mix --}}
        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-[14px] font-bold text-heading">Task breakdown</h2>
                <p class="mt-0.5 text-[12px] text-muted">Across your visible work</p>
            </div>
            <div class="space-y-4 px-5 py-5">
                @php
                    $total = max($taskMix->sum(), 1);
                    $mix = [
                        'todo' => ['To Do', 'bg-slate-300', 'text-slate-600'],
                        'in_progress' => ['In Progress', 'bg-brand-400', 'text-brand-700'],
                        'review' => ['In Review', 'bg-amber-400', 'text-amber-700'],
                        'done' => ['Done', 'bg-emerald-500', 'text-emerald-700'],
                    ];
                @endphp

                @foreach ($mix as $key => [$label, $bar, $text])
                    @php $count = (int) ($taskMix[$key] ?? 0); @endphp
                    <div>
                        <div class="mb-1.5 flex items-center justify-between text-[12.5px]">
                            <span class="font-medium text-slate-600">{{ $label }}</span>
                            <span class="font-bold {{ $text }}">{{ $count }}</span>
                        </div>
                        <div class="progress">
                            <div class="h-full rounded-full {{ $bar }} transition-[width] duration-700" style="width: {{ round($count / $total * 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endunless
        </div>
    </div>

    @unless ($hideCharts)
    {{-- ===================== DIAGRAMS ROW ===================== --}}
    <div class="mt-5 grid gap-5 lg:grid-cols-3">

        {{-- Donut: project status split --}}
        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="flex items-center gap-2 text-[14px] font-bold text-heading">
                    <x-icon name="pie-chart" class="size-4 text-brand-600" /> Projects by status
                </h2>
                <p class="mt-0.5 text-[12px] text-muted">Distribution across the lifecycle</p>
            </div>
            <div class="relative px-5 py-5">
                <div class="relative h-[220px]">
                    <canvas id="statusDonut"></canvas>
                    <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center" style="padding-bottom: 44px;">
                        <span class="text-[26px] font-extrabold leading-none tracking-tight text-heading">{{ array_sum($statusDonut['data']) }}</span>
                        <span class="mt-1 text-[10.5px] font-semibold uppercase tracking-wide text-muted">Projects</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Horizontal bars: budget vs spent --}}
        <div class="card lg:col-span-2">
            <div class="flex items-start justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="flex items-center gap-2 text-[14px] font-bold text-heading">
                        <x-icon name="bar-chart-horizontal-big" class="size-4 text-brand-600" /> Budget vs. spend
                    </h2>
                    <p class="mt-0.5 text-[12px] text-muted">Top projects by allocated budget</p>
                </div>
                <div class="flex items-center gap-3 text-[11px] font-semibold">
                    <span class="inline-flex items-center gap-1.5 text-slate-600"><span class="size-2 rounded-full bg-ink-900"></span> Budget</span>
                    <span class="inline-flex items-center gap-1.5 text-slate-600"><span class="size-2 rounded-full bg-brand-400"></span> Spent</span>
                </div>
            </div>
            <div class="px-3 py-4">
                <div class="h-[220px]"><canvas id="budgetChart"></canvas></div>
            </div>
        </div>
    </div>
    @endunless

    <div class="mt-5 grid gap-5 xl:grid-cols-3">

        {{-- Recent projects --}}
        <div class="card xl:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="text-[14px] font-bold text-heading">Recent projects</h2>
                <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-1 text-[12px] font-semibold text-brand-600 hover:text-brand-700">
                    View all <x-icon name="arrow-right" class="size-3.5" />
                </a>
            </div>

            @if ($recentProjects->isEmpty())
                <x-empty-state icon="folder-open" title="No projects yet" description="Create your first project to get started." />
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($recentProjects as $project)
                        <a href="{{ route('projects.show', $project) }}" class="flex items-center gap-4 px-5 py-3.5 transition hover:bg-slate-50/80">
                            <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 ring-1 ring-brand-100">
                                <x-icon name="folder-kanban" class="size-4" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-[13px] font-semibold text-heading">{{ $project->name }}</p>
                                <p class="truncate text-[11.5px] text-muted">{{ $project->customer?->company_name ?? 'Internal' }} · {{ $project->manager?->name ?? 'Unassigned' }}</p>
                            </div>
                            <div class="hidden w-28 shrink-0 sm:block">
                                <div class="progress"><div class="progress-bar" style="width: {{ $project->progress }}%"></div></div>
                            </div>
                            <span class="w-10 shrink-0 text-right text-[12px] font-bold text-slate-600">{{ $project->progress }}%</span>
                            <x-status-badge :label="$project->status_label" :classes="$project->status_classes" />
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Upcoming deadlines --}}
        <div class="card">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="text-[14px] font-bold text-heading">Upcoming deadlines</h2>
                <a href="{{ route('tasks.index') }}" class="text-[12px] font-semibold text-brand-600 hover:text-brand-700">All</a>
            </div>

            @if ($upcoming->isEmpty())
                <x-empty-state icon="calendar-check" title="Nothing due" description="No upcoming task deadlines." />
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($upcoming as $task)
                        @php $overdue = $task->due_date && $task->due_date->isPast(); @endphp
                        <div class="flex items-start gap-3 px-5 py-3">
                            <span class="mt-0.5 inline-flex size-7 shrink-0 items-center justify-center rounded-lg {{ $overdue ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-500' }}">
                                <x-icon :name="$overdue ? 'alarm-clock' : 'calendar'" class="size-3.5" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-[12.5px] font-semibold text-heading">{{ $task->title }}</p>
                                <p class="truncate text-[11px] text-muted">{{ $task->project?->name }}</p>
                            </div>
                            <span class="shrink-0 text-[11px] font-semibold {{ $overdue ? 'text-rose-600' : 'text-slate-500' }}">
                                {{ $task->due_date->format('M j') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Quick modals --}}
    @include('partials.task-modal')
    @include('partials.project-modal')

<script>
window.addEventListener('load', function () {
    /* -------- Velocity area chart -------- */
    const velocity = document.getElementById('velocityChart');
    if (velocity) {
        makeAreaChart(
            velocity,
            @json($timeline['labels']),
            [
                { label: 'Completed', data: @json($timeline['completed']), color: '#84CC16' },
                { label: 'Created', data: @json($timeline['created']), color: '#CBD5E1' },
            ]
        );
    }

    /* -------- Project status donut -------- */
    const donut = document.getElementById('statusDonut');
    if (donut) {
        makeDonutChart(
            donut,
            @json($statusDonut['labels']),
            @json($statusDonut['data']),
            @json($statusDonut['colors'])
        );
    }

    /* -------- Budget vs. spend horizontal bars -------- */
    const budget = document.getElementById('budgetChart');
    if (budget) {
        const labels = @json($budgetChart['labels']);
        const budgetData = @json($budgetChart['budget']);
        const spentData = @json($budgetChart['spent']);

        new Chart(budget.getContext('2d'), {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    { label: 'Budget', data: budgetData, backgroundColor: '#111827', borderRadius: 5, borderSkipped: false, maxBarThickness: 15 },
                    { label: 'Spent', data: spentData, backgroundColor: '#A3E635', borderRadius: 5, borderSkipped: false, maxBarThickness: 15 },
                ],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        padding: 12,
                        cornerRadius: 10,
                        displayColors: true,
                        boxWidth: 8,
                        boxHeight: 8,
                        usePointStyle: true,
                        bodyFont: { family: 'Inter', size: 12 },
                        callbacks: {
                            label: (item) => ' ' + item.dataset.label + ': $' + Number(item.raw).toLocaleString(),
                        },
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: 'rgba(148, 163, 184, 0.16)' },
                        border: { display: false },
                        ticks: { color: '#94A3B8', font: { family: 'Inter', size: 10.5 }, callback: (v) => '$' + (v >= 1000 ? (v / 1000) + 'k' : v) },
                    },
                    y: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: {
                            color: '#475569',
                            font: { family: 'Inter', size: 11, weight: '600' },
                            callback: function (value) {
                                const label = this.getLabelForValue(value);
                                return label.length > 16 ? label.slice(0, 15) + '…' : label;
                            },
                        },
                    },
                },
            },
        });
    }

});
</script>

</x-layouts.app>