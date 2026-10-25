<x-layouts.app title="Staff">

    <x-page-header eyebrow="People" title="Staff" subtitle="Your team, their roles and current workload.">
        <x-slot:actions>
            @can('staff.manage')
                <button type="button" @click="$store.modals.show('new-staff')" class="btn-primary">
                    <x-icon name="user-plus" class="size-4" /> Add member
                </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach (\App\Models\User::ROLES as $slug => $label)
            <a href="{{ route('staff.index', ['role' => $slug]) }}"
               class="card p-4 transition hover:border-brand-300 {{ request('role') === $slug ? 'ring-2 ring-brand-400' : '' }}">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">{{ $label }}</p>
                <p class="mt-1 text-[20px] font-extrabold tracking-tight text-heading">{{ $roleCounts[$slug] ?? 0 }}</p>
            </a>
        @endforeach
    </div>

    <div class="card mt-5 overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-5 py-3.5">
            <h2 class="text-[14px] font-bold text-heading">Team members</h2>
            <div class="flex items-center gap-2">
                @if (request()->hasAny(['role', 'q']))
                    <a href="{{ route('staff.index') }}" class="btn-ghost !py-1.5 !text-[12.5px]">Clear</a>
                @endif
                <form method="GET">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search name or email…" class="input !py-1.5 !text-[12.5px] !w-[220px]">
                </form>
            </div>
        </div>

        @if ($members->isEmpty())
            <x-empty-state icon="users-round" title="No team members found" />
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Role</th>
                            <th class="text-right">Open tasks</th>
                            <th class="text-right">Projects led</th>
                            <th>Last login</th>
                            <th>Status</th>
                            @can('staff.manage')<th></th>@endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($members as $member)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2.5">
                                        <x-avatar :name="$member->name" :color="$member->avatar_color" size="md" />
                                        <div>
                                            <p class="font-semibold text-heading">{{ $member->name }}</p>
                                            <p class="text-[11px] text-muted">{{ $member->job_title ?? $member->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600 ring-1 ring-slate-200">
                                        {{ \App\Models\User::ROLES[$member->getRoleNames()->first() ?? ''] ?? '—' }}
                                    </span>
                                </td>
                                <td class="text-right font-semibold">{{ $member->open_tasks_count }}</td>
                                <td class="text-right font-semibold">{{ $member->managed_projects_count }}</td>
                                <td class="text-[12.5px] text-muted">{{ $member->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                                <td>
                                    <x-status-badge :label="$member->is_active ? 'Active' : 'Inactive'"
                                                    :classes="$member->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-500 ring-slate-200'" />
                                </td>
                                @can('staff.manage')
                                    <td>
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button"
                                                    @click="$dispatch('staff-edit', {{ $member->toJson() }})"
                                                    title="Edit" aria-label="Edit"
                                                    class="inline-flex size-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-heading">
                                                <x-icon name="pencil" class="size-4" />
                                            </button>
                                            @if (auth()->id() !== $member->id)
                                                <form method="POST" action="{{ route('staff.toggle', $member) }}">
                                                    @csrf @method('PATCH')
                                                    <button type="submit"
                                                            title="{{ $member->is_active ? 'Deactivate' : 'Reactivate' }}" aria-label="{{ $member->is_active ? 'Deactivate' : 'Reactivate' }}"
                                                            class="inline-flex size-8 items-center justify-center rounded-lg transition {{ $member->is_active ? 'text-amber-500 hover:bg-amber-50 hover:text-amber-600' : 'text-emerald-500 hover:bg-emerald-50 hover:text-emerald-600' }}">
                                                        <x-icon name="{{ $member->is_active ? 'user-round-x' : 'user-round-check' }}" class="size-4" />
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('staff.destroy', $member) }}"
                                                      onsubmit="return confirm('Remove {{ addslashes($member->name) }}?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" title="Delete" aria-label="Delete"
                                                            class="inline-flex size-8 items-center justify-center rounded-lg text-rose-500 transition hover:bg-rose-50 hover:text-rose-600">
                                                        <x-icon name="trash-2" class="size-4" />
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-[11px] text-slate-400">You</span>
                                            @endif
                                        </div>
                                    </td>
                                @endcan
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-3">{{ $members->links() }}</div>
        @endif
    </div>

    @can('staff.manage')
        <x-modal name="edit-staff" title="Edit team member" description="Update their details and role." max-width="xl">
            <form method="POST"
                  x-data="{ editMember: null }"
                  x-on:staff-edit.window="editMember = $event.detail; $store.modals.show('edit-staff')"
                  :action="'/staff/' + (editMember?.id ?? '')"
                  class="space-y-4 px-6 py-5">
                @csrf
                @method('PATCH')
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Full name</label>
                        <input name="name" type="text" required :value="editMember?.name" class="input">
                    </div>
                    <div>
                        <label class="label">Email</label>
                        <input name="email" type="email" required :value="editMember?.email" class="input">
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Job title</label>
                        <input name="job_title" type="text" :value="editMember?.job_title" class="input">
                    </div>
                    <div>
                        <label class="label">Role</label>
                        <select name="role" class="input">
                            @foreach (\App\Models\User::ROLES as $value => $label)
                                <option value="{{ $value }}" :selected="editMember?.roles?.[0]?.name === '{{ $value }}'">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" @click="$store.modals.close('edit-staff')" class="btn-ghost">Cancel</button>
                    <button type="submit" class="btn-primary">Save changes</button>
                </div>
            </form>
        </x-modal>
        <x-modal name="new-staff" title="Add team member" description="Create an account and assign a role." max-width="xl">
            <form method="POST" action="{{ route('staff.store') }}" class="space-y-4 px-6 py-5">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="ns-name" class="label">Full name</label>
                        <input id="ns-name" name="name" type="text" required value="{{ old('name') }}" class="input">
                        @error('name')<p class="error"><x-icon name="alert-circle" class="size-3.5" />{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="ns-email" class="label">Email</label>
                        <input id="ns-email" name="email" type="email" required value="{{ old('email') }}" class="input">
                        @error('email')<p class="error"><x-icon name="alert-circle" class="size-3.5" />{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="ns-title" class="label">Job title</label>
                        <input id="ns-title" name="job_title" type="text" value="{{ old('job_title') }}" placeholder="e.g. Senior Developer" class="input">
                    </div>
                    <div>
                        <label for="ns-phone" class="label">Phone</label>
                        <input id="ns-phone" name="phone" type="text" value="{{ old('phone') }}" class="input">
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="ns-role" class="label">Role</label>
                        <select id="ns-role" name="role" class="input">
                            @foreach (\App\Models\User::ROLES as $value => $label)
                                <option value="{{ $value }}" @selected(old('role', 'staff') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('role')<p class="error"><x-icon name="alert-circle" class="size-3.5" />{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="ns-password" class="label">Temporary password</label>
                        <input id="ns-password" name="password" type="text" required value="{{ old('password') }}" class="input" placeholder="Min. 8 characters">
                        @error('password')<p class="error"><x-icon name="alert-circle" class="size-3.5" />{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" @click="$store.modals.close('new-staff')" class="btn-ghost">Cancel</button>
                    <button type="submit" class="btn-primary"><x-icon name="user-plus" class="size-4" /> Add member</button>
                </div>
            </form>
        </x-modal>
    @endcan

</x-layouts.app>
