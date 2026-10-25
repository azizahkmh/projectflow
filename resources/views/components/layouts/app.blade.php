@php
    use App\Models\User;

    $user = auth()->user();

    $navigation = [
        ['label' => 'Dashboard',    'route' => 'dashboard',      'icon' => 'layout-dashboard', 'match' => 'dashboard',  'gate' => null],
        ['label' => 'Projects',     'route' => 'projects.index', 'icon' => 'folder-kanban',    'match' => 'projects*',  'gate' => 'projects.view'],
        ['label' => 'Tasks',        'route' => 'tasks.index',    'icon' => 'list-checks',      'match' => 'tasks*',     'gate' => 'tasks.view'],
        ['label' => 'Orders',       'route' => 'orders.index',   'icon' => 'shopping-cart',    'match' => 'orders*',    'gate' => 'orders.view'],
        ['label' => 'Finance & Expenses', 'route' => 'finance.index', 'icon' => 'wallet',     'match' => 'finance*',   'gate' => 'finance.view'],
        ['label' => 'Reports',      'route' => 'reports.index',  'icon' => 'bar-chart-3',      'match' => 'reports*',   'gate' => 'reports.view'],
        ['label' => 'Staff Management', 'route' => 'staff.index','icon' => 'users-round',      'match' => 'staff*',     'gate' => 'staff.view'],
        ['label' => 'Activity Log', 'route' => 'activity.index', 'icon' => 'history',          'match' => 'activity*',  'gate' => 'activity.view'],
    ];

    $visibleNav = collect($navigation)->filter(fn ($item) => $item['gate'] === null || $user->can($item['gate']));
    $currentRole = $user->roles->first()?->name;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} · ProjectFlow</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="h-full bg-canvas" x-data="{ sidebarOpen: false }">

