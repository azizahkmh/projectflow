<x-layouts.guest title="Request access">

    <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-ink-900 px-4 py-10">
        <div class="pointer-events-none absolute inset-0 grid-noise opacity-[0.06]"></div>
        <div class="pointer-events-none absolute -right-32 top-0 size-[520px] rounded-full bg-brand-500/10 blur-[130px]"></div>

        <a href="{{ route('login') }}"
           class="absolute left-5 top-5 inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-[12.5px] font-medium text-slate-400 transition hover:bg-white/5 hover:text-white">
            <x-icon name="arrow-left" class="size-3.5" /> Back to sign in
        </a>

        <div class="relative z-10 w-full max-w-[460px] animate-fade-up">
            <div class="mb-6 flex flex-col items-center text-center">
                <x-logo size="lg" tone="dark" />
                <h1 class="mt-5 text-[20px] font-bold tracking-tight text-heading">Request access</h1>
                <p class="mt-1 max-w-sm text-[13px] text-muted">
                    Tell us who you are — an administrator will approve your workspace seat.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-7 shadow-[0_40px_80px_-30px_rgba(0,0,0,0.6)]">
                <form method="POST" action="{{ route('access.store') }}" class="space-y-4">
                    @csrf

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="name" class="label">Full name</label>
                            <input id="name" name="name" type="text" required value="{{ old('name') }}" placeholder="Jane Cooper"
                                   class="input @error('name') input-error @enderror">
                            @error('name')<p class="error"><x-icon name="alert-circle" class="size-3.5" />{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="company" class="label">Company <span class="font-normal text-muted">(optional)</span></label>
                            <input id="company" name="company" type="text" value="{{ old('company') }}" placeholder="Acme Inc."
                                   class="input">
                        </div>
                    </div>

                    <div>
                        <label for="email" class="label">Work email</label>
                        <input id="email" name="email" type="email" required value="{{ old('email') }}" placeholder="you@company.com"
                               class="input @error('email') input-error @enderror">
                        @error('email')<p class="error"><x-icon name="alert-circle" class="size-3.5" />{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="message" class="label">How will you use ProjectFlow? <span class="font-normal text-muted">(optional)</span></label>
                        <textarea id="message" name="message" rows="3" placeholder="We run ~15 client projects per quarter…"
                                  class="input resize-none">{{ old('message') }}</textarea>
                    </div>

                    <button type="submit" class="btn-primary w-full !py-3">
                        <x-icon name="send" class="size-4" />
                        Send request
                    </button>
                </form>
            </div>

            <p class="mt-6 text-center text-[13px] text-slate-500">
                Already have an account?
                <a href="{{ route('login') }}" class="font-semibold text-brand-400 hover:text-brand-300 hover:underline">Sign in</a>
            </p>
        </div>
    </div>

</x-layouts.guest>
