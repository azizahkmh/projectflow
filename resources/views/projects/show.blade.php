<x-layouts.app :title="$project->name">

    <div class="mb-5 flex items-center gap-1.5 text-[12px] font-medium text-muted">
        <a href="{{ route('projects.index') }}" class="hover:text-heading">Projects</a>
        <x-icon name="chevron-right" class="size-3.5 text-slate-300" />
        <span class="text-heading">{{ $project->name }}</span>
    </div>

    {{-- Header --}}
    <div class="card overflow-hidden">
        <div class="border-b border-slate-100 bg-gradient-to-r from-brand-50/60 to-transparent px-6 py-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-start gap-3.5">
                    <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-xl bg-ink-900 text-brand-400 shadow-sm">
                        <x-icon name="folder-kanban" class="size-5" />
                    </span>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-[20px] font-extrabold tracking-tight text-heading">{{ $project->name }}</h1>
                            <x-status-badge :label="$project->status_label" :classes="$project->status_classes" />
                            <x-status-badge :label="ucfirst($project->priority)" dot="false" classes="bg-slate-100 text-slate-600 ring-slate-200" />
                        </div>
                        <p class="mt-1 text-[13px] text-muted">
                            {{ $project->customer?->company_name ?? 'Internal project' }}
                            @if ($project->start_date)
                                · {{ $project->start_date->format('M j, Y') }} – {{ $project->deadline?->format('M j, Y') ?? 'ongoing' }}
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="$store.modals.show('new-task')" class="btn-ghost">
                        <x-icon name="plus" class="size-4" /> Add task
                    </button>
                </div>
            </div>
        </div>

        {{-- Key figures --}}
        <div class="grid divide-y divide-slate-100 sm:grid-cols-2 sm:divide-y-0 lg:grid-cols-4 lg:divide-x">
            @foreach ([
                ['Budget', '$'.number_format($project->budget, 0), 'wallet', 'text-heading'],
                ['Spent', '$'.number_format($spent, 0), 'receipt', $spent > $project->budget ? 'text-rose-600' : 'text-heading'],
                ['Invoiced', '$'.number_format($invoiced, 0), 'file-text', 'text-heading'],
                ['Progress', $project->progress.'%', 'activity', 'text-brand-600'],
            ] as [$label, $value, $icon, $tone])
                <div class="px-6 py-4">
                    <p class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-muted">
                        <x-icon :name="$icon" class="size-3.5" /> {{ $label }}
                    </p>
                    <p class="mt-1 text-[19px] font-extrabold tracking-tight {{ $tone }}">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <div class="border-t border-slate-100 px-6 py-4">
            <div class="mb-1.5 flex items-center justify-between text-[12px]">
                <span class="font-semibold text-slate-500">Overall completion</span>
                <span class="font-bold text-heading">{{ $project->progress }}%</span>
            </div>
            <div class="progress !h-2"><div class="progress-bar" style="width: {{ $project->progress }}%"></div></div>
        </div>
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-3">

        {{-- Description + tasks --}}
        <div class="space-y-5 lg:col-span-2">
            <div class="card">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-[14px] font-bold text-heading">About this project</h2>
                </div>
                <p class="px-5 py-4 text-[13px] leading-relaxed text-slate-600">
                    {{ $project->description ?: 'No description has been added to this project yet.' }}
                </p>
            </div>

            <div class="card">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h2 class="text-[14px] font-bold text-heading">Tasks</h2>
                    <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-600">{{ $project->tasks->count() }}</span>
                </div>

                @if ($project->tasks->isEmpty())
                    <x-empty-state icon="list-checks" title="No tasks yet" description="Break this project into trackable work items." />
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($project->tasks->sortBy('status') as $task)
                            <div class="flex items-center gap-3 px-5 py-3">
                                <span class="size-2 shrink-0 rounded-full {{ $task->status === 'done' ? 'bg-emerald-500' : ($task->status === 'review' ? 'bg-amber-400' : ($task->status === 'in_progress' ? 'bg-brand-400' : 'bg-slate-300')) }}"></span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[12.5px] font-semibold {{ $task->status === 'done' ? 'text-slate-400 line-through' : 'text-heading' }}">
                                        {{ $task->title }}
                                    </p>
                                </div>
                                @if ($task->assignee)
                                    <x-avatar :name="$task->assignee->name" :color="$task->assignee->avatar_color" size="sm" />
                                @endif
                                <x-status-badge :label="$task->status_label" :classes="$task->status_classes" class="!text-[10px]" />
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-5">
            <div class="card">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-[14px] font-bold text-heading">Team</h2>
                </div>
                <div class="space-y-3 px-5 py-4">
                    @if ($project->manager)
                        <div class="flex items-center justify-between">
                            <x-avatar :name="$project->manager->name" :color="$project->manager->avatar_color" size="md" subtitle="Project manager" />
                            <span class="rounded bg-ink-900 px-1.5 py-0.5 text-[9px] font-bold uppercase text-brand-400">Lead</span>
                        </div>
                    @endif
                    @foreach ($project->staff as $member)
                        <x-avatar :name="$member->name" :color="$member->avatar_color" size="md" :subtitle="$member->job_title ?? 'Team member'" />
                    @endforeach
                    @if (! $project->manager && $project->staff->isEmpty())
                        <p class="text-[12.5px] text-muted">No team members assigned yet.</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-[14px] font-bold text-heading">Recent expenses</h2>
                </div>
                @if ($project->expenses->isEmpty())
                    <x-empty-state icon="wallet" title="No expenses" />
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($project->expenses->take(5) as $expense)
                            <div class="flex items-center justify-between px-5 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-[12.5px] font-medium text-heading">{{ $expense->title }}</p>
                                    <p class="text-[11px] text-muted">{{ ucfirst($expense->category) }}</p>
                                </div>
                                <span class="text-[12.5px] font-bold text-slate-700">${{ number_format($expense->amount, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('partials.task-modal')

</x-layouts.app>
