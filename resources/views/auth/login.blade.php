<x-layouts.guest title="Sign in">

    <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-ink-900 px-4 py-10">

        {{-- Backdrop: dark base + lime glows + fine grid --}}
        <div class="pointer-events-none absolute inset-0 grid-noise opacity-[0.06]"></div>
        <div class="pointer-events-none absolute -left-32 top-0 size-[560px] rounded-full bg-brand-500/10 blur-[130px]"></div>
        <div class="pointer-events-none absolute -bottom-40 -right-24 size-[520px] rounded-full bg-emerald-400/10 blur-[130px]"></div>
        <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-brand-400/30 to-transparent"></div>

        <a href="{{ route('landing') }}"
           class="absolute left-5 top-5 inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-[12.5px] font-medium text-slate-400 transition hover:bg-white/5 hover:text-white">
            <x-icon name="arrow-left" class="size-3.5" /> Back to site
        </a>

        <div class="relative z-10 w-full max-w-[420px] animate-fade-up">

            {{-- Brand --}}
            <div class="mb-6 flex flex-col items-center text-center">
                <a href="{{ route('landing') }}" class="flex items-center gap-2.5">
                    <x-logo size="lg" tone="dark" />
                    <span class="text-[22px] font-extrabold tracking-[-0.03em] text-white">
                        Project<span class="text-brand-400">Flow</span>
                    </span>
                </a>
                <h1 class="mt-5 text-[20px] font-bold tracking-tight text-heading">Welcome back</h1>
                <p class="mt-1 text-[13px] text-muted">Sign in to continue to your workspace</p>
            </div>

            {{-- Card --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-7 shadow-[0_40px_80px_-30px_rgba(0,0,0,0.6)]">

                @if (session('status'))
                    <div class="mb-5 flex items-start gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-[12.5px] font-medium text-emerald-800">
                        <x-icon name="check-circle-2" class="mt-0.5 size-4 shrink-0" />
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 flex items-start gap-2.5 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2.5 text-[12.5px] font-medium text-rose-700">
                        <x-icon name="alert-circle" class="mt-0.5 size-4 shrink-0" />
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="space-y-4" x-data="{ showPassword: false, loading: false }" @submit="loading = true">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label for="email" class="label">Email address</label>
                        <div class="relative">
                            <x-icon name="mail" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                            <input id="email" name="email" type="email" autocomplete="email" required autofocus
                                   value="{{ old('email') }}" placeholder="you@company.com"
                                   class="input !py-3 !pl-10 @error('email') input-error @enderror">
                        </div>
                        @error('email')<p class="error"><x-icon name="alert-circle" class="size-3.5" />{{ $message }}</p>@enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="label">Password</label>
                        <div class="relative">
                            <x-icon name="lock" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                            <input id="password" name="password" :type="showPassword ? 'text' : 'password'"
                                   autocomplete="current-password" required placeholder="••••••••"
                                   class="input !py-3 !pl-10 !pr-10 @error('password') input-error @enderror">
                            <button type="button" @click="showPassword = ! showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 rounded p-0.5 text-slate-400 transition hover:text-heading"
                                    :aria-label="showPassword ? 'Hide password' : 'Show password'">
                                <x-icon x-bind:name="showPassword ? 'eye-off' : 'eye'" name="eye" class="size-4" />
                            </button>
                        </div>
                        @error('password')<p class="error"><x-icon name="alert-circle" class="size-3.5" />{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center justify-between pt-0.5">
                        <label class="inline-flex cursor-pointer items-center gap-2 text-[12.5px] font-medium text-slate-600">
                            <input type="checkbox" name="remember" value="1" @checked(old('remember'))
                                   class="size-4 rounded border-slate-300 text-brand-500 accent-brand-500 focus:ring-brand-400">
                            Remember me
                        </label>
                        <a href="#" class="text-[12.5px] font-semibold text-brand-600 hover:text-brand-700 hover:underline">
                            Forgot password?
                        </a>
                    </div>

                    <button type="submit" class="btn-primary w-full !py-3" :disabled="loading">
                        <template x-if="loading">
                            <x-icon name="loader-circle" class="size-4 animate-spin" />
                        </template>
                        <template x-if="! loading">
                            <x-icon name="log-in" class="size-4" />
                        </template>
                        Sign In
                    </button>
                </form>

                <div class="my-5 flex items-center gap-3">
                    <span class="h-px flex-1 bg-slate-200"></span>
                    <span class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Demo accounts</span>
                    <span class="h-px flex-1 bg-slate-200"></span>
                </div>

                {{-- One-click demo logins (password is always "password") --}}
                <div class="grid grid-cols-2 gap-2">
                    @foreach ([
                        ['admin@projectflow.test', 'Owner', 'shield-check'],
                        ['manager@projectflow.test', 'Manager', 'folder-kanban'],
                        ['staff@projectflow.test', 'Staff', 'list-checks'],
                        ['finance@projectflow.test', 'Finance', 'wallet'],
                    ] as [$email, $label, $icon])
                        <form method="POST" action="{{ route('login.store') }}" class="contents">
                            @csrf
                            <input type="hidden" name="email" value="{{ $email }}">
                            <input type="hidden" name="password" value="password">
                            <button type="submit"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-2 py-2 text-[11.5px] font-semibold text-slate-600 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700">
                                <x-icon :name="$icon" class="size-3.5" />
                                {{ $label }}
                            </button>
                        </form>
                    @endforeach
                    <div class="hidden"></div>
                </div>

                <p class="mt-5 text-center text-[12.5px] text-muted">
                    Don't have an account?
                    <a href="{{ route('access.create') }}" class="font-semibold text-brand-600 hover:text-brand-700 hover:underline">
                        Request access
                    </a>
                </p>
            </div>

            <p class="mt-6 flex items-center justify-center gap-1.5 text-[11.5px] text-slate-500">
                <x-icon name="shield-check" class="size-3.5 text-brand-500" />
                Secured with role-based access control
            </p>
        </div>
    </div>

</x-layouts.guest>