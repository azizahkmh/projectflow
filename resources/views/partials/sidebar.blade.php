@php
    use App\Models\User;

    $authUser = auth()->user();
    $currentRole = $authUser->roles->first()?->name;

    // The switcher stays available to the *real* owner even while previewing
    // another role, so it never disappears mid-session.
    $realRole = session('original_role') ?? $currentRole;
    $isRealOwner = $realRole === 'super-admin';
    $simulating = session('simulated_role') && session('simulated_role') !== $realRole;
@endphp

<aside x-cloak
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-40 flex w-[268px] flex-col border-r border-white/5 bg-ink-900 transition-transform duration-300 ease-out lg:translate-x-0">

    {{-- Brand + Role preview switcher ------------------------------------- --}}
    <div class="relative shrink-0 px-5 pb-4 pt-5">
        <div class="flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                <x-logo size="md" />
                <span class="text-[17px] font-extrabold tracking-[-0.02em] text-white">
                    Project<span class="text-brand-400">Flow</span>
                </span>
            </a>

            <button type="button" @click="sidebarOpen = false"
                    class="rounded-lg p-1.5 text-slate-400 hover:bg-white/10 hover:text-white lg:hidden">
                <x-icon name="x" class="size-4" />
            </button>
        </div>

        {{-- Preview-role switcher — OWNER ONLY (real role, survives simulation) --}}
        @if ($isRealOwner)
        <div class="mt-5" x-data="{ pickerOpen: false }">
            <div class="mb-1.5 flex items-center justify-between">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Preview as</p>
                <span class="rounded bg-brand-400/10 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-brand-400">dev</span>
            </div>

            <div class="relative">
                <button type="button" @click="pickerOpen = !pickerOpen"
                        class="flex w-full items-center gap-2.5 rounded-xl border border-white/10 bg-white/[0.05] px-3 py-2.5 text-left transition hover:border-white/20 hover:bg-white/[0.08]">
                    <span class="inline-flex size-7 shrink-0 items-center justify-center rounded-lg bg-brand-400 text-ink-900">
                        <x-icon name="shield-check" class="size-3.5" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-[12px] font-semibold text-white">
                            {{ User::ROLES[$currentRole] ?? 'No role' }}
                        </span>
                        <span class="block truncate text-[10px] text-slate-400">
                            {{ $simulating ? 'Previewing — your real role is Owner' : 'Switch role to preview UI' }}
                        </span>
                    </span>
                    <x-icon name="chevrons-up-down" class="size-3.5 shrink-0 text-slate-400" />
                </button>

                <div x-show="pickerOpen" x-cloak @click.outside="pickerOpen = false"
                     x-transition.origin.top.duration.150ms
                     class="absolute left-0 right-0 top-full z-50 mt-1.5 overflow-hidden rounded-xl border border-white/10 bg-ink-800 p-1 shadow-lift">
                    @foreach (User::ROLES as $slug => $label)
                        <form method="POST" action="{{ route('simulate-role.update') }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="role" value="{{ $slug }}">
                            <button type="submit"
                                    class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-left text-[12px] transition
                                           {{ $currentRole === $slug ? 'bg-brand-400/10 font-semibold text-brand-300' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                                <span class="inline-flex size-4 items-center justify-center">
                                    @if ($currentRole === $slug)
                                        <x-icon name="check" class="size-3.5 text-brand-400" />
                                    @else
                                        <span class="size-1.5 rounded-full bg-slate-500"></span>
                                    @endif
                                </span>
                                {{ $label }}
                            </button>
                        </form>
                    @endforeach

                    @if ($simulating)
                        <form method="POST" action="{{ route('simulate-role.reset') }}" class="border-t border-white/10">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="mt-1 flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-left text-[12px] font-semibold text-brand-300 hover:bg-white/5">
                                <x-icon name="rotate-ccw" class="size-3.5" />
                                Exit preview (back to Owner)
                            </button>
                        </form>
                    @endif

                    <p class="border-t border-white/10 px-2.5 pb-1 pt-2 text-[10px] leading-snug text-slate-500">
                        Preview only — your real role stays Owner.
                    </p>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Navigation -------------------------------------------------------- --}}
    <nav class="min-h-0 flex-1 space-y-0.5 overflow-y-auto px-3 pb-2">
        <p class="px-3 pb-1.5 pt-2 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Workspace</p>

        @foreach ($visibleNav as $item)
            <a href="{{ route($item['route']) }}"
               class="nav-link {{ request()->routeIs($item['match']) ? 'nav-link-active' : '' }}">
                <x-icon :name="$item['icon']" class="nav-icon" />
                <span class="truncate">{{ $item['label'] }}</span>

                @if ($item['route'] === 'tasks.index' && isset($taskOpenCount) && $taskOpenCount > 0)
                    <span class="ml-auto rounded-md bg-brand-400/15 px-1.5 py-0.5 text-[10px] font-bold text-brand-300">{{ $taskOpenCount }}</span>
                @elseif ($item['route'] === 'projects.index' && isset($projectCount) && $projectCount > 0)
                    <span class="ml-auto text-[10.5px] font-semibold text-slate-500">{{ $projectCount }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    {{-- Upgrade / help card ---------------------------------------------- --}}
    <div class="shrink-0 px-3 pb-3">
        <a href="{{ route('landing') }}#pricing"
           class="group relative block overflow-hidden rounded-xl border border-white/10 bg-gradient-to-br from-white/[0.08] to-transparent p-3.5 transition hover:border-brand-400/30">
            <div class="pointer-events-none absolute -right-7 -top-7 size-20 rounded-full bg-brand-400/20 blur-2xl transition group-hover:bg-brand-400/30"></div>
            <div class="flex items-center gap-2">
                <span class="inline-flex size-7 items-center justify-center rounded-lg bg-brand-400 text-ink-900">
                    <x-icon name="sparkles" class="size-3.5" />
                </span>
                <p class="text-[12px] font-bold text-white">Flow Pro</p>
            </div>
            <p class="mt-1.5 text-[10.5px] leading-relaxed text-slate-400">Unlock advanced reports, automation &amp; client portals.</p>
            <p class="mt-2 inline-flex items-center gap-1 text-[10.5px] font-semibold text-brand-400">
                Explore plans <x-icon name="arrow-right" class="size-3" />
            </p>
        </a>
    </div>

    {{-- User footer ------------------------------------------------------- --}}
    <div class="shrink-0 border-t border-white/10 p-3">
        <div class="flex items-center gap-2.5 rounded-xl px-1.5 py-1.5">
            <x-avatar :name="$authUser->name" :color="$authUser->avatar_color" size="sm" />
            <div class="min-w-0 flex-1">
                <p class="truncate text-[12px] font-semibold text-white">{{ $authUser->name }}</p>
                <p class="truncate text-[10px] text-slate-400">{{ $authUser->job_title ?? $authUser->primary_role_label }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Sign out"
                        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-rose-500/10 hover:text-rose-400">
                    <x-icon name="log-out" class="size-4" />
                </button>
            </form>
        </div>
    </div>
</aside>
