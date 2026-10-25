<x-layouts.app title="Orders">

    <x-page-header eyebrow="Revenue" title="Orders" subtitle="Client orders and invoices, with live payment status.">
        <x-slot:actions>
            @can('orders.manage')
                <button type="button" @click="$store.modals.show('new-order')" class="btn-primary">
                    <x-icon name="plus" class="size-4" /> New order
                </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="mt-5 grid gap-4 sm:grid-cols-3">
        <div class="card p-5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Collected</p>
            <p class="mt-1 text-[22px] font-extrabold tracking-tight text-emerald-600">${{ number_format($summary['paid'], 2) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Outstanding</p>
            <p class="mt-1 text-[22px] font-extrabold tracking-tight text-amber-600">${{ number_format($summary['pending'], 2) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Total orders</p>
            <p class="mt-1 text-[22px] font-extrabold tracking-tight text-heading">{{ number_format($summary['count']) }}</p>
        </div>
    </div>

    <div class="card mt-5 overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-5 py-3.5">
            <h2 class="text-[14px] font-bold text-heading">All orders</h2>
            <form method="GET" class="flex items-center gap-2">
                <select name="status" class="input !py-1.5 !text-[12.5px] !w-auto" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    @foreach (['pending', 'paid', 'overdue', 'refunded', 'cancelled'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        @if ($orders->isEmpty())
            <x-empty-state icon="receipt" title="No orders yet" description="Create an order to start invoicing clients." />
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Customer</th>
                            <th>Project</th>
                            <th>Issued</th>
                            <th class="text-right">Total</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td><span class="font-mono text-[12px] font-semibold text-heading">{{ $order->reference }}</span></td>
                                <td class="font-medium text-heading">{{ $order->customer?->company_name ?? '—' }}</td>
                                <td>{{ $order->project?->name ?? '—' }}</td>
                                <td class="text-[12.5px]">{{ $order->issued_at?->format('M j, Y') }}</td>
                                <td class="text-right font-bold text-heading">${{ number_format($order->total, 2) }}</td>
                                <td><x-status-badge :label="ucfirst($order->status)" :classes="$order->status_classes" /></td>
                                <td class="text-right">
                                    @can('orders.manage')
                                        <x-dropdown align="right" width="44">
                                            <x-slot:trigger>
                                                <button type="button" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-heading">
                                                    <x-icon name="more-horizontal" class="size-4" />
                                                </button>
                                            </x-slot:trigger>
                                            @foreach (['paid', 'pending', 'overdue', 'refunded', 'cancelled'] as $s)
                                                @continue($order->status === $s)
                                                <form method="POST" action="{{ route('orders.status', $order) }}">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="{{ $s }}">
                                                    <button type="submit" class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-[12px] text-slate-600 hover:bg-slate-50">
                                                        Mark {{ $s }}
                                                    </button>
                                                </form>
                                            @endforeach
                                            <button type="button" @click="$dispatch('order-edit', {{ $order->toJson() }})"
                                                    class="flex w-full items-center gap-2 border-t border-slate-100 px-3 py-1.5 text-left text-[12px] text-slate-600 hover:bg-slate-50">
                                                <x-icon name="pencil" class="size-3.5 text-slate-400" /> Edit order
                                            </button>
                                        </x-dropdown>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-3">{{ $orders->links() }}</div>
        @endif
    </div>

    {{-- New order modal --}}
    @can('orders.manage')
        <x-modal name="edit-order" title="Edit order" description="Adjust amounts or status � no new orders or deletions here." max-width="xl">
            <form method="POST"
                  x-data="{ order: null }"
                  x-on:order-edit.window="order = $event.detail; $store.modals.show('edit-order')"
                  :action="'/orders/' + (order?.id ?? '')"
                  class="space-y-4 px-6 py-5">
                @csrf
                @method('PATCH')
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="label">Subtotal ($)</label>
                        <input name="subtotal" type="number" step="0.01" min="0" required :value="order?.subtotal" class="input">
                    </div>
                    <div>
                        <label class="label">Tax ($)</label>
                        <input name="tax" type="number" step="0.01" min="0" :value="order?.tax" class="input">
                    </div>
                    <div>
                        <label class="label">Status</label>
                        <select name="status" class="input">
                            @foreach (['pending', 'paid', 'overdue', 'refunded', 'cancelled'] as $s)
                                <option value="{{ $s }}" :selected="order?.status === '{{ $s }}'">{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" @click="$store.modals.close('edit-order')" class="btn-ghost">Cancel</button>
                    <button type="submit" class="btn-primary">Save changes</button>
                </div>
            </form>
        </x-modal>
        <x-modal name="new-order" title="New order" description="Issue an invoice against a customer." max-width="xl">
            <form method="POST" action="{{ route('orders.store') }}" class="space-y-4 px-6 py-5">
                @csrf
                <div>
                    <label for="no-title" class="label">Description</label>
                    <input id="no-title" name="title" type="text" required value="{{ old('title') }}" placeholder="e.g. Q3 retainer" class="input">
                    @error('title')<p class="error"><x-icon name="alert-circle" class="size-3.5" />{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="no-customer" class="label">Customer</label>
                        <select id="no-customer" name="customer_id" required class="input">
                            <option value="">Select customer…</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="no-project" class="label">Project <span class="font-normal text-muted">(optional)</span></label>
                        <select id="no-project" name="project_id" class="input">
                            <option value="">None</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="no-subtotal" class="label">Subtotal ($)</label>
                        <input id="no-subtotal" name="subtotal" type="number" step="0.01" min="0" required value="{{ old('subtotal', 0) }}" class="input">
                    </div>
                    <div>
                        <label for="no-tax" class="label">Tax ($)</label>
                        <input id="no-tax" name="tax" type="number" step="0.01" min="0" value="{{ old('tax', 0) }}" class="input">
                    </div>
                    <div>
                        <label for="no-status" class="label">Status</label>
                        <select id="no-status" name="status" class="input">
                            @foreach (['pending', 'paid', 'overdue'] as $s)
                                <option value="{{ $s }}" @selected(old('status', 'pending') === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="no-issued" class="label">Issue date</label>
                        <input id="no-issued" name="issued_at" type="date" required value="{{ old('issued_at', now()->toDateString()) }}" class="input">
                    </div>
                    <div>
                        <label for="no-due" class="label">Due date</label>
                        <input id="no-due" name="due_at" type="date" value="{{ old('due_at') }}" class="input">
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" @click="$store.modals.close('new-order')" class="btn-ghost">Cancel</button>
                    <button type="submit" class="btn-primary"><x-icon name="check" class="size-4" /> Create order</button>
                </div>
            </form>
        </x-modal>
    @endcan

</x-layouts.app>
