<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Sales\Actions\UpdateOrderStatusAction;
use Modules\Sales\Http\Requests\UpdateOrderRequest;
use Modules\Sales\Models\Order;
use Modules\Sales\Repositories\Contracts\OrderRepositoryInterface;
use Modules\Sales\Services\OrderTimelineService;

class OrderController extends Controller
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private OrderTimelineService $timelineService
    ) {}

    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        if ($search) {
            $orders = $this->orderRepository->search($search);
        } elseif ($status) {
            $orders = $this->orderRepository->getByStatus($status);
        } else {
            $orders = $this->orderRepository->getAll();
        }

        $stats = $this->orderRepository->getStatistics();

        return view('admin.orders.index', compact('orders', 'stats', 'status', 'search'));
    }

    public function create()
    {
        return view('admin.orders.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'          => 'required|exists:users,id',
            'order_number'     => 'required|unique:orders',
            'status'           => 'required|in:pending,processing,shipped,delivered,cancelled',
            'total_amount'     => 'required|numeric|min:0',
            'payment_method'   => 'required|string',
            'payment_status'   => 'required|in:pending,paid,failed,refunded',
            'shipping_address' => 'required|string',
            'billing_address'  => 'required|string',
            'notes'            => 'nullable|string',
        ]);

        Order::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'   => 'success',
                'message'  => 'Order created successfully.',
                'redirect' => route('admin.orders.index'),
            ], 201);
        }

        return redirect()->route('admin.orders.index')
            ->with('success', 'Order created successfully.');
    }

    public function show($id)
    {
        $order = $this->orderRepository->getWithDetails($id);
        return view('admin.orders.show', compact('order'));
    }

    public function edit($id)
    {
        $order = $this->orderRepository->find($id);
        return view('admin.orders.edit', compact('order'));
    }

    public function update(UpdateOrderRequest $request, $id)
    {
        $this->orderRepository->update($id, $request->validated());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'   => 'success',
                'message'  => 'Order updated successfully.',
                'redirect' => route('admin.orders.show', $id),
            ]);
        }

        return redirect()->route('admin.orders.show', $id)
            ->with('success', 'Order updated successfully.');
    }

    public function invoice($id)
    {
        $order = Order::with(['user', 'items.product.category'])->findOrFail($id);
        return view('admin.orders.invoice', compact('order'));
    }

    public function destroy($id)
    {
        $order = $this->orderRepository->find($id);
        $order->delete();

        return redirect()->route('admin.orders.index')
            ->with('success', 'Order deleted successfully.');
    }

    public function updateStatus(Request $request, $id, UpdateOrderStatusAction $action)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $action->execute((int) $id, $validated['status']);

        return redirect()->back()
            ->with('success', 'Order status updated successfully.');
    }

    public function tracking($id)
    {
        $order    = $this->orderRepository->getWithDetails($id);
        $timeline = $this->timelineService->build($order);

        return view('admin.orders.tracking', compact('order', 'timeline'));
    }
}
