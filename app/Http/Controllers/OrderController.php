<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Activity;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Project;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->can('orders.view'), 403);

        $query = Order::with(['customer', 'project'])->latest('issued_at');


        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        return view('orders.index', [
            'orders' => $query->paginate(10)->withQueryString(),
            'customers' => Customer::orderBy('company_name')->get(),
            'projects' => Project::visibleTo($request->user())->orderBy('name')->get(['id', 'name']),
            'summary' => [
                'paid' => (float) Order::where('status', 'paid')->sum('total'),
                'pending' => (float) Order::whereIn('status', ['pending', 'overdue'])->sum('total'),
                'count' => Order::count(),
            ],
            'taskOpenCount' => \App\Models\Task::visibleTo($request->user())->whereIn('status', ['todo', 'in_progress', 'review'])->count(),
            'projectCount' => Project::visibleTo($request->user())->whereIn('status', ['planning', 'in_progress'])->count(),
        ]);
    }

    public function store(StoreOrderRequest $request)
    {
        $data = $request->validated();
        $data['tax'] = $data['tax'] ?? 0;
        $data['total'] = round($data['subtotal'] + $data['tax'], 2);

        $order = Order::create($data);

        Activity::log('created', "Issued order {$order->reference} for {$order->customer?->company_name}", $order, 'receipt');

        return redirect()->route('orders.index')->with('status', "Order {$order->reference} created.");
    }

    public function updateStatus(Request $request, Order $order)
    {
        abort_unless($request->user()->can('orders.manage'), 403);

        $data = $request->validate([
            'status' => ['required', 'in:pending,paid,overdue,refunded,cancelled'],
        ]);

        $order->update($data);

        Activity::log('updated', "Order {$order->reference} marked as {$order->status}", $order, 'badge-check');

        return back()->with('status', "Order {$order->reference} marked as {$order->status}.");
    }
    /** Edit an existing order — managers and finance only (no create/delete). */
    public function update(Request $request, Order $order)
    {
        abort_unless($request->user()->can('orders.manage'), 403);

        $data = $request->validate([
            'subtotal' => ['required', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:pending,paid,overdue,refunded,cancelled'],
        ]);

        $data['tax'] = $data['tax'] ?? 0;
        $data['total'] = round($data['subtotal'] + $data['tax'], 2);

        $order->update($data);

        Activity::log('updated', "Edited order {$order->reference}", $order, 'receipt');

        return back()->with('status', "Order {$order->reference} updated.");
    }
}
