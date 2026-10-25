<x-layouts.app title="Profile">

    <x-page-header eyebrow="Account" title="My profile" subtitle="Your details and workspace role" />

    <div class="mt-5 grid gap-5 lg:grid-cols-3">

        {{-- Identity card --}}
        <div class="card overflow-hidden">
            <div class="h-24 bg-gradient-to-r from-ink-900 via-ink-800 to-brand-700"></div>
            <div class="-mt-10 px-5 pb-5">
                <x-avatar :name="auth()->user()->name" :color="auth()->user()->avatar_color" size="xl" class="ring-4 ring-white" />
                <h2 class="mt-3 text-[16px] font-extrabold tracking-tight text-heading">{{ auth()->user()->name }}</h2>
                <p class="text-[12.5px] text-muted">{{ auth()->user()->job_title ?? 'Team member' }}</p>

                <span class="mt-2.5 inline-flex items-center gap-1 rounded-full bg-brand-50 px-2.5 py-1 text-[11px] font-bold text-brand-700 ring-1 ring-brand-200">
                    <x-icon name="shield-check" class="size-3" />
                    {{ \App\Models\User::ROLES[auth()->user()->getRoleNames()->first() ?? ''] ?? 'No role' }}
                </span>

                <dl class="mt-4 space-y-2.5 border-t border-slate-100 pt-4">
                    <div class="flex items-center gap-2 text-[12.5px]">
                        <x-icon name="mail" class="size-4 text-slate-400" />
                        <span class="text-slate-600">{{ auth()->user()->email }}</span>
                    </div>
                    @if (auth()->user()->phone)
                        <div class="flex items-center gap-2 text-[12.5px]">
                            <x-icon name="phone" class="size-4 text-slate-400" />
                            <span class="text-slate-600">{{ auth()->user()->phone }}</span>
                        </div>
                    @endif
                    <div class="flex items-center gap-2 text-[12.5px]">
                        <x-icon name="clock" class="size-4 text-slate-400" />
                        <span class="text-slate-600">Last login {{ auth()->user()->last_login_at?->diffForHumans() ?? 'never' }}</span>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Security / info --}}
        <div class="space-y-5 lg:col-span-2">
            <div class="card">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-[14px] font-bold text-heading">Access &amp; permissions</h2>
                    <p class="mt-0.5 text-[12px] text-muted">What your role allows you to do</p>
                </div>
                <div class="grid gap-2.5 px-5 py-4 sm:grid-cols-2">
                    @php
                        $perms = [
                            ['projects.view', 'View projects', 'folder-kanban'],
                            ['projects.manage', 'Create & edit projects', 'folder-pen'],
                            ['tasks.manage', 'Manage tasks', 'list-checks'],
                            ['finance.view', 'View finances', 'wallet'],
                            ['finance.manage', 'Manage expenses', 'receipt'],
                            ['orders.view', 'View orders', 'shopping-cart'],
                            ['customers.view', 'View customers', 'building-2'],
                            ['staff.view', 'View staff', 'users-round'],
                            ['reports.export', 'Export reports', 'bar-chart-3'],
                            ['activity.view', 'View activity log', 'history'],
                        ];
                    @endphp
                    @foreach ($perms as [$perm, $label, $icon])
                        @php $allowed = auth()->user()->can($perm); @endphp
                        <div class="flex items-center gap-2.5 rounded-lg border px-3 py-2 {{ $allowed ? 'border-emerald-100 bg-emerald-50/50' : 'border-slate-100 bg-slate-50/50' }}">
                            <x-icon :name="$icon" class="size-4 {{ $allowed ? 'text-emerald-600' : 'text-slate-300' }}" />
                            <span class="flex-1 text-[12.5px] {{ $allowed ? 'font-medium text-slate-700' : 'text-slate-400' }}">{{ $label }}</span>
                            <x-icon :name="$allowed ? 'check' : 'x'" class="size-3.5 {{ $allowed ? 'text-emerald-600' : 'text-slate-300' }}" />
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-[14px] font-bold text-heading">Workspace</h2>
                </div>
                <div class="px-5 py-4">
                    <p class="text-[12.5px] leading-relaxed text-muted">
                        You're signed in to the <span class="font-semibold text-heading">ProjectFlow</span> demo workspace.
                        Use the <span class="font-semibold text-heading">Simulate role</span> switcher in the sidebar to preview
                        the app exactly as another role sees it.
                    </p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('dashboard') }}" class="btn-ghost btn-sm">
                            <x-icon name="layout-dashboard" class="size-3.5" /> Go to dashboard
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn-danger btn-sm">
                                <x-icon name="log-out" class="size-3.5" /> Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-layouts.app>
