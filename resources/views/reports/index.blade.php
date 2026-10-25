<x-layouts.app title="Reports">

    <x-page-header eyebrow="Insights" title="Reports" subtitle="Performance across projects, delivery and finances.">
        <x-slot:actions>
            @can('reports.export')
                <a href="{{ route('reports.export') }}" target="_blank" rel="noopener" class="btn-ghost">
                    <x-icon name="download" class="size-4" /> Export PDF
                </a>
                <button type="button" @click="$store.modals.show('new-report')" class="btn-primary">
                    <x-icon name="file-plus" class="size-4" /> Generate report
                </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    {{-- KPIs --}}
    <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['Completion rate', $completionRate.'%', 'check-circle-2', 'text-brand-600'],
            ['On-time delivery', $onTimeRate.'%', 'timer', 'text-emerald-600'],
            ['Projects at risk', $health['at_risk'], 'alert-triangle', 'text-rose-600'],
            ['Projects completed', $health['completed'], 'folder-check', 'text-heading'],
        ] as [$label, $value, $icon, $tone])
            <div class="card p-5">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">{{ $label }}</p>
                    <x-icon :name="$icon" class="size-4 text-slate-300" />
                </div>
                <p class="mt-1.5 text-[22px] font-extrabold tracking-tight {{ $tone }}">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-3">

        {{-- Throughput --}}
        <div class="card xl:col-span-2">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-[14px] font-bold text-heading">Task throughput</h2>
                <p class="mt-0.5 text-[12px] text-muted">Created vs. completed over the last 9 months</p>
            </div>
            <div class="px-3 py-4"><div class="h-[280px]"><canvas id="throughputChart"></canvas></div></div>
        </div>

        {{-- Health --}}
        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-[14px] font-bold text-heading">Portfolio health</h2>
            </div>
            <div class="space-y-4 px-5 py-5">
                @php $t = max($health['total'], 1); @endphp
                @foreach ([
                    ['On track', $health['on_track'], 'bg-emerald-500', 'text-emerald-700'],
                    ['At risk', $health['at_risk'], 'bg-rose-500', 'text-rose-700'],
                    ['Completed', $health['completed'], 'bg-brand-500', 'text-brand-700'],
                ] as [$label, $count, $bar, $text])
                    <div>
                        <div class="mb-1 flex items-center justify-between text-[12.5px]">
                            <span class="font-medium text-slate-600">{{ $label }}</span>
                            <span class="font-bold {{ $text }}">{{ $count }}</span>
                        </div>
                        <div class="progress"><div class="h-full rounded-full {{ $bar }}" style="width: {{ round($count / $t * 100) }}%"></div></div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ===================== DIAGRAM ROW 1 ===================== --}}
    <div class="mt-5 grid gap-5 lg:grid-cols-3">

        {{-- Donut: status distribution --}}
        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="flex items-center gap-2 text-[14px] font-bold text-heading">
                    <x-icon name="pie-chart" class="size-4 text-brand-600" /> Status distribution
                </h2>
                <p class="mt-0.5 text-[12px] text-muted">Projects by lifecycle stage</p>
            </div>
            <div class="relative px-5 py-5">
                <div class="relative h-[220px]">
                    <canvas id="reportStatusDonut"></canvas>
                    <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center" style="padding-bottom: 44px;">
                        <span class="text-[26px] font-extrabold leading-none tracking-tight text-heading">{{ array_sum($statusDonut['data']) }}</span>
                        <span class="mt-1 text-[10.5px] font-semibold uppercase tracking-wide text-muted">Total</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Horizontal bars: budget vs spent --}}
        <div class="card lg:col-span-2">
            <div class="flex items-start justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="flex items-center gap-2 text-[14px] font-bold text-heading">
                        <x-icon name="bar-chart-horizontal-big" class="size-4 text-brand-600" /> Budget utilisation
                    </h2>
                    <p class="mt-0.5 text-[12px] text-muted">Allocated budget against recorded spend</p>
                </div>
                <div class="flex items-center gap-3 text-[11px] font-semibold">
                    <span class="inline-flex items-center gap-1.5 text-slate-600"><span class="size-2 rounded-full bg-ink-900"></span> Budget</span>
                    <span class="inline-flex items-center gap-1.5 text-slate-600"><span class="size-2 rounded-full bg-brand-400"></span> Spent</span>
                </div>
            </div>
            <div class="px-3 py-4"><div class="h-[220px]"><canvas id="reportBudgetChart"></canvas></div></div>
        </div>
    </div>

    {{-- ===================== DIAGRAM ROW 2 ===================== --}}
    <div class="mt-5 grid gap-5 lg:grid-cols-3">

        {{-- Radar: team profile --}}
        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="flex items-center gap-2 text-[14px] font-bold text-heading">
                    <x-icon name="radar" class="size-4 text-brand-600" /> Delivery capability
                </h2>
                <p class="mt-0.5 text-[12px] text-muted">Staff vs. managers, 0–100</p>
            </div>
            <div class="px-3 py-4"><div class="h-[260px]"><canvas id="reportRadar"></canvas></div></div>
        </div>

        {{-- Cashflow bars --}}
        <div class="card lg:col-span-2">
            <div class="flex items-start justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="flex items-center gap-2 text-[14px] font-bold text-heading">
                        <x-icon name="chart-column" class="size-4 text-brand-600" /> Revenue vs. expenses
                    </h2>
                    <p class="mt-0.5 text-[12px] text-muted">Monthly cashflow over the last 9 months</p>
                </div>
                <div class="flex items-center gap-3 text-[11px] font-semibold">
                    <span class="inline-flex items-center gap-1.5 text-slate-600"><span class="size-2 rounded-full bg-emerald-500"></span> Revenue</span>
                    <span class="inline-flex items-center gap-1.5 text-slate-600"><span class="size-2 rounded-full bg-rose-400"></span> Expenses</span>
                </div>
            </div>
            <div class="px-3 py-4"><div class="h-[260px]"><canvas id="reportCashflowChart"></canvas></div></div>
        </div>
    </div>

    {{-- ===================== SPEND MIX ===================== --}}
    <div class="mt-5 grid gap-5 lg:grid-cols-3">
        <div class="card lg:col-span-3">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="flex items-center gap-2 text-[14px] font-bold text-heading">
                    <x-icon name="scale" class="size-4 text-brand-600" /> Expense mix by category
                </h2>
                <p class="mt-0.5 text-[12px] text-muted">Where the approved budget is being spent</p>
            </div>
            <div class="px-5 py-5">
                @php $mixTotal = max($expenseMix->sum('total'), 1); @endphp
                <div class="flex h-3 w-full overflow-hidden rounded-full bg-slate-100">
                    @foreach ($expenseMix as $i => $row)
                        @php
                            $palette = ['#84CC16', '#0EA5E9', '#F59E0B', '#F43F5E', '#8B5CF6', '#14B8A6', '#64748B'];
                            $color = $palette[$i % count($palette)];
                        @endphp
                        <div style="width: {{ round($row->total / $mixTotal * 100, 2) }}%; background: {{ $color }};" title="{{ ucfirst($row->category) }}"></div>
                    @endforeach
                </div>
                <div class="mt-4 grid gap-2.5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($expenseMix as $i => $row)
                        @php $palette = ['#84CC16', '#0EA5E9', '#F59E0B', '#F43F5E', '#8B5CF6', '#14B8A6', '#64748B']; $color = $palette[$i % count($palette)]; @endphp
                        <div class="flex items-center justify-between rounded-lg border border-slate-100 px-3 py-2">
                            <span class="inline-flex items-center gap-1.5 text-[12px] font-medium capitalize text-slate-600">
                                <span class="size-2 rounded-full" style="background: {{ $color }}"></span> {{ $row->category }}
                            </span>
                            <span class="text-[12px] font-bold text-heading">${{ number_format($row->total, 0) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-2">
        {{-- Per project --}}
        <div class="card overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-[14px] font-bold text-heading">Top projects by budget</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($perProject as $project)
                    <div class="flex items-center gap-3 px-5 py-3">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-[12.5px] font-semibold text-heading">{{ $project->name }}</p>
                            <p class="text-[11px] text-muted">{{ $project->done_tasks_count }}/{{ $project->tasks_count }} tasks done</p>
                        </div>
                        <span class="text-[12px] font-bold text-slate-700">${{ number_format($project->budget, 0) }}</span>
                    </div>
                @empty
                    <p class="px-5 py-6 text-center text-[12.5px] text-muted">No projects yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Generated reports --}}
        <div class="card overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-[14px] font-bold text-heading">Saved reports</h2>
            </div>
            @if ($reports->isEmpty())
                <x-empty-state icon="file-bar-chart" title="No reports" description="Generate a report to keep a snapshot." />
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($reports as $report)
                        <div class="flex items-center gap-3 px-5 py-3">
                            <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 ring-1 ring-brand-100">
                                <x-icon name="file-text" class="size-4" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-[12.5px] font-semibold text-heading">{{ $report->name }}</p>
                                <p class="text-[11px] capitalize text-muted">{{ $report->type }} · {{ $report->generated_at?->format('M j, Y') }} · {{ $report->generator?->name ?? 'System' }}</p>
                            </div>
                            <x-status-badge :label="ucfirst($report->status)" classes="bg-emerald-50 text-emerald-700 ring-emerald-200" />
                        </div>
                    @endforeach
                </div>
                <div class="border-t border-slate-100 px-5 py-3">{{ $reports->links() }}</div>
            @endif
        </div>
    </div>

    {{-- Generate modal --}}
    @can('reports.export')
        <x-modal name="new-report" title="Generate report" description="Pick a type and period to snapshot." max-width="md">
            <form method="POST" action="{{ route('reports.store') }}" class="space-y-4 px-6 py-5">
                @csrf
                <div>
                    <label for="nr-name" class="label">Report name</label>
                    <input id="nr-name" name="name" type="text" required value="{{ old('name') }}" placeholder="e.g. Q3 Performance" class="input">
                    @error('name')<p class="error"><x-icon name="alert-circle" class="size-3.5" />{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="nr-type" class="label">Type</label>
                    <select id="nr-type" name="type" class="input">
                        @foreach (['project' => 'Project', 'finance' => 'Finance', 'tasks' => 'Tasks', 'performance' => 'Performance'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="nr-start" class="label">Period start</label>
                        <input id="nr-start" name="period_start" type="date" required value="{{ old('period_start', now()->startOfMonth()->toDateString()) }}" class="input">
                        @error('period_start')<p class="error"><x-icon name="alert-circle" class="size-3.5" />{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="nr-end" class="label">Period end</label>
                        <input id="nr-end" name="period_end" type="date" required value="{{ old('period_end', now()->toDateString()) }}" class="input">
                        @error('period_end')<p class="error"><x-icon name="alert-circle" class="size-3.5" />{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" @click="$store.modals.close('new-report')" class="btn-ghost">Cancel</button>
                    <button type="submit" class="btn-primary"><x-icon name="sparkles" class="size-4" /> Generate</button>
                </div>
            </form>
        </x-modal>
    @endcan

<script>
window.addEventListener('load', function () {
    /* -------- Throughput area -------- */
    const throughput = document.getElementById('throughputChart');
    if (throughput) {
        makeAreaChart(throughput, @json($timeline['labels']), [
            { label: 'Completed', data: @json($timeline['completed']), color: '#84CC16' },
            { label: 'Created', data: @json($timeline['created']), color: '#CBD5E1' },
        ]);
    }

    /* -------- Status donut -------- */
    const donut = document.getElementById('reportStatusDonut');
    if (donut) {
        makeDonutChart(donut, @json($statusDonut['labels']), @json($statusDonut['data']), @json($statusDonut['colors']));
    }

    /* -------- Budget vs spend -------- */
    const budget = document.getElementById('reportBudgetChart');
    if (budget) {
        new Chart(budget.getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($budgetChart['labels']),
                datasets: [
                    { label: 'Budget', data: @json($budgetChart['budget']), backgroundColor: '#111827', borderRadius: 5, borderSkipped: false, maxBarThickness: 15 },
                    { label: 'Spent', data: @json($budgetChart['spent']), backgroundColor: '#A3E635', borderRadius: 5, borderSkipped: false, maxBarThickness: 15 },
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
                        backgroundColor: '#0F172A', padding: 12, cornerRadius: 10,
                        boxWidth: 8, boxHeight: 8, usePointStyle: true,
                        bodyFont: { family: 'Inter', size: 12 },
                        callbacks: { label: (i) => ' ' + i.dataset.label + ': $' + Number(i.raw).toLocaleString() },
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

    /* -------- Radar -------- */
    const radar = document.getElementById('reportRadar');
    if (radar) {
        makeRadarChart(radar, @json($teamRadar['labels']), @json($teamRadar['series']));
    }

    /* -------- Cashflow grouped bars -------- */
    const cashflow = document.getElementById('reportCashflowChart');
    if (cashflow) {
        new Chart(cashflow.getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($cashflow['labels']),
                datasets: [
                    { label: 'Revenue', data: @json($cashflow['revenue']), backgroundColor: '#10B981', borderRadius: 5, borderSkipped: false, maxBarThickness: 16 },
                    { label: 'Expenses', data: @json($cashflow['expenses']), backgroundColor: '#FB7185', borderRadius: 5, borderSkipped: false, maxBarThickness: 16 },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A', padding: 12, cornerRadius: 10,
                        boxWidth: 8, boxHeight: 8, usePointStyle: true,
                        bodyFont: { family: 'Inter', size: 12 },
                        callbacks: { label: (i) => ' ' + i.dataset.label + ': $' + Number(i.raw).toLocaleString() },
                    },
                },
                scales: {
                    x: { grid: { display: false }, border: { display: false }, ticks: { color: '#94A3B8', font: { family: 'Inter', size: 11 } } },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(148, 163, 184, 0.16)' },
                        border: { display: false },
                        ticks: { color: '#94A3B8', font: { family: 'Inter', size: 11 }, callback: (v) => '$' + (v >= 1000 ? (v / 1000) + 'k' : v) },
                    },
                },
            },
        });
    }
});
</script>

</x-layouts.app>
