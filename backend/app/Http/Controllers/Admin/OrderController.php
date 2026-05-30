<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\Admin\OrderRepositoryInterface;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * Constructor
     *
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Display a listing of the orders.
     *
     * @return \Illuminate\View\View
     */
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

    /**
     * Show the form for creating a new order.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.orders.create');
    }

    /**
     * Store a newly created order in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'order_number' => 'required|unique:orders',
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'total_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'shipping_address' => 'required|string',
            'billing_address' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $this->orderRepository->update(0, $validated);

        return redirect()->route('admin.orders.index')
            ->with('success', 'Order created successfully.');
    }

    /**
     * Display the specified order.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $order = $this->orderRepository->getWithDetails($id);
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified order.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $order = $this->orderRepository->find($id);
        return view('admin.orders.edit', compact('order'));
    }

    /**
     * Update the specified order in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'notes' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'billing_address' => 'nullable|string',
        ]);

        $this->orderRepository->update($id, $validated);

        return redirect()->route('admin.orders.show', $id)
            ->with('success', 'Order updated successfully.');
    }

    /**
     * Remove the specified order from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $order = $this->orderRepository->find($id);
        $order->delete();

        return redirect()->route('admin.orders.index')
            ->with('success', 'Order deleted successfully.');
    }

    /**
     * Update order status
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $this->orderRepository->updateStatus($id, $validated['status']);

        return redirect()->back()
            ->with('success', 'Order status updated successfully.');
    }

    /**
     * Display order tracking
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function tracking($id)
    {
        $order = $this->orderRepository->getWithDetails($id);

        // Determine tracking timeline based on status
        $timeline = $this->buildOrderTimeline($order);

        return view('admin.orders.tracking', compact('order', 'timeline'));
    }

    /**
     * Build order tracking timeline
     *
     * @param $order
     * @return array
     */
    private function buildOrderTimeline($order)
    {
        $statuses = [
            'pending' => [
                'label' => 'Order Placed',
                'icon' => 'check',
                'completed' => true,
                'date' => $order->created_at,
            ],
            'processing' => [
                'label' => 'Processing',
                'icon' => 'cog',
                'completed' => in_array($order->status, ['processing', 'shipped', 'delivered']),
                'date' => null,
            ],
            'shipped' => [
                'label' => 'Shipped',
                'icon' => 'truck',
                'completed' => in_array($order->status, ['shipped', 'delivered']),
                'date' => $order->shipped_at,
            ],
            'delivered' => [
                'label' => 'Delivered',
                'icon' => 'home',
                'completed' => $order->status === 'delivered',
                'date' => $order->delivered_at,
            ],
        ];

        if ($order->status === 'cancelled') {
            $statuses['cancelled'] = [
                'label' => 'Cancelled',
                'icon' => 'times',
                'completed' => true,
                'date' => $order->updated_at,
            ];
        }

        return $statuses;
    }
}
