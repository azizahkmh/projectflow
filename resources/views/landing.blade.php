<x-layouts.public title="Manage Projects & Finances seamlessly">

    {{-- ============================== HEADER ============================= --}}
    <header x-data="{ open: false, scrolled: false }"
            @scroll.window="scrolled = window.scrollY > 12"
            :class="scrolled ? 'bg-white/85 shadow-[0_1px_0_rgba(15,23,42,0.06),0_10px_30px_-24px_rgba(15,23,42,0.4)] backdrop-blur-xl' : 'bg-white/60 backdrop-blur-md'"
            class="fixed inset-x-0 top-0 z-50 border-b border-slate-200/70 transition duration-300">
        <div class="mx-auto flex h-16 max-w-7xl items-center gap-6 px-4 sm:px-6 lg:px-8">

            <a href="{{ route('landing') }}" class="flex items-center gap-2.5">
                <x-logo size="md" />
                <span class="text-[17px] font-extrabold tracking-[-0.02em] text-heading">
                    Project<span class="text-brand-500">Flow</span>
                </span>
            </a>

            <nav class="ml-6 hidden items-center gap-1 md:flex">
                @foreach (['Features' => '#features', 'Pricing' => '#pricing', 'About' => '#about'] as $label => $href)
                    <a href="{{ $href }}"
                       class="rounded-lg px-3 py-2 text-[13.5px] font-medium text-slate-600 transition hover:bg-slate-100 hover:text-heading">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            <div class="ml-auto flex items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary">
                        Go to dashboard
                        <x-icon name="arrow-right" class="size-4" />
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="hidden rounded-xl px-3.5 py-2 text-[13.5px] font-semibold text-slate-700 transition hover:bg-slate-100 hover:text-heading sm:inline-flex">
                        Sign In
                    </a>
                    <a href="{{ route('login') }}" class="btn-primary">
                        Get Started
                        <x-icon name="arrow-right" class="size-4" />
                    </a>
                @endauth

                <button type="button" @click="open = ! open"
                        class="inline-flex size-10 items-center justify-center rounded-xl text-slate-600 hover:bg-slate-100 md:hidden">
                    <x-icon name="menu" class="size-5" />
                </button>
            </div>
        </div>

        <div x-show="open" x-cloak x-transition.origin.top
             class="border-t border-slate-200 bg-white px-4 py-3 md:hidden">
            @foreach (['Features' => '#features', 'Pricing' => '#pricing', 'About' => '#about', 'Sign in' => route('login')] as $label => $href)
                <a href="{{ $href }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ $label }}</a>
            @endforeach
        </div>
    </header>

    {{-- =============================== HERO ============================== --}}
    <section class="relative overflow-hidden pt-32 pb-16 sm:pt-36 lg:pb-24">
        {{-- Ambient background --}}
        <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-b from-brand-50/60 via-canvas to-canvas"></div>
        <div class="pointer-events-none absolute inset-0 -z-10 grid-noise opacity-[0.35] [mask-image:radial-gradient(ellipse_at_top,black,transparent_70%)]"></div>
        <div class="pointer-events-none absolute -left-40 top-10 -z-10 size-[520px] rounded-full bg-brand-300/25 blur-[120px]"></div>
        <div class="pointer-events-none absolute -right-40 top-40 -z-10 size-[420px] rounded-full bg-emerald-200/25 blur-[120px]"></div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.05fr)]">

                {{-- Copy --}}
                <div class="animate-fade-up">
                    <span class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-white/80 px-3 py-1 text-[11.5px] font-semibold text-brand-700 shadow-sm backdrop-blur">
                        <span class="relative flex size-1.5">
                            <span class="absolute inline-flex size-full animate-ping rounded-full bg-brand-500 opacity-75"></span>
                            <span class="relative inline-flex size-1.5 rounded-full bg-brand-500"></span>
                        </span>
                        New · Automated finance reconciliation
                    </span>

                    <h1 class="mt-5 text-balance text-[clamp(2.15rem,4.6vw,3.6rem)] font-extrabold leading-[1.06] tracking-[-0.03em] text-heading">
                        Manage Projects &amp; Finances seamlessly with
                        <span class="relative whitespace-nowrap">
                            <span class="relative z-10">ProjectFlow</span>
                            <svg class="absolute -bottom-1.5 left-0 z-0 h-3 w-full text-brand-300" viewBox="0 0 200 12" preserveAspectRatio="none" fill="none" aria-hidden="true">
                                <path d="M2 9C50 3 120 2 198 7" stroke="currentColor" stroke-width="5" stroke-linecap="round" />
                            </svg>
                        </span>
                    </h1>

                    <p class="mt-5 max-w-xl text-[15.5px] leading-relaxed text-muted">
                        One calm workspace for delivery and the numbers that back it up — plan projects,
                        assign work, track orders, and watch margin in real time. No spreadsheets, no guesswork.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <a href="{{ route('login') }}" class="btn-primary !px-5 !py-3 text-[14px]">
                            Get Started free
                            <x-icon name="arrow-right" class="size-4" />
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn-ghost !px-5 !py-3 text-[14px]">
                            <x-icon name="play-circle" class="size-4 text-brand-600" />
                            Live Demo
                        </a>
                    </div>

                    <div class="mt-9 flex flex-wrap items-center gap-x-7 gap-y-4">
                        <div class="flex -space-x-2.5">
                            @foreach ([['Avery Coleman', '#84CC16'], ['Jordan Reyes', '#0EA5E9'], ['Priya N', '#A855F7'], ['Marcus Bell', '#F59E0B']] as [$name, $color])
                                <span class="inline-flex size-9 items-center justify-center rounded-full text-[11px] font-bold text-white ring-2 ring-white"
                                      style="background: linear-gradient(135deg, {{ $color }}, {{ $color }}C0);">{{ mb_substr($name, 0, 1) }}{{ mb_substr(strrchr($name, ' '), 1, 1) }}</span>
                            @endforeach
                        </div>
                        <div>
                            <div class="flex items-center gap-0.5 text-amber-400">
                                @for ($i = 0; $i < 5; $i++)
                                    <x-icon name="star" class="size-3.5 fill-current" />
                                @endfor
                                <span class="ml-1.5 text-[12px] font-bold text-heading">4.9/5</span>
                            </div>
                            <p class="text-[12px] text-muted">Trusted by 2,400+ delivery teams</p>
                        </div>
                    </div>
                </div>

                {{-- Dashboard mock-up --}}
                <div class="relative animate-fade-up [animation-delay:120ms]">
                    <div class="absolute -inset-4 -z-10 rounded-[28px] bg-gradient-to-tr from-brand-300/40 via-transparent to-emerald-200/40 blur-2xl"></div>

                    <div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-[0_40px_80px_-40px_rgba(15,23,42,0.4)]">
                        {{-- Fake browser chrome --}}
                        <div class="flex items-center gap-2 border-b border-slate-200 bg-slate-50/90 px-3.5 py-2.5">
                            <span class="size-2.5 rounded-full bg-rose-400/80"></span>
                            <span class="size-2.5 rounded-full bg-amber-400/80"></span>
                            <span class="size-2.5 rounded-full bg-emerald-400/80"></span>
                            <div class="ml-3 flex-1 rounded-md bg-white px-2.5 py-1 text-[10px] text-slate-400 ring-1 ring-slate-200">
                                app.projectflow.io/dashboard
                            </div>
                        </div>

                        <div class="flex">
                            {{-- Mini sidebar --}}
                            <div class="hidden w-[104px] shrink-0 flex-col gap-1 bg-ink-900 p-2.5 sm:flex">
                                <div class="mb-2 flex items-center gap-1.5">
                                    <span class="inline-flex size-5 items-center justify-center rounded-md bg-brand-400 text-ink-900">
                                        <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M5 19V5.5A1.5 1.5 0 0 1 6.5 4H13a4.5 4.5 0 0 1 0 9H5" /></svg>
                                    </span>
                                    <span class="text-[9px] font-extrabold text-white">ProjectFlow</span>
                                </div>
                                @foreach ([['layout-dashboard', 'Dashboard', true], ['folder-kanban', 'Projects', false], ['list-checks', 'Tasks', false], ['wallet', 'Finance', false], ['bar-chart-3', 'Reports', false]] as [$icon, $label, $active])
                                    <div class="flex items-center gap-1.5 rounded-md px-1.5 py-1.5 text-[9px] {{ $active ? 'bg-brand-400/10 font-semibold text-brand-300' : 'text-slate-400' }}">
                                        <x-icon :name="$icon" class="size-2.5" />
                                        {{ $label }}
                                    </div>
                                @endforeach
                            </div>

                            {{-- Mini dashboard --}}
                            <div class="min-w-0 flex-1 bg-canvas p-3">
                                <div class="mb-2.5 flex items-center justify-between">
                                    <div>
                                        <p class="text-[10px] font-bold text-heading">Good morning, Avery</p>
                                        <p class="text-[8px] text-muted">Here's what's moving today</p>
                                    </div>
                                    <span class="rounded-full bg-brand-400 px-2 py-0.5 text-[8px] font-bold text-ink-900">+ New</span>
                                </div>

                                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                    @foreach ([['Active Projects', '12', 'folder-kanban'], ['Pending Tasks', '48', 'clock'], ['Revenue', '$24.5k', 'trending-up'], ['Expenses', '$8.2k', 'trending-down']] as [$label, $value, $icon])
                                        <div class="rounded-lg border border-slate-200/80 bg-white p-2 shadow-sm">
                                            <div class="flex items-center justify-between">
                                                <span class="text-[7px] font-semibold uppercase tracking-wide text-muted">{{ $label }}</span>
                                                <x-icon :name="$icon" class="size-2 text-brand-600" />
                                            </div>
                                            <p class="mt-1 text-[13px] font-extrabold leading-none text-heading">{{ $value }}</p>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-[1.4fr_1fr]">
                                    <div class="rounded-lg border border-slate-200/80 bg-white p-2.5 shadow-sm">
                                        <div class="mb-1.5 flex items-center justify-between">
                                            <span class="text-[8px] font-bold text-heading">Tasks completed</span>
                                            <span class="rounded bg-brand-50 px-1 py-0.5 text-[7px] font-bold text-brand-700">+18%</span>
                                        </div>
                                        <svg viewBox="0 0 200 64" class="h-16 w-full" preserveAspectRatio="none" aria-hidden="true">
                                            <defs>
                                                <linearGradient id="heroGrad" x1="0" y1="0" x2="0" y2="1">
                                                    <stop offset="0%" stop-color="#A3E635" stop-opacity="0.5" />
                                                    <stop offset="100%" stop-color="#A3E635" stop-opacity="0" />
                                                </linearGradient>
                                            </defs>
                                            <path d="M0 50 L28 42 L57 46 L85 30 L114 34 L142 18 L171 22 L199 8 L199 64 L0 64 Z" fill="url(#heroGrad)" />
                                            <path d="M0 50 L28 42 L57 46 L85 30 L114 34 L142 18 L171 22 L199 8" fill="none" stroke="#84CC16" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" />
                                        </svg>
                                    </div>

                                    <div class="rounded-lg border border-slate-200/80 bg-white p-2.5 shadow-sm">
                                        <span class="text-[8px] font-bold text-heading">Recent projects</span>
                                        <div class="mt-1.5 space-y-1.5">
                                            @foreach ([['Web Redesign', 78, 'In Progress'], ['Mobile MVP', 46, 'In Progress'], ['Billing Bot', 12, 'Planning']] as [$name, $pct, $status])
                                                <div>
                                                    <div class="flex items-center justify-between text-[7px]">
                                                        <span class="font-semibold text-slate-600">{{ $name }}</span>
                                                        <span class="text-slate-400">{{ $pct }}%</span>
                                                    </div>
                                                    <div class="mt-0.5 h-[3px] w-full overflow-hidden rounded-full bg-slate-100">
                                                        <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-brand-300" style="width: {{ $pct }}%"></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Floating cards --}}
                    <div class="absolute -bottom-5 -left-3 hidden animate-fade-up items-center gap-2.5 rounded-xl border border-slate-200 bg-white/95 px-3.5 py-2.5 shadow-lift backdrop-blur [animation-delay:400ms] sm:flex">
                        <span class="inline-flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                            <x-icon name="badge-check" class="size-4" />
                        </span>
                        <div>
                            <p class="text-[11px] font-bold text-heading">Invoice paid</p>
                            <p class="text-[10px] text-muted">$4,200 · Northwind</p>
                        </div>
                    </div>

                    <div class="absolute -right-2 top-10 hidden animate-fade-up items-center gap-2 rounded-xl border border-slate-200 bg-white/95 px-3 py-2 shadow-lift backdrop-blur [animation-delay:550ms] lg:flex">
                        <span class="relative flex size-2">
                            <span class="absolute inline-flex size-full animate-ping rounded-full bg-brand-500 opacity-70"></span>
                            <span class="relative inline-flex size-2 rounded-full bg-brand-500"></span>
                        </span>
                        <p class="text-[10.5px] font-semibold text-heading">12 tasks shipped today</p>
                    </div>
                </div>
            </div>

            {{-- Trust bar --}}
            <div class="mt-16 border-t border-slate-200/80 pt-7">
                <p class="text-center text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">
                    Powering delivery at fast-moving teams
                </p>
                <div class="mt-4 flex flex-wrap items-center justify-center gap-x-10 gap-y-4 opacity-60">
                    @foreach (['Northwind', 'Vertex Health', 'Lumen', 'Orbit', 'Harbor & Co.'] as $brand)
                        <span class="text-[15px] font-extrabold tracking-tight text-slate-500">{{ $brand }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ============================= FEATURES ============================ --}}
    <section id="features" class="scroll-mt-20 border-y border-slate-200/70 bg-white py-20 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="eyebrow">Everything in one flow</p>
                <h2 class="mt-2 text-balance text-[clamp(1.7rem,3vw,2.4rem)] font-extrabold tracking-[-0.025em] text-heading">
                    Built for the whole job — not just the gantt chart
                </h2>
                <p class="mt-3 text-[15px] leading-relaxed text-muted">
                    Four modules that talk to each other. Move a deadline and the forecast updates.
                    Close an order and the dashboard knows.
                </p>
            </div>

            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['list-checks', 'Tasks', 'Kanban boards, priorities, assignees and due dates. Drag to move — the burn-down chart keeps up.', 'from-lime-50 to-white', 'text-brand-600 bg-brand-100'],
                    ['wallet', 'Financial Tracking', 'Track revenue against expenses by project. Margin, cashflow and outstanding invoices in one glance.', 'from-emerald-50 to-white', 'text-emerald-600 bg-emerald-100'],
                    ['shopping-cart', 'Orders', 'Quote, issue and reconcile client orders. Auto-numbered references and status workflows.', 'from-sky-50 to-white', 'text-sky-600 bg-sky-100'],
                    ['users-round', 'Team Management', 'Role-based access for owners, managers, staff, finance and clients — everyone sees exactly their slice.', 'from-violet-50 to-white', 'text-violet-600 bg-violet-100'],
                ] as [$icon, $title, $copy, $gradient, $iconClass])
                    <div class="card card-hover group relative overflow-hidden bg-gradient-to-b {{ $gradient }} p-5">
                        <span class="inline-flex size-11 items-center justify-center rounded-xl {{ $iconClass }} ring-1 ring-black/5 transition duration-300 group-hover:scale-105">
                            <x-icon :name="$icon" class="size-5" />
                        </span>
                        <h3 class="mt-4 text-[15px] font-bold tracking-tight text-heading">{{ $title }}</h3>
                        <p class="mt-1.5 text-[13px] leading-relaxed text-muted">{{ $copy }}</p>
                        <span class="mt-4 inline-flex items-center gap-1 text-[12px] font-semibold text-brand-600 opacity-0 transition group-hover:opacity-100">
                            Learn more <x-icon name="arrow-right" class="size-3.5" />
                        </span>
                    </div>
                @endforeach
            </div>

            {{-- Secondary feature strip --}}
            <div class="mt-5 grid gap-5 md:grid-cols-3">
                @foreach ([
                    ['bar-chart-3', 'Live reporting', 'Export PDF performance reports in one click.'],
                    ['shield-check', 'Granular RBAC', 'Five built-in roles, policy-driven permissions.'],
                    ['history', 'Audit trail', 'Every action logged with actor, IP and timestamp.'],
                ] as [$icon, $title, $copy])
                    <div class="flex items-start gap-3 rounded-xl border border-slate-200/80 bg-slate-50/60 p-4">
                        <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-lg bg-white text-brand-600 ring-1 ring-slate-200">
                            <x-icon :name="$icon" class="size-4" />
                        </span>
                        <div>
                            <p class="text-[13px] font-bold text-heading">{{ $title }}</p>
                            <p class="mt-0.5 text-[12.5px] leading-relaxed text-muted">{{ $copy }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================== PRICING ============================ --}}
    <section id="pricing" class="scroll-mt-20 py-20 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="eyebrow">Simple pricing</p>
                <h2 class="mt-2 text-[clamp(1.7rem,3vw,2.4rem)] font-extrabold tracking-[-0.025em] text-heading">
                    Pay for the team you have, not seats you don't
                </h2>
                <p class="mt-3 text-[15px] text-muted">Every plan includes unlimited projects, orders and the audit log.</p>
            </div>

            <div x-data="{ annual: true }" class="mt-10">
                <div class="mb-8 flex items-center justify-center gap-3">
                    <span class="text-[13px] font-semibold" :class="! annual ? 'text-heading' : 'text-muted'">Monthly</span>
                    <button type="button" @click="annual = ! annual"
                            :class="annual ? 'bg-brand-400' : 'bg-slate-300'"
                            class="relative h-6 w-11 rounded-full transition-colors duration-200">
                        <span :class="annual ? 'translate-x-[22px]' : 'translate-x-0.5'"
                              class="absolute top-0.5 size-5 rounded-full bg-white shadow transition-transform duration-200"></span>
                    </button>
                    <span class="text-[13px] font-semibold" :class="annual ? 'text-heading' : 'text-muted'">
                        Annual <span class="ml-1 rounded bg-brand-100 px-1.5 py-0.5 text-[10px] font-bold text-brand-700">-20%</span>
                    </span>
                </div>

                <div class="grid gap-5 lg:grid-cols-3">
                    @foreach ([
                        ['Starter', 0, 19, 'For solo operators getting organised', ['Up to 3 projects', 'Task boards', 'Basic reports', '1 admin seat']],
                        ['Growth', 1, 49, 'For teams running multiple clients', ['Unlimited projects', 'Finance & expenses', 'Orders + invoicing', 'PDF exports', '10 seats']],
                        ['Scale', 2, 99, 'For agencies and delivery orgs', ['Everything in Growth', 'Role simulation & SSO', 'Custom dashboards', 'Unlimited seats', 'Priority support']],
                    ] as [$name, $idx, $monthly, $tagline, $features])
                        <div class="relative flex flex-col rounded-2xl border p-6 transition duration-300
                                    {{ $idx === 1 ? 'border-brand-300 bg-ink-900 text-white shadow-[0_30px_60px_-30px_rgba(15,23,42,0.55)] lg:-mt-2 lg:mb-2' : 'border-slate-200 bg-white shadow-soft hover:-translate-y-0.5 hover:shadow-lift' }}">

                            @if ($idx === 1)
                                <span class="absolute -top-3 left-6 rounded-full bg-brand-400 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide text-ink-900">
                                    Most popular
                                </span>
                            @endif

                            <p class="text-[13px] font-bold {{ $idx === 1 ? 'text-brand-300' : 'text-brand-600' }}">{{ $name }}</p>
                            <p class="mt-1 text-[12.5px] {{ $idx === 1 ? 'text-slate-400' : 'text-muted' }}">{{ $tagline }}</p>

                            <p class="mt-5 flex items-baseline gap-1">
                                <span class="text-[34px] font-extrabold tracking-tight">$<span x-text="annual ? {{ (int) round($monthly * 0.8) }} : {{ $monthly }}">{{ $monthly }}</span></span>
                                <span class="text-[12.5px] {{ $idx === 1 ? 'text-slate-400' : 'text-muted' }}">/month</span>
                            </p>

                            <ul class="mt-5 flex-1 space-y-2.5">
                                @foreach ($features as $feature)
                                    <li class="flex items-start gap-2 text-[13px] {{ $idx === 1 ? 'text-slate-300' : 'text-slate-600' }}">
                                        <x-icon name="check" class="mt-0.5 size-3.5 shrink-0 {{ $idx === 1 ? 'text-brand-400' : 'text-brand-600' }}" />
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>

                            <a href="{{ route('login') }}" class="mt-6 {{ $idx === 1 ? 'btn-primary' : 'btn-ghost' }} w-full">
                                {{ $monthly === 0 ? 'Start free' : 'Choose '.$name }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- =============================== ABOUT ============================= --}}
    <section id="about" class="scroll-mt-20 border-y border-slate-200/70 bg-ink-900 py-20 text-white lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-brand-400">Our story</p>
                    <h2 class="mt-2 text-balance text-[clamp(1.7rem,3vw,2.4rem)] font-extrabold tracking-[-0.025em]">
                        We got tired of two versions of the truth
                    </h2>
                    <p class="mt-4 text-[14.5px] leading-relaxed text-slate-300">
                        Project tools tracked the work. Accounting tools tracked the money. Nobody tracked both.
                        ProjectFlow was born inside a 20-person studio that lost a client over a mis-quoted margin —
                        so we built the single source of truth we wished we had.
                    </p>
                    <p class="mt-3 text-[14.5px] leading-relaxed text-slate-400">
                        Today it runs 12,000+ projects a month for teams that refuse to choose between shipping well
                        and knowing their numbers.
                    </p>

                    <div class="mt-8 grid grid-cols-3 gap-4">
                        @foreach ([['12k+', 'Projects run monthly'], ['99.98%', 'Uptime last year'], ['2,400+', 'Teams onboard']] as [$stat, $label])
                            <div class="rounded-xl border border-white/10 bg-white/[0.04] p-3.5">
                                <p class="text-[20px] font-extrabold text-brand-400">{{ $stat }}</p>
                                <p class="mt-0.5 text-[11px] leading-tight text-slate-400">{{ $label }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Testimonial --}}
                <figure class="relative rounded-2xl border border-white/10 bg-gradient-to-br from-white/[0.07] to-transparent p-7 backdrop-blur">
                    <x-icon name="quote" class="size-8 text-brand-400/60" />
                    <blockquote class="mt-4 text-[16.5px] font-medium leading-relaxed text-slate-100">
                        "We closed the month in two hours instead of two days. The finance view and the delivery
                        board finally agree with each other."
                    </blockquote>
                    <figcaption class="mt-6 flex items-center gap-3">
                        <x-avatar name="Chloe Brandt" color="#84CC16" size="lg" />
                        <div>
                            <p class="text-[13.5px] font-bold text-white">Chloe Brandt</p>
                            <p class="text-[12px] text-slate-400">Operations Director · Lumen Studios</p>
                        </div>
                    </figcaption>
                </figure>
            </div>

            {{-- Final CTA --}}
            <div class="mt-16 overflow-hidden rounded-2xl border border-brand-400/20 bg-gradient-to-r from-brand-400/[0.12] via-white/[0.03] to-transparent p-8 sm:p-10">
                <div class="flex flex-col items-start gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-[20px] font-extrabold tracking-tight">Ready to see the whole picture?</h3>
                        <p class="mt-1 text-[13.5px] text-slate-400">Spin up the demo workspace — pre-seeded with projects, orders and expenses.</p>
                    </div>
                    <div class="flex gap-2.5">
                        <a href="{{ route('login') }}" class="btn-primary !px-5 !py-3">Sign in to demo</a>
                        <a href="{{ route('access.create') }}" class="btn border border-white/15 bg-white/5 !px-5 !py-3 text-white hover:bg-white/10">Request access</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- =============================== FOOTER ============================ --}}
    <footer class="bg-canvas py-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-10 md:grid-cols-[1.4fr_1fr_1fr_1fr]">
                <div>
                    <a href="{{ route('landing') }}" class="flex items-center gap-2.5">
                        <x-logo size="md" />
                        <span class="text-[16px] font-extrabold tracking-[-0.02em] text-heading">
                            Project<span class="text-brand-500">Flow</span>
                        </span>
                    </a>
                    <p class="mt-3 max-w-xs text-[13px] leading-relaxed text-muted">
                        The project and finance workspace for teams that ship. Plan, deliver, invoice — in one flow.
                    </p>
                    <div class="mt-4 flex gap-2">
                        @foreach (['twitter', 'linkedin', 'github', 'mail'] as $social)
                            <a href="#" class="inline-flex size-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:border-brand-300 hover:text-brand-600">
                                <x-icon :name="$social" class="size-3.5" />
                            </a>
                        @endforeach
                    </div>
                </div>

                @foreach ([
                    'Product' => ['Features' => '#features', 'Pricing' => '#pricing', 'Live demo' => '/dashboard', 'Changelog' => '#'],
                    'Company' => ['About' => '#about', 'Careers' => '#', 'Blog' => '#', 'Contact' => '#'],
                    'Resources' => ['Documentation' => '#', 'API reference' => '#', 'Status' => '#', 'Sign in' => '/login'],
                ] as $heading => $links)
                    <div>
                        <p class="text-[12px] font-bold uppercase tracking-wider text-heading">{{ $heading }}</p>
                        <ul class="mt-3 space-y-2">
                            @foreach ($links as $label => $href)
                                <li>
                                    <a href="{{ $href }}" class="text-[13px] text-muted transition hover:text-brand-600">{{ $label }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            <div class="mt-12 flex flex-col items-center justify-between gap-3 border-t border-slate-200 pt-6 sm:flex-row">
                <p class="text-[12px] text-muted">&copy; {{ date('Y') }} ProjectFlow Inc. All rights reserved.</p>
                <div class="flex items-center gap-5 text-[12px] text-muted">
                    <a href="#" class="hover:text-heading">Privacy</a>
                    <a href="#" class="hover:text-heading">Terms</a>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="size-1.5 rounded-full bg-emerald-500"></span> All systems operational
                    </span>
                </div>
            </div>
        </div>
    </footer>

</x-layouts.public>