<div class="flex min-h-screen">

    {{-- ============================ SIDEBAR ============================ --}}
    @include('partials.sidebar', ['visibleNav' => $visibleNav])

    {{-- Mobile backdrop --}}
    <div x-show="sidebarOpen" x-cloak x-transition.opacity
         class="fixed inset-0 z-30 bg-ink-950/60 backdrop-blur-sm lg:hidden"
         @click="sidebarOpen = false"></div>

    {{-- ============================ MAIN ============================== --}}
    <div class="flex min-h-screen w-full min-w-0 flex-1 flex-col lg:pl-[268px]">

        {{-- Topbar --}}
        <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/80 backdrop-blur-xl">
            <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">

                <button type="button" @click="sidebarOpen = true"
                        class="btn-ghost -ml-1 !px-2.5 lg:hidden" aria-label="Open menu">
                    <x-icon name="menu" class="size-5" />
                </button>

                {{-- Breadcrumb / page context --}}
                <div class="min-w-0 flex-1">
                    <nav class="hidden items-center gap-1.5 text-[12px] font-medium text-muted sm:flex">
                        <a href="{{ route('dashboard') }}" class="hover:text-heading">ProjectFlow</a>
                        <x-icon name="chevron-right" class="size-3.5 text-slate-300" />
                        <span class="text-heading">{{ $title ?? 'Dashboard' }}</span>
                    </nav>
                    <p class="truncate text-[14px] font-semibold text-heading sm:hidden">{{ $title ?? 'Dashboard' }}</p>
                </div>

                {{-- Global search --}}
                <div class="relative hidden md:block">
                    <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                    <input type="search" placeholder="Search projects, clients, tasks…"
                           class="input w-[290px] !py-2 !pl-9 !text-[13px]" />
                    <kbd class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[10px] font-semibold text-slate-400">⌘K</kbd>
                </div>

                <div class="flex items-center gap-1.5">
                    <x-dropdown align="right" width="80">
                        <x-slot:trigger>
                            <button type="button" class="relative inline-flex size-10 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-heading" aria-label="Notifications">
                                <x-icon name="bell" class="size-[19px]" />
                                <span class="absolute right-2.5 top-2.5 size-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
                            </button>
                        </x-slot:trigger>

                        <div class="border-b border-slate-100 px-4 py-3">
                            <p class="text-[13px] font-bold text-heading">Notifications</p>
                            <p class="text-[11px] text-muted">3 unread updates</p>
                        </div>
                        <div class="max-h-72 overflow-y-auto">
                            @foreach ([
                                ['check-circle', 'Task approved', 'QA & testing on Mobile App MVP', '12m'],
                                ['receipt', 'Invoice paid', 'Northwind Traders · $4,200.00', '1h'],
                                ['message-square', 'New client message', 'Vertex Health replied in comments', '3h'],
                            ] as [$icon, $t, $d, $time])
                                <div class="flex gap-3 px-4 py-3 hover:bg-slate-50">
                                    <span class="mt-0.5 inline-flex size-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                                        <x-icon :name="$icon" class="size-4" />
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-[12.5px] font-semibold text-heading">{{ $t }}</p>
                                        <p class="truncate text-[11.5px] text-muted">{{ $d }}</p>
                                    </div>
                                    <span class="ml-auto text-[10.5px] text-slate-400">{{ $time }}</span>
                                </div>
                            @endforeach
                        </div>
                    </x-dropdown>

                    <x-dropdown align="right" width="64">
                        <x-slot:trigger>
                            <button type="button" class="flex items-center gap-2 rounded-xl p-1 pr-2 transition hover:bg-slate-100">
                                <x-avatar :name="$user->name" :color="$user->avatar_color" size="sm" />
                                <x-icon name="chevron-down" class="size-3.5 text-slate-400" />
                            </button>
                        </x-slot:trigger>

                        <div class="border-b border-slate-100 px-4 py-3">
                            <p class="text-[13px] font-bold text-heading">{{ $user->name }}</p>
                            <p class="text-[11.5px] text-muted">{{ $user->email }}</p>
                        </div>
                        <a href="{{ route('profile.edit') ?? '#' }}" class="flex items-center gap-2.5 px-4 py-2.5 text-[13px] text-slate-600 hover:bg-slate-50 hover:text-heading">
                            <x-icon name="user-round" class="size-4 text-slate-400" /> My profile
                        </a>
                        <a href="{{ route('landing') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-[13px] text-slate-600 hover:bg-slate-50 hover:text-heading">
                            <x-icon name="globe" class="size-4 text-slate-400" /> Public site
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2.5 px-4 py-2.5 text-[13px] font-medium text-rose-600 hover:bg-rose-50">
                                <x-icon name="log-out" class="size-4" /> Sign out
                            </button>
                        </form>
                    </x-dropdown>
                </div>
            </div>
        </header>

        {{-- Flash + validation feedback --}}
        <main class="flex-1 grow px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            @if (session('status'))
                <div class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[13px] font-medium text-emerald-800">
                    <x-icon name="check-circle-2" class="mt-0.5 size-4 shrink-0" />
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-[13px] text-rose-800">
                    <x-icon name="alert-circle" class="mt-0.5 size-4 shrink-0" />
                    <div>
                        <p class="font-semibold">Please fix the following:</p>
                        <ul class="mt-1 list-inside list-disc space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="animate-fade-up">
                {{ $slot }}
            </div>
        </main>

        <footer class="border-t border-slate-200/80 px-4 py-4 text-[11.5px] text-muted sm:px-6 lg:px-8">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} ProjectFlow. Built for teams that ship.</p>
                <p>Signed in as <span class="font-semibold text-heading">{{ $user->primary_role_label }}</span></p>
            </div>
        </footer>
    </div>
</div>

{{-- Toast stack --}}
<div x-data class="pointer-events-none fixed bottom-5 right-5 z-[60] flex w-[320px] flex-col gap-2">
    <template x-for="toast in $store.toasts.items" :key="toast.id">
        <div x-transition.opacity.duration.250ms
             class="pointer-events-auto flex items-start gap-2.5 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-lift">
            <span class="mt-0.5 size-2 shrink-0 rounded-full bg-brand-500"></span>
            <p class="text-[12.5px] font-medium text-heading" x-text="toast.message"></p>
            <button @click="$store.toasts.dismiss(toast.id)" class="ml-auto text-slate-400 hover:text-heading">
                <x-icon name="x" class="size-3.5" />
            </button>
        </div>
    </template>
</div>

@stack('scripts')
</body>
</html>
