<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center text-decoration-none">
            <span class="adm-brand-badge">
                <i class='bx bx-store-alt'></i>
            </span>
            <h4 class="logo-text mb-0">ShopPulse</h4>
        </a>
        <div class="toggle-icon ms-auto">
            <i class='bx bx-arrow-to-left'></i>
        </div>
    </div>

    <!-- Navigation -->
    <ul class="metismenu" id="menu">
        <!-- Overview -->
        <li class="menu-label">Overview</li>
        <li class="{{ request()->routeIs('admin.dashboard') ? 'mm-active' : '' }}">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <div class="parent-icon"><i class='bx bx-home-circle'></i></div>
                <div class="menu-title">Dashboard</div>
            </a>
        </li>

        <!-- Catalog & Storefront -->
        <li class="menu-label">Catalog &amp; Storefront</li>
        <li class="{{ request()->routeIs('admin.products.*') ? 'mm-active' : '' }}">
            <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                <div class="parent-icon"><i class='bx bx-package'></i></div>
                <div class="menu-title">Products</div>
            </a>
        </li>
        <li class="{{ request()->routeIs('admin.categories.*') ? 'mm-active' : '' }}">
            <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <div class="parent-icon"><i class='bx bx-category'></i></div>
                <div class="menu-title">Categories</div>
            </a>
        </li>
        <li class="{{ request()->routeIs('admin.sections.*') ? 'mm-active' : '' }}">
            <a href="{{ route('admin.sections.index') }}" class="{{ request()->routeIs('admin.sections.*') ? 'active' : '' }}">
                <div class="parent-icon"><i class='bx bx-grid-alt'></i></div>
                <div class="menu-title">Homepage Sections</div>
            </a>
        </li>
        <li class="{{ request()->routeIs('admin.sliders.*') ? 'mm-active' : '' }}">
            <a href="{{ route('admin.sliders.index') }}" class="{{ request()->routeIs('admin.sliders.*') ? 'active' : '' }}">
                <div class="parent-icon"><i class='bx bx-images'></i></div>
                <div class="menu-title">Hero Sliders</div>
            </a>
        </li>

        <!-- Sales & Customers -->
        <li class="menu-label">Sales &amp; Customers</li>
        <li class="{{ request()->routeIs('admin.orders.*') ? 'mm-active' : '' }}">
            <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <div class="parent-icon"><i class='bx bx-shopping-bag'></i></div>
                <div class="menu-title">Orders</div>
            </a>
        </li>
        <li class="{{ request()->routeIs('admin.users.*') ? 'mm-active' : '' }}">
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <div class="parent-icon"><i class='bx bx-group'></i></div>
                <div class="menu-title">Customers</div>
            </a>
        </li>
        <li class="{{ request()->routeIs('admin.coupons.*') ? 'mm-active' : '' }}">
            <a href="{{ route('admin.coupons.index') }}" class="{{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
                <div class="parent-icon"><i class='bx bx-purchase-tag-alt'></i></div>
                <div class="menu-title">Coupons &amp; Discounts</div>
            </a>
        </li>
        <li class="{{ request()->routeIs('admin.reviews.*') ? 'mm-active' : '' }}">
            <a href="{{ route('admin.reviews.index') }}" class="{{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                <div class="parent-icon"><i class='bx bx-star'></i></div>
                <div class="menu-title">Product Reviews</div>
            </a>
        </li>
        <li class="{{ request()->routeIs('admin.contacts.*') ? 'mm-active' : '' }}">
            <a href="{{ route('admin.contacts.index') }}" class="{{ request()->routeIs('admin.contacts.*') ? 'active' : '' }}">
                <div class="parent-icon"><i class='bx bx-envelope'></i></div>
                <div class="menu-title">Contact Messages</div>
            </a>
        </li>
        <li class="{{ request()->routeIs('admin.notifications.*') ? 'mm-active' : '' }}">
            <a href="{{ route('admin.notifications.index') }}" class="{{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                <div class="parent-icon"><i class='bx bx-bell'></i></div>
                <div class="menu-title">Notifications</div>
            </a>
        </li>

        <!-- System & Security -->
        <li class="menu-label">System &amp; Security</li>
        <li class="{{ request()->routeIs('admin.roles.*', 'admin.permissions.*') ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-shield-alt-2'></i></div>
                <div class="menu-title">Access Control</div>
            </a>
            <ul>
                @can('roles.view')
                    <li>
                        <a href="{{ route('admin.roles.index') }}" class="{{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                            <i class="bx bx-right-arrow-alt"></i>Roles
                        </a>
                    </li>
                @endcan
                @can('permissions.view')
                    <li>
                        <a href="{{ route('admin.permissions.index') }}" class="{{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                            <i class="bx bx-right-arrow-alt"></i>Permissions
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    </ul>
</div>
