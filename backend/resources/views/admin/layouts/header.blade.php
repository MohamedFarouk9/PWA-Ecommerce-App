@php
    $authUser = auth()->user();
    $userName = $authUser->name ?? 'Administrator';
    $userRole = $authUser ? ucwords(str_replace('_', ' ', $authUser->getRoleNames()->first() ?? ($authUser->role ?? 'Admin'))) : 'Admin';
    $initials = collect(explode(' ', $userName))->filter()->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode('');
    $pendingOrdersCount = \App\Models\Order::where('status', 'pending')->count();
    $recentContactsCount = \App\Models\Contact::whereDate('created_at', '>=', now()->subDays(7))->count();
    $latestOrdersHeader = \App\Models\Order::with('user:id,name')->latest()->limit(5)->get();
@endphp

<header>
    <div class="topbar d-flex align-items-center">
        <nav class="navbar navbar-expand w-100">
            <div class="mobile-toggle-menu">
                <i class='bx bx-menu'></i>
            </div>

            <!-- Live Global Admin Search -->
            <div class="search-bar flex-grow-1">
                <form action="{{ route('admin.search') }}" method="GET" class="position-relative search-bar-box mb-0">
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        class="form-control search-control"
                        placeholder="Search products, orders (#ORD-...), or customers..."
                        autocomplete="off"
                    >
                    <span class="position-absolute top-50 search-show translate-middle-y">
                        <i class='bx bx-search'></i>
                    </span>
                    <span class="position-absolute top-50 search-close translate-middle-y">
                        <i class='bx bx-x'></i>
                    </span>
                </form>
            </div>

            <div class="top-menu ms-auto">
                <ul class="navbar-nav align-items-center gap-1">
                    <!-- Mobile Search Icon -->
                    <li class="nav-item mobile-search-icon">
                        <a class="nav-link adm-icon-btn" href="#"><i class='bx bx-search'></i></a>
                    </li>

                    <!-- Dark / Light Mode Toggle -->
                    <li class="nav-item">
                        <button type="button" id="adminThemeToggle" class="adm-icon-btn" title="Toggle Dark / Light Mode">
                            <i id="adminThemeIcon" class='bx bx-moon fs-5'></i>
                        </button>
                    </li>

                    <!-- Quick Actions Launcher -->
                    <li class="nav-item dropdown dropdown-large">
                        <a class="nav-link adm-icon-btn dropdown-toggle dropdown-toggle-nocaret" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Quick Actions">
                            <i class='bx bx-grid-alt fs-5'></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="min-width: 280px; border-radius: 14px;">
                            <div class="px-3 py-2 border-bottom">
                                <span class="fw-bold small text-uppercase text-muted">Quick Actions</span>
                            </div>
                            <div class="row row-cols-3 g-3 p-3">
                                <div class="col text-center">
                                    <a href="{{ route('admin.products.create') }}" class="text-decoration-none text-reset">
                                        <div class="app-box mx-auto bg-gradient-cosmic text-white"><i class='bx bx-plus-circle'></i></div>
                                        <div class="app-title small mt-1">New Product</div>
                                    </a>
                                </div>
                                <div class="col text-center">
                                    <a href="{{ route('admin.orders.index') }}" class="text-decoration-none text-reset">
                                        <div class="app-box mx-auto bg-gradient-burning text-white"><i class='bx bx-shopping-bag'></i></div>
                                        <div class="app-title small mt-1">Orders</div>
                                    </a>
                                </div>
                                <div class="col text-center">
                                    <a href="{{ route('admin.categories.index') }}" class="text-decoration-none text-reset">
                                        <div class="app-box mx-auto bg-gradient-lush text-white"><i class='bx bx-category'></i></div>
                                        <div class="app-title small mt-1">Categories</div>
                                    </a>
                                </div>
                                <div class="col text-center">
                                    <a href="{{ route('admin.users.index') }}" class="text-decoration-none text-reset">
                                        <div class="app-box mx-auto bg-gradient-kyoto text-dark"><i class='bx bx-group'></i></div>
                                        <div class="app-title small mt-1">Customers</div>
                                    </a>
                                </div>
                                <div class="col text-center">
                                    <a href="{{ route('admin.coupons.index') }}" class="text-decoration-none text-reset">
                                        <div class="app-box mx-auto bg-gradient-blues text-dark"><i class='bx bx-purchase-tag'></i></div>
                                        <div class="app-title small mt-1">Coupons</div>
                                    </a>
                                </div>
                                <div class="col text-center">
                                    <a href="{{ route('admin.reviews.index') }}" class="text-decoration-none text-reset">
                                        <div class="app-box mx-auto bg-gradient-moonlit text-white"><i class='bx bx-star'></i></div>
                                        <div class="app-title small mt-1">Reviews</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>

                    <!-- Real Order & Activity Notifications -->
                    <li class="nav-item dropdown dropdown-large">
                        <a class="nav-link adm-icon-btn dropdown-toggle dropdown-toggle-nocaret position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Recent Activity">
                            @if($pendingOrdersCount > 0)
                                <span class="alert-count">{{ $pendingOrdersCount > 99 ? '99+' : $pendingOrdersCount }}</span>
                            @endif
                            <i class='bx bx-bell fs-5'></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="border-radius: 14px;">
                            <div class="msg-header d-flex align-items-center">
                                <p class="msg-header-title mb-0">Recent Orders</p>
                                <span class="badge bg-primary bg-opacity-10 text-primary ms-auto">{{ $pendingOrdersCount }} pending</span>
                            </div>
                            <div class="header-notifications-list">
                                @forelse($latestOrdersHeader as $hdrOrder)
                                    <a class="dropdown-item" href="{{ route('admin.orders.show', $hdrOrder->id) }}">
                                        <div class="d-flex align-items-center">
                                            <div class="notify bg-light-primary text-primary">
                                                <i class="bx bx-cart-alt"></i>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <h6 class="msg-name text-truncate">
                                                    {{ $hdrOrder->order_number ?? 'ORD-' . $hdrOrder->id }}
                                                    <span class="msg-time float-end">{{ $hdrOrder->created_at?->diffForHumans(null, true) }}</span>
                                                </h6>
                                                <p class="msg-info text-truncate">
                                                    {{ $hdrOrder->user->name ?? 'Guest' }} &bull; ${{ number_format((float) $hdrOrder->total_amount, 2) }} ({{ ucfirst($hdrOrder->status) }})
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="text-center py-4 text-muted small">No recent orders yet.</div>
                                @endforelse
                            </div>
                            <a href="{{ route('admin.orders.index') }}">
                                <div class="text-center msg-footer">View All Orders</div>
                            </a>
                        </div>
                    </li>

                    <!-- Contact Messages Shortcut -->
                    <li class="nav-item">
                        <a class="nav-link adm-icon-btn position-relative" href="{{ route('admin.contacts.index') }}" title="Contact Messages">
                            @if($recentContactsCount > 0)
                                <span class="alert-count" style="background: #0ea5e9;">{{ $recentContactsCount }}</span>
                            @endif
                            <i class='bx bx-envelope fs-5'></i>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Authenticated Admin User Dropdown -->
            <div class="user-box dropdown">
                <a class="d-flex align-items-center nav-link dropdown-toggle dropdown-toggle-nocaret" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="adm-avatar-circle">{{ $initials ?: 'AD' }}</span>
                    <div class="user-info ps-2 d-none d-sm-block">
                        <p class="user-name mb-0">{{ $userName }}</p>
                        <p class="designattion mb-0">{{ $userRole }}</p>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="border-radius: 12px;">
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                            <i class='bx bx-home-circle me-2'></i><span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.profile') }}">
                            <i class="bx bx-user me-2"></i><span>My Profile</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.password.change') }}">
                            <i class="bx bx-lock-alt me-2"></i><span>Change Password</span>
                        </a>
                    </li>
                    <li><div class="dropdown-divider my-1"></div></li>
                    <li>
                        <a class="dropdown-item text-danger" href="{{ route('admin.logout') }}">
                            <i class="bx bx-log-out-circle me-2"></i><span>Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</header>
