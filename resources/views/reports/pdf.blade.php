<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>ProjectFlow — Project Performance Report</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif; color: #0F172A; margin: 0; padding: 40px; background: #fff; font-size: 13px; }
        .header { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #A3E635; padding-bottom: 16px; margin-bottom: 24px; }
        .brand { display: flex; align-items: center; gap: 10px; }
        .logo { width: 34px; height: 34px; border-radius: 9px; background: #111827; color: #A3E635; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 17px; }
        h1 { font-size: 20px; margin: 0; letter-spacing: -0.02em; }
        .muted { color: #64748B; font-size: 11.5px; }
        .kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 26px; }
        .kpi { border: 1px solid #E2E8F0; border-radius: 10px; padding: 12px 14px; }
        .kpi .label { text-transform: uppercase; font-size: 9.5px; letter-spacing: 0.08em; color: #64748B; font-weight: 700; }
        .kpi .value { font-size: 20px; font-weight: 800; margin-top: 4px; letter-spacing: -0.02em; }
        h2 { font-size: 13px; text-transform: uppercase; letter-spacing: 0.08em; color: #475569; margin: 26px 0 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.06em; color: #64748B; border-bottom: 1px solid #E2E8F0; padding: 8px 10px; background: #F8FAFC; }
        td { padding: 8px 10px; border-bottom: 1px solid #F1F5F9; font-size: 12px; }
        .text-right { text-align: right; }
        .bar { height: 6px; background: #F1F5F9; border-radius: 3px; overflow: hidden; min-width: 70px; }
        .bar > span { display: block; height: 100%; background: linear-gradient(90deg, #84CC16, #BEF264); }
        .footer { margin-top: 30px; border-top: 1px solid #E2E8F0; padding-top: 12px; display: flex; justify-content: space-between; }
        .chart { width: 100%; height: 120px; }
        @media print { body { padding: 0; } .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <div class="brand">
            <div class="logo">P</div>
            <div>
                <h1>Project Performance Report</h1>
                <div class="muted">Generated {{ $generatedAt->format('F j, Y \a\t g:i A') }} by {{ $generatedBy->name }}</div>
            </div>
        </div>
        <div style="text-align:right">
            <div class="muted">ProjectFlow</div>
            <div class="muted">{{ $generatedAt->format('Y-m-d') }}</div>
        </div>
    </div>

    <div class="kpis">
        <div class="kpi"><div class="label">Total projects</div><div class="value">{{ $health['total'] }}</div></div>
        <div class="kpi"><div class="label">On track</div><div class="value" style="color:#059669">{{ $health['on_track'] }}</div></div>
        <div class="kpi"><div class="label">At risk</div><div class="value" style="color:#E11D48">{{ $health['at_risk'] }}</div></div>
        <div class="kpi"><div class="label">Completed</div><div class="value" style="color:#65A30D">{{ $health['completed'] }}</div></div>
    </div>

    <h2>Task throughput (last 9 months)</h2>
    @php
        $max = max(max($timeline['completed']), max($timeline['created']), 1);
        $w = 100 / max(count($timeline['labels']), 1);
    @endphp
    <svg class="chart" viewBox="0 0 {{ count($timeline['labels']) * 60 }} 100" preserveAspectRatio="none">
        @foreach ($timeline['labels'] as $i => $label)
            @php
                $ch = ($timeline['completed'][$i] / $max) * 80;
                $dh = ($timeline['created'][$i] / $max) * 80;
                $x = $i * 60;
            @endphp
            <rect x="{{ $x + 12 }}" y="{{ 90 - $ch }}" width="16" height="{{ max($ch, 1) }}" fill="#84CC16" rx="2" />
            <rect x="{{ $x + 32 }}" y="{{ 90 - $dh }}" width="16" height="{{ max($dh, 1) }}" fill="#CBD5E1" rx="2" />
            <text x="{{ $x + 30 }}" y="99" font-size="7" fill="#94A3B8" text-anchor="middle">{{ $label }}</text>
        @endforeach
    </svg>
    <div class="muted" style="margin-top:4px">
        <span style="display:inline-block;width:8px;height:8px;background:#84CC16;border-radius:2px"></span> Completed
        &nbsp;&nbsp;
        <span style="display:inline-block;width:8px;height:8px;background:#CBD5E1;border-radius:2px"></span> Created
    </div>

    <h2>Project breakdown</h2>
    <table>
        <thead>
            <tr>
                <th>Project</th>
                <th>Customer</th>
                <th>Manager</th>
                <th class="text-right">Budget</th>
                <th class="text-right">Tasks</th>
                <th style="width:130px">Progress</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($projects as $project)
                <tr>
                    <td style="font-weight:600">{{ $project->name }}</td>
                    <td>{{ $project->customer?->company_name ?? '—' }}</td>
                    <td>{{ $project->manager?->name ?? '—' }}</td>
                    <td class="text-right">${{ number_format($project->budget, 0) }}</td>
                    <td class="text-right">{{ $project->done_tasks_count }}/{{ $project->tasks_count }}</td>
                    <td>
                        <div class="bar"><span style="width: {{ $project->progress }}%"></span></div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#94A3B8;padding:20px">No projects to report.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <span class="muted">Generated automatically by ProjectFlow</span>
        <span class="muted">Page 1 of 1</span>
    </div>

</body>
</html>
