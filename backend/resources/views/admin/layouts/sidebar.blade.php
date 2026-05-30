	<div class="sidebar-wrapper" data-simplebar="true">
			<div class="sidebar-header">
				<div>
					<img src="{{asset('admin/assets/images/logo-icon.png')}}" class="logo-icon" alt="logo icon">
				</div>
				<div>
					<h4 class="logo-text">Rukada</h4>
				</div>
				<div class="toggle-icon ms-auto"><i class='bx bx-arrow-to-left'></i>
				</div>
			</div>
			<!--navigation-->
			<ul class="metismenu" id="menu">
				<!-- MAIN DASHBOARD -->
				<li>
					<a href="{{route('admin.dashboard')}}">
						<div class="parent-icon"><i class='bx bx-home-circle'></i>
						</div>
						<div class="menu-title">Dashboard</div>
					</a>
				</li>

				<!-- ECOMMERCE CORE FEATURES -->
				<li class="menu-label">eCommerce Management</li>

				<!-- Product Management -->
				<li>
					<a href="{{route('admin.products.index')}}">
						<div class="parent-icon"><i class='bx bx-package'></i>
						</div>
						<div class="menu-title">Products</div>
					</a>
				</li>

				<!-- Categories -->
				<li>
					<a href="{{route('admin.categories.index')}}">
						<div class="parent-icon"><i class='bx bx-list-ul'></i>
						</div>
						<div class="menu-title">Categories</div>
					</a>
				</li>

				<!-- Sections Management -->
				<li>
					<a href="{{route('admin.sections.index')}}">
						<div class="parent-icon"><i class='bx bx-grid'></i>
						</div>
						<div class="menu-title">Sections</div>
					</a>
				</li>

				<!-- Sliders -->
				<li>
					<a href="{{route('admin.sliders.index')}}">
						<div class="parent-icon"><i class='bx bx-image'></i>
						</div>
						<div class="menu-title">Sliders</div>
					</a>
				</li>

				<!-- Notifications -->
				<li>
					<a href="{{route('admin.notifications.index')}}">
						<div class="parent-icon"><i class='bx bx-bell'></i>
						</div>
						<div class="menu-title">Notifications</div>
					</a>
				</li>

				<!-- Orders -->
				<li>
					<a href="{{route('admin.orders.index')}}">
						<div class="parent-icon"><i class='bx bx-shopping-bag'></i>
						</div>
						<div class="menu-title">Orders</div>
					</a>
				</li>

				<!-- ADMIN & SYSTEM SETTINGS -->
				<li class="menu-label">System Settings</li>

				<!-- Access Control -->
				<li>
					<a href="javascript:;" class="has-arrow">
						<div class="parent-icon"><i class='bx bx-shield-alt-2'></i>
						</div>
						<div class="menu-title">Access Control</div>
					</a>
					<ul>
						@can('roles.view')
							<li> <a href="{{ route('admin.roles.index') }}"><i class="bx bx-right-arrow-alt"></i>Roles</a></li>
						@endcan
						@can('permissions.view')
							<li> <a href="{{ route('admin.permissions.index') }}"><i class="bx bx-right-arrow-alt"></i>Permissions</a></li>
						@endcan
					</ul>
				</li>

				<!-- Site Settings -->
				{{-- <li>
					<a href="{{route('admin.site.settings')}}">
						<div class="parent-icon"><i class='bx bx-cog'></i>
						</div>
						<div class="menu-title">Site Settings</div>
					</a>
				</li> --}}

				<!-- COMMENTED OUT - NOT NEEDED FOR ECOMMERCE -->
				{{-- <!-- Dashboard Variants - not needed, using main dashboard -->
				<li class="menu-label">Disabled Items</li>
				<li>
					<a href="javascript:;" class="has-arrow" style="opacity: 0.5; pointer-events: none;">
						<div class="parent-icon"><i class='bx bx-home-circle'></i></div>
						<div class="menu-title">Dashboard Variants</div>
					</a>
					<ul style="display:none;">
						<li> <a href="dashboard-eCommerce.html"><i class="bx bx-right-arrow-alt"></i>Analytics</a></li>
						<li> <a href="dashboard-analytics.html"><i class="bx bx-right-arrow-alt"></i>Digital Marketing</a></li>
						<li> <a href="dashboard-digital-marketing.html"><i class="bx bx-right-arrow-alt"></i>HR</a></li>
					</ul>
				</li> -->

				{{-- <!-- UI ELEMENTS - Not needed -->
				<!-- <li class="menu-label">UI Elements</li> -->
				<!-- <li>
					<a href="widgets.html">
						<div class="parent-icon"><i class='bx bx-cookie'></i></div>
						<div class="menu-title">Widgets</div>
					</a>
				</li> -->
				<!-- Components Section - Not needed -->
				<!-- <li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class='bx bx-bookmark-heart'></i></div>
						<div class="menu-title">Components</div>
					</a>
				</li> --> --}}

				{{-- <!-- APPLICATION - Not needed for ecommerce -->
				<!-- <li>
					<a href="javascript:;" class="has-arrow">
						<div class="parent-icon"><i class="bx bx-category"></i></div>
						<div class="menu-title">Application</div>
					</a>
					<ul>
						<li> <a href="app-emailbox.html"><i class="bx bx-right-arrow-alt"></i>Email</a></li>
						<li> <a href="app-chat-box.html"><i class="bx bx-right-arrow-alt"></i>Chat Box</a></li>
						<li> <a href="app-file-manager.html"><i class="bx bx-right-arrow-alt"></i>File Manager</a></li>
						<li> <a href="app-contact-list.html"><i class="bx bx-right-arrow-alt"></i>Contacts</a></li>
						<li> <a href="app-to-do.html"><i class="bx bx-right-arrow-alt"></i>Todo List</a></li>
						<li> <a href="app-invoice.html"><i class="bx bx-right-arrow-alt"></i>Invoice</a></li>
						<li> <a href="app-fullcalender.html"><i class="bx bx-right-arrow-alt"></i>Calendar</a></li>
					</ul>
				</li> --> --}}

				{{-- <!-- FORMS & TABLES - Not needed -->
				<!-- <li class="menu-label">Forms & Tables</li> -->
				<!-- <li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class='bx bx-message-square-edit'></i></div>
						<div class="menu-title">Forms</div>
					</a>
				</li>
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class="bx bx-grid-alt"></i></div>
						<div class="menu-title">Tables</div>
					</a>
				</li> --> --}}

				{{-- <!-- PAGES - Not needed -->
				<!-- <li class="menu-label">Pages</li> -->
				<!-- <li><a href="user-profile.html"><div class="parent-icon"><i class="bx bx-user-circle"></i></div><div class="menu-title">User Profile</div></a></li> -->
				<!-- <li><a href="timeline.html"><div class="parent-icon"><i class="bx bx-video-recording"></i></div><div class="menu-title">Timeline</div></a></li> -->
				<!-- <li><a href="faq.html"><div class="parent-icon"><i class="bx bx-help-circle"></i></div><div class="menu-title">FAQ</div></a></li> --> --}}

				{{-- <!-- CHARTS & MAPS - Not needed for basic ecommerce -->
				<!-- <li class="menu-label">Charts & Maps</li> -->
				<!-- <li><a href="javascript:;"><div class="parent-icon"><i class="bx bx-line-chart"></i></div><div class="menu-title">Charts</div></a></li> -->
				<!-- <li><a href="javascript:;"><div class="parent-icon"><i class="bx bx-map-alt"></i></div><div class="menu-title">Maps</div></a></li> --> --}}
			</ul>
			<!--end navigation-->
		</div>
