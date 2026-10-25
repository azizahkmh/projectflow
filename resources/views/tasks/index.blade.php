<x-layouts.app title="Tasks">

    <x-page-header
        eyebrow="Execution"
        title="Tasks"
        subtitle="Everything in flight, grouped by status. Move work forward as it progresses."
    >
        <x-slot:actions>
            @can('create', \App\Models\Task::class)
                <button type="button" @click="$store.modals.show('new-task')" class="btn-primary">
                    <x-icon name="plus" class="size-4" /> New task
                </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    {{-- Filters --}}
    <form method="GET" action="{{ route('tasks.index') }}" class="mt-5 flex flex-wrap items-center gap-2">
        <div class="relative">
            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search tasks…" class="input !py-2 !pl-9 !text-[13px] !w-[210px]">
        </div>
        <select name="project" class="input !py-2 !text-[13px] !w-auto" onchange="this.form.submit()">
            <option value="">All projects</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}" @selected(request('project') == $project->id)>{{ $project->name }}</option>
            @endforeach
        </select>
        <select name="assignee" class="input !py-2 !text-[13px] !w-auto" onchange="this.form.submit()">
            <option value="">Anyone</option>
            @foreach ($assignees as $assignee)
                <option value="{{ $assignee->id }}" @selected(request('assignee') == $assignee->id)>{{ $assignee->name }}</option>
            @endforeach
        </select>
        @if (request()->hasAny(['q', 'project', 'assignee']))
            <a href="{{ route('tasks.index') }}" class="btn-ghost !py-2 !text-[13px]">Clear</a>
        @endif
    </form>

    {{-- Board --}}
    <div class="mt-5 grid gap-4 lg:grid-cols-4">
        @foreach ($columns as $status => $label)
            @php
                $tasks = $board[$status] ?? collect();
                $accent = match ($status) {
                    'in_progress' => 'bg-brand-400',
                    'review' => 'bg-amber-400',
                    'done' => 'bg-emerald-500',
                    default => 'bg-slate-300',
                };
            @endphp

            <div class="flex flex-col rounded-2xl border border-slate-200/80 bg-slate-50/70">
                <div class="flex items-center justify-between border-b border-slate-200/70 px-4 py-3">
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full {{ $accent }}"></span>
                        <h2 class="text-[12.5px] font-bold uppercase tracking-wide text-slate-600">{{ $label }}</h2>
                    </div>
                    <span class="rounded-md bg-white px-1.5 py-0.5 text-[10.5px] font-bold text-slate-500 ring-1 ring-slate-200">{{ $tasks->count() }}</span>
                </div>

                <div class="flex-1 space-y-2.5 p-2.5">
                    @forelse ($tasks as $task)
                        <div class="group rounded-xl border border-slate-200/80 bg-white p-3 shadow-sm transition hover:border-slate-300 hover:shadow-soft">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-[12.5px] font-semibold leading-snug {{ $status === 'done' ? 'text-slate-400 line-through' : 'text-heading' }}">
                                    {{ $task->title }}
                                </p>

                                @can('update', $task)
                                    <x-dropdown align="right" width="44">
                                        <x-slot:trigger>
                                            <button type="button" class="rounded p-0.5 text-slate-400 opacity-0 transition hover:text-heading group-hover:opacity-100">
                                                <x-icon name="more-horizontal" class="size-3.5" />
                                            </button>
                                        </x-slot:trigger>

                                        <p class="px-3 pb-1 pt-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Move to</p>
                                        @foreach ($columns as $targetStatus => $targetLabel)
                                            @continue($targetStatus === $status)
                                            <form method="POST" action="{{ route('tasks.move', $task) }}">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="{{ $targetStatus }}">
                                                <button type="submit" class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-[12px] text-slate-600 hover:bg-slate-50 hover:text-heading">
                                                    <span class="size-1.5 rounded-full {{ $targetStatus === 'done' ? 'bg-emerald-500' : ($targetStatus === 'in_progress' ? 'bg-brand-400' : ($targetStatus === 'review' ? 'bg-amber-400' : 'bg-slate-300')) }}"></span>
                                                    {{ $targetLabel }}
                                                </button>
                                            </form>
                                        @endforeach

                                        <button type="button" @click="$dispatch('task-edit', {{ $task->toJson() }})"
                                                class="flex w-full items-center gap-2 border-t border-slate-100 px-3 py-2 text-left text-[12px] text-slate-600 hover:bg-slate-50 hover:text-heading">
                                            <x-icon name="pencil" class="size-3.5" /> Edit task
                                        </button>

                                        @can('delete', $task)
                                            <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="border-t border-slate-100"
                                                  onsubmit="return confirm('Delete this task?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-[12px] font-medium text-rose-600 hover:bg-rose-50">
                                                    <x-icon name="trash-2" class="size-3.5" /> Delete
                                                </button>
                                            </form>
                                        @endcan
                                    </x-dropdown>
                                @endcan
                            </div>

                            @if ($task->project)
                                <p class="mt-1.5 inline-flex items-center gap-1 text-[10.5px] font-medium text-slate-400">
                                    <x-icon name="folder" class="size-3" /> {{ $task->project->name }}
                                </p>
                            @endif

                            <div class="mt-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-1.5">
                                    <x-status-badge :label="ucfirst($task->priority)" :dot="false" :classes="$task->priority_classes" class="!px-1.5 !py-0.5 !text-[9.5px]" />
                                    @if ($task->due_date)
                                        @php $late = $task->due_date->isPast() && $status !== 'done'; @endphp
                                        <span class="inline-flex items-center gap-0.5 text-[10px] font-medium {{ $late ? 'text-rose-600' : 'text-slate-400' }}">
                                            <x-icon name="calendar" class="size-3" /> {{ $task->due_date->format('M j') }}
                                        </span>
                                    @endif
                                </div>
                                @if ($task->assignee)
                                    <x-avatar :name="$task->assignee->name" :color="$task->assignee->avatar_color" size="sm" />
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 py-8 text-center">
                            <x-icon name="inbox" class="size-5 text-slate-300" />
                            <p class="mt-1.5 text-[11px] text-slate-400">No tasks</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    @include('partials.task-modal')

</x-layouts.app>
