<x-layouts.app title="Activity">

    <x-page-header eyebrow="Audit" title="Activity log" subtitle="Every action taken across the workspace, newest first." />

    <div class="mt-5 flex flex-wrap items-center gap-2">
        <span class="inline-flex items-center gap-1.5 rounded-lg bg-brand-50 px-3 py-1.5 text-[12.5px] font-semibold text-brand-700 ring-1 ring-brand-200">
            <x-icon name="activity" class="size-3.5" /> {{ $todayCount }} today
        </span>

        <form method="GET" class="flex flex-wrap items-center gap-2">
            <select name="action" class="input !py-1.5 !text-[12.5px] !w-auto" onchange="this.form.submit()">
                <option value="">All actions</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>{{ ucfirst(str_replace('_', ' ', $action)) }}</option>
                @endforeach
            </select>
            <select name="user" class="input !py-1.5 !text-[12.5px] !w-auto" onchange="this.form.submit()">
                <option value="">Anyone</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(request('user') == $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
            @if (request()->hasAny(['action', 'user']))
                <a href="{{ route('activity.index') }}" class="btn-ghost !py-1.5 !text-[12.5px]">Clear</a>
            @endif
        </form>
    </div>

    <div class="card mt-4 overflow-hidden">
        @if ($activities->isEmpty())
            <x-empty-state icon="history" title="No activity yet" description="Actions across the workspace will appear here." />
        @else
            <ol class="divide-y divide-slate-100">
                @foreach ($activities as $activity)
                    <li class="flex items-start gap-3.5 px-5 py-3.5">
                        <span class="mt-0.5 inline-flex size-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 ring-1 ring-slate-200">
                            <x-icon :name="$activity->icon ?? 'activity'" class="size-4" />
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="text-[12.5px] text-slate-700">
                                <span class="font-semibold text-heading">{{ $activity->user?->name ?? 'System' }}</span>
                                {{ $activity->description }}
                            </p>
                            <div class="mt-0.5 flex flex-wrap items-center gap-2 text-[11px] text-muted">
                                <span class="inline-flex items-center gap-1">
                                    <x-icon name="clock" class="size-3" /> {{ $activity->created_at->diffForHumans() }}
                                </span>
                                @if ($activity->ip_address)
                                    <span class="inline-flex items-center gap-1">
                                        <x-icon name="globe" class="size-3" /> {{ $activity->ip_address }}
                                    </span>
                                @endif
                                <span class="rounded bg-slate-100 px-1.5 py-0.5 font-semibold text-slate-500">{{ str_replace('_', ' ', $activity->action) }}</span>
                            </div>
                        </div>

                        <time class="shrink-0 text-[11px] font-medium text-slate-400" datetime="{{ $activity->created_at->toIso8601String() }}">
                            {{ $activity->created_at->format('M j, g:i A') }}
                        </time>
                    </li>
                @endforeach
            </ol>
            <div class="border-t border-slate-100 px-5 py-3">{{ $activities->links() }}</div>
        @endif
    </div>

</x-layouts.app>
