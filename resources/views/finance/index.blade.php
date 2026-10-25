<x-layouts.app title="Finance &amp; Expenses">

    <x-page-header
        eyebrow="Money"
        title="Finance &amp; Expenses"
        subtitle="Revenue, spend and margin — reconciled against delivery."
    >
        <x-slot:actions>
            @can('finance.manage')
                <button type="button" @click="$store.modals.show('new-expense')" class="btn-primary">
                    <x-icon name="plus" class="size-4" /> Log expense
                </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    {{-- Summary --}}
    <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['Revenue', '$'.number_format($summary['revenue'], 0), 'trending-up', 'text-emerald-600'],
            ['Outstanding', '$'.number_format($summary['outstanding'], 0), 'clock', 'text-amber-600'],
            ['Approved spend', '$'.number_format($summary['expenses'], 0), 'wallet', 'text-rose-600'],
            ['Pending approval', '$'.number_format($summary['pending_expenses'], 0), 'hourglass', 'text-slate-600'],
        ] as [$label, $value, $icon, $tone])
            <div class="card p-5">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">{{ $label }}</p>
                    <x-icon :name="$icon" class="size-4 text-slate-300" />
                </div>
                <p class="mt-1.5 text-[22px] font-extrabold tracking-tight {{ $tone }}">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-3">
        {{-- Cashflow chart --}}
        <div class="card xl:col-span-2">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-[14px] font-bold text-heading">Cashflow</h2>
                <p class="mt-0.5 text-[12px] text-muted">Revenue vs. expenses over the last 8 months</p>
            </div>
            <div class="px-3 py-4">
                <div class="h-[280px]"><canvas id="cashflowChart"></canvas></div>
            </div>
        </div>

        {{-- Spend by category --}}
        <div class="card">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-[14px] font-bold text-heading">Spend by category</h2>
            </div>
            <div class="space-y-3.5 px-5 py-4">
                @php $catTotal = max($byCategory->sum('total'), 1); @endphp
                @forelse ($byCategory as $row)
                    <div>
                        <div class="mb-1 flex items-center justify-between text-[12px]">
                            <span class="font-medium capitalize text-slate-600">{{ $row->category }}</span>
                            <span class="font-bold text-heading">${{ number_format($row->total, 0) }}</span>
                        </div>
                        <div class="progress"><div class="progress-bar" style="width: {{ round($row->total / $catTotal * 100) }}%"></div></div>
                    </div>
                @empty
                    <p class="text-[12.5px] text-muted">No approved expenses yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Expense ledger --}}
    <div class="card mt-5 overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-5 py-3.5">
            <h2 class="text-[14px] font-bold text-heading">Expense ledger</h2>
            <form method="GET" class="flex items-center gap-2">
                <select name="category" class="input !py-1.5 !text-[12.5px] !w-auto" onchange="this.form.submit()">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected(request('category') === $category)>{{ ucfirst($category) }}</option>
                    @endforeach
                </select>
                <select name="project" class="input !py-1.5 !text-[12.5px] !w-auto" onchange="this.form.submit()">
                    <option value="">All projects</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}" @selected(request('project') == $project->id)>{{ $project->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        @if ($expenses->isEmpty())
            <x-empty-state icon="wallet" title="No expenses found" description="Log your first expense to start tracking spend." />
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Expense</th>
                            <th>Category</th>
                            <th>Project</th>
                            <th>Date</th>
                            <th class="text-right">Amount</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($expenses as $expense)
                            <tr>
                                <td class="font-medium text-heading">{{ $expense->title }}</td>
                                <td class="capitalize">{{ $expense->category }}</td>
                                <td>{{ $expense->project?->name ?? '—' }}</td>
                                <td class="text-[12.5px]">{{ $expense->spent_on?->format('M j, Y') }}</td>
                                <td class="text-right font-bold text-heading">${{ number_format($expense->amount, 2) }}</td>
                                <td><x-status-badge :label="ucfirst($expense->status)" :classes="$expense->status_classes" /></td>
                                <td>
                                    @can('finance.manage')
                                        <div class="flex items-center justify-end gap-1">
                                            @if ($expense->status === 'pending')
                                                <form method="POST" action="{{ route('finance.expenses.approve', $expense) }}">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="btn-sm bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 hover:bg-emerald-100">
                                                        Approve
                                                    </button>
                                                </form>
                                            @endif
                                            <button type="button" @click="$dispatch('expense-edit', {{ $expense->toJson() }})"
                                                    title="Edit" aria-label="Edit"
                                                    class="inline-flex size-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-heading">
                                                <x-icon name="pencil" class="size-4" />
                                            </button>
                                            <form method="POST" action="{{ route('finance.expenses.destroy', $expense) }}"
                                                  onsubmit="return confirm('Delete this expense?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" title="Delete" aria-label="Delete"
                                                        class="inline-flex size-8 items-center justify-center rounded-lg text-rose-500 transition hover:bg-rose-50 hover:text-rose-600">
                                                    <x-icon name="trash-2" class="size-4" />
                                                </button>
                                            </form>
                                        </div>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-3">{{ $expenses->links() }}</div>
        @endif
    </div>

    {{-- New expense modal --}}
    @can('finance.manage')
        <x-modal name="edit-expense" title="Edit expense" description="Update the recorded expense." max-width="lg">
            <form method="POST"
                  x-data="{ expense: null }"
                  x-on:expense-edit.window="expense = $event.detail; $store.modals.show('edit-expense')"
                  :action="'/finance/expenses/' + (expense?.id ?? '')"
                  class="space-y-4 px-6 py-5">
                @csrf
                @method('PATCH')
                <div>
                    <label class="label">Title</label>
                    <input name="title" type="text" required :value="expense?.title" class="input">
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Category</label>
                        <select name="category" class="input">
                            @foreach ($categories as $category)
                                <option value="{{ $category }}" :selected="expense?.category === '{{ $category }}'">{{ ucfirst($category) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Amount ($)</label>
                        <input name="amount" type="number" step="0.01" min="0.01" required :value="expense?.amount" class="input">
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">Project</label>
                        <select name="project_id" class="input">
                            <option value="">None</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" :selected="String(expense?.project_id) === '{{ $project->id }}'">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Date spent</label>
                        <input name="spent_on" type="date" required :value="expense?.spent_on?.substring(0,10)" class="input">
                    </div>
                </div>
                <div>
                    <label class="label">Status</label>
                    <select name="status" class="input">
                        @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'reimbursed' => 'Reimbursed'] as $value => $label)
                            <option value="{{ $value }}" :selected="expense?.status === '{{ $value }}'">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" @click="$store.modals.close('edit-expense')" class="btn-ghost">Cancel</button>
                    <button type="submit" class="btn-primary">Save changes</button>
                </div>
            </form>
        </x-modal>
        <x-modal name="new-expense" title="Log expense" description="Record money spent against a project." max-width="lg">
            <form method="POST" action="{{ route('finance.expenses.store') }}" class="space-y-4 px-6 py-5">
                @csrf
                <div>
                    <label for="ne-title" class="label">Title</label>
                    <input id="ne-title" name="title" type="text" required value="{{ old('title') }}" placeholder="e.g. Figma subscription" class="input">
                    @error('title')<p class="error"><x-icon name="alert-circle" class="size-3.5" />{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="ne-category" class="label">Category</label>
                        <select id="ne-category" name="category" class="input">
                            @foreach ($categories as $category)
                                <option value="{{ $category }}" @selected(old('category') === $category)>{{ ucfirst($category) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="ne-amount" class="label">Amount ($)</label>
                        <input id="ne-amount" name="amount" type="number" step="0.01" min="0.01" required value="{{ old('amount') }}" class="input">
                        @error('amount')<p class="error"><x-icon name="alert-circle" class="size-3.5" />{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="ne-project" class="label">Project <span class="font-normal text-muted">(optional)</span></label>
                        <select id="ne-project" name="project_id" class="input">
                            <option value="">None</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="ne-date" class="label">Date spent</label>
                        <input id="ne-date" name="spent_on" type="date" required value="{{ old('spent_on', now()->toDateString()) }}" class="input">
                        @error('spent_on')<p class="error"><x-icon name="alert-circle" class="size-3.5" />{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label for="ne-status" class="label">Status</label>
                    <select id="ne-status" name="status" class="input">
                        @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'reimbursed' => 'Reimbursed'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', 'pending') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="ne-notes" class="label">Notes <span class="font-normal text-muted">(optional)</span></label>
                    <textarea id="ne-notes" name="notes" rows="2" class="input resize-none">{{ old('notes') }}</textarea>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" @click="$store.modals.close('new-expense')" class="btn-ghost">Cancel</button>
                    <button type="submit" class="btn-primary"><x-icon name="check" class="size-4" /> Save expense</button>
                </div>
            </form>
        </x-modal>
    @endcan

<script>
window.addEventListener('load', function () {
    const canvas = document.getElementById('cashflowChart');
    if (canvas) {
        makeAreaChart(canvas, @json($cashflow['labels']), [
            { label: 'Revenue', data: @json($cashflow['revenue']), color: '#84CC16' },
            { label: 'Expenses', data: @json($cashflow['expenses']), color: '#F43F5E' },
        ]);
    }
});
</script>

</x-layouts.app>
