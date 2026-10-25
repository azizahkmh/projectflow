<x-layouts.app title="Projects">

    <x-page-header
        eyebrow="Delivery"
        title="Projects"
        subtitle="Every engagement, its owner, budget and current progress."
    >
        <x-slot:actions>
            @can('create', \App\Models\Project::class)
                <button type="button" @click="$store.modals.show('new-project')" class="btn-primary">
                    <x-icon name="folder-plus" class="size-4" /> New project
                </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    {{-- Filter pills + search --}}
    <div class="mt-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-wrap items-center gap-1.5">
            @php
                $filters = [
                    'all' => ['All', $counts['all']],
                    'in_progress' => ['In Progress', $counts['in_progress']],
                    'planning' => ['Planning', $counts['planning']],
                    'completed' => ['Completed', $counts['completed']],
                ];
                $active = request('status', 'all') ?: 'all';
            @endphp
            @foreach ($filters as $key => [$label, $count])
                <a href="{{ $key === 'all' ? route('projects.index') : route('projects.index', ['status' => $key]) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-[12.5px] font-semibold transition
                          {{ $active === $key ? 'bg-ink-900 text-white shadow-sm' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' }}">
                    {{ $label }}
                    <span class="rounded px-1 text-[10.5px] {{ $active === $key ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-500' }}">{{ $count }}</span>
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('projects.index') }}" class="relative">
            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search projects or clients…"
                   class="input !w-full !py-2 !pl-9 !text-[13px] lg:!w-[280px]">
        </form>
    </div>

    {{-- Grid --}}
    @if ($projects->isEmpty())
        <div class="card mt-4">
            <x-empty-state icon="folder-open" title="No projects found" description="Try a different filter, or create your first project.">
                <button type="button" @click="$store.modals.show('new-project')" class="btn-primary btn-sm">
                    <x-icon name="plus" class="size-3.5" /> New project
                </button>
            </x-empty-state>
        </div>
    @else
        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($projects as $project)
                <div class="card card-hover group flex flex-col p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 ring-1 ring-brand-100">
                                <x-icon name="folder-kanban" class="size-[18px]" />
                            </span>
                            <div class="min-w-0">
                                <a href="{{ route('projects.show', $project) }}" class="block truncate text-[14px] font-bold text-heading hover:text-brand-600">
                                    {{ $project->name }}
                                </a>
                                <p class="truncate text-[11.5px] text-muted">{{ $project->customer?->company_name ?? 'Internal' }}</p>
                            </div>
                        </div>

                        <x-dropdown align="right" width="48">
                            <x-slot:trigger>
                                <button type="button" class="rounded-lg p-1.5 text-slate-400 opacity-0 transition hover:bg-slate-100 hover:text-heading group-hover:opacity-100">
                                    <x-icon name="more-horizontal" class="size-4" />
                                </button>
                            </x-slot:trigger>
                            <a href="{{ route('projects.show', $project) }}" class="flex items-center gap-2.5 px-3.5 py-2.5 text-[13px] text-slate-600 hover:bg-slate-50">
                                <x-icon name="eye" class="size-4 text-slate-400" /> View details
                            </a>
                            @can('update', $project)
                                <button type="button" @click="$dispatch('project-edit', {{ $project->toJson() }})"
                                        class="flex w-full items-center gap-2.5 px-3.5 py-2.5 text-[13px] text-slate-600 hover:bg-slate-50">
                                    <x-icon name="pencil" class="size-4 text-slate-400" /> Edit project
                                </button>
                            @endcan
                            @can('delete', $project)
                                <form method="POST" action="{{ route('projects.destroy', $project) }}"
                                      onsubmit="return confirm('Delete this project and all its tasks?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="flex w-full items-center gap-2.5 px-3.5 py-2.5 text-[13px] font-medium text-rose-600 hover:bg-rose-50">
                                        <x-icon name="trash-2" class="size-4" /> Delete
                                    </button>
                                </form>
                            @endcan
                        </x-dropdown>
                    </div>

                    <p class="mt-3 line-clamp-2 min-h-[32px] text-[12.5px] leading-relaxed text-muted">
                        {{ $project->description ?: 'No description provided.' }}
                    </p>

                    <div class="mt-4">
                        <div class="mb-1.5 flex items-center justify-between text-[11.5px]">
                            <span class="font-semibold text-slate-500">{{ $project->done_tasks_count }}/{{ $project->tasks_count }} tasks</span>
                            <span class="font-bold text-heading">{{ $project->progress }}%</span>
                        </div>
                        <div class="progress"><div class="progress-bar" style="width: {{ $project->progress }}%"></div></div>
                    </div>

                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3.5">
                        <div class="flex items-center gap-2">
                            @if ($project->manager)
                                <x-avatar :name="$project->manager->name" :color="$project->manager->avatar_color" size="sm" />
                                <span class="text-[11.5px] font-medium text-slate-600">{{ $project->manager->name }}</span>
                            @else
                                <span class="text-[11.5px] text-muted">Unassigned</span>
                            @endif
                        </div>
                        <x-status-badge :label="$project->status_label" :classes="$project->status_classes" />
                    </div>

                    <div class="mt-3 flex items-center justify-between text-[11px] text-muted">
                        <span class="inline-flex items-center gap-1">
                            <x-icon name="wallet" class="size-3.5" /> ${{ number_format($project->budget, 0) }}
                        </span>
                        @if ($project->deadline)
                            @php $late = $project->deadline->isPast() && $project->status !== 'completed'; @endphp
                            <span class="inline-flex items-center gap-1 {{ $late ? 'font-semibold text-rose-600' : '' }}">
                                <x-icon name="calendar" class="size-3.5" /> {{ $project->deadline->format('M j, Y') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1">
                                <x-icon name="calendar" class="size-3.5" /> No deadline
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">{{ $projects->links() }}</div>
    @endif

    @include('partials.project-modal')

</x-layouts.app>
