@extends('admin.layouts.app')
@section('title', 'Customer Profile — ' . $user->name)

@section('content')
<div class="page-wrapper">
    <div class="page-content">

        {{-- Breadcrumb --}}
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Customers</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Customers</a></li>
                        <li class="breadcrumb-item active">{{ $user->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary btn-sm">
                    <i class="bx bx-edit me-1"></i>Edit Customer
                </a>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bx bx-arrow-back me-1"></i>Back
                </a>
            </div>
        </div>

        @include('admin.components.flash-messages')

        <div class="row">
            {{-- Left: Profile Card --}}
            <div class="col-12 col-xl-4 mb-4">
                <div class="card radius-10 text-center">
                    <div class="card-body py-4">
                        <img src="{{ $user->image }}" alt="{{ $user->name }}"
                            class="rounded-circle mb-3" width="100" height="100" style="object-fit:cover; border:3px solid #dee2e6;">
                        <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                        <p class="text-muted mb-2">{{ $user->email }}</p>
                        <div class="mb-3 d-flex justify-content-center gap-1">
                            @if($user->role === 'admin')
                                <span class="badge bg-danger">Administrator</span>
                            @else
                                <span class="badge bg-primary">Customer</span>
                            @endif

                            @if($user->status === 'banned')
                                <span class="badge bg-danger">Banned</span>
                            @elseif($user->status === 'inactive')
                                <span class="badge bg-secondary">Inactive</span>
                            @else
                                <span class="badge bg-success">Active</span>
                            @endif
                        </div>

                        @if($user->id !== auth()->id())
                            <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" class="mb-3">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $user->status === 'banned' ? 'btn-success' : 'btn-outline-warning' }}">
                                    <i class="bx {{ $user->status === 'banned' ? 'bx-check-circle' : 'bx-block' }} me-1"></i>
                                    {{ $user->status === 'banned' ? 'Activate Account' : 'Ban Account' }}
                                </button>
                            </form>
                        @endif

                        <hr>
                        <div class="row text-center g-0">
                            <div class="col border-end">
                                <div class="fs-5 fw-bold text-primary">{{ $orderCount }}</div>
                                <div class="text-muted small">Orders</div>
                            </div>
                            <div class="col border-end">
                                <div class="fs-5 fw-bold text-success">${{ number_format($totalSpent, 2) }}</div>
                                <div class="text-muted small">Total Spent</div>
                            </div>
                            <div class="col">
                                <div class="fs-5 fw-bold text-warning">{{ $reviewCount }}</div>
                                <div class="text-muted small">Reviews</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Contact Info --}}
                <div class="card radius-10 mt-3">
                    <div class="card-header bg-transparent fw-bold"><i class="bx bx-info-circle me-2"></i>Contact Info</div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted">Phone</td>
                                    <td>{{ $user->phone ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">City</td>
                                    <td>{{ $user->city ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">State</td>
                                    <td>{{ $user->state ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Country</td>
                                    <td>{{ $user->country ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Postal Code</td>
                                    <td>{{ $user->postal_code ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Joined</td>
                                    <td>{{ $user->created_at->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Last Updated</td>
                                    <td>{{ $user->updated_at->diffForHumans() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Right: Orders + Reviews --}}
            <div class="col-12 col-xl-8">

                {{-- Order Summary KPIs --}}
                <div class="row row-cols-1 row-cols-md-3 mb-4">
                    <div class="col">
                        <div class="card radius-10 border-start border-info border-4">
                            <div class="card-body">
                                <div class="text-muted small">Avg. Order Value</div>
                                <div class="fs-5 fw-bold">${{ $orderCount > 0 ? number_format($totalSpent / $orderCount, 2) : '0.00' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10 border-start border-success border-4">
                            <div class="card-body">
                                <div class="text-muted small">Completed Orders</div>
                                <div class="fs-5 fw-bold">{{ $completedOrders }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10 border-start border-warning border-4">
                            <div class="card-body">
                                <div class="text-muted small">Pending Orders</div>
                                <div class="fs-5 fw-bold">{{ $pendingOrders }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recent Orders --}}
                <div class="card radius-10 mb-4">
                    <div class="card-header bg-transparent d-flex align-items-center">
                        <strong><i class="bx bx-shopping-bag me-2"></i>Order History</strong>
                        <span class="badge bg-primary ms-2">{{ $orderCount }} total</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                <tr>
                                    <td><strong>#{{ $order->order_number ?? 'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</strong></td>
                                    <td>{{ $order->created_at->format('d M Y') }}</td>
                                    <td>{{ $order->items_count ?? '—' }}</td>
                                    <td><strong>${{ number_format($order->total_amount, 2) }}</strong></td>
                                    <td>
                                        @php
                                            $statusColors = ['pending'=>'warning','processing'=>'info','shipped'=>'primary','delivered'=>'success','cancelled'=>'danger'];
                                            $sc = $statusColors[$order->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $sc }}">{{ ucfirst($order->status) }}</span>
                                    </td>
                                    <td>
                                        @if($order->payment_status === 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($order->payment_status === 'refunded')
                                            <span class="badge bg-warning text-dark">Refunded</span>
                                        @else
                                            <span class="badge bg-secondary">Unpaid</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-show"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No orders yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Recent Reviews --}}
                @if($reviews->count())
                <div class="card radius-10">
                    <div class="card-header bg-transparent d-flex align-items-center">
                        <strong><i class="bx bx-star me-2"></i>Product Reviews</strong>
                        <span class="badge bg-warning text-dark ms-2">{{ $reviewCount }} total</span>
                    </div>
                    <div class="card-body p-0">
                        @foreach($reviews as $review)
                        <div class="d-flex gap-3 p-3 border-bottom">
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $review->product->name ?? 'Unknown Product' }}</div>
                                <div class="mb-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bx bxs-star {{ $i <= $review->rating ? 'text-warning' : 'text-secondary opacity-25' }}"></i>
                                    @endfor
                                    <span class="ms-1 small text-muted">{{ $review->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="mb-0 text-muted small">{{ $review->comment }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
