@extends('admin.layouts.app')
@section('title', 'Dashboard')

@section('content') 
    <div class="wrapper">
        <!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">

                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
                    <div class="col">
                        <div class="card radius-10 bg-gradient-deepblue">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <h5 class="mb-0 text-white">{{ number_format($stats['total_orders'] ?? 0) }}</h5>
                                    <div class="ms-auto">
                                        <i class='bx bx-cart fs-3 text-white'></i>
                                    </div>
                                </div>
                                <div class="progress my-3 bg-light-transparent" style="height:3px;">
                                    <div class="progress-bar bg-white" role="progressbar" style="width: 75%"
                                        aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex align-items-center text-white">
                                    <p class="mb-0">Total Orders</p>
                                    <p class="mb-0 ms-auto">{{ ($stats['orders_change_type'] ?? 'increase') === 'increase' ? '+' : '-' }}{{ $stats['orders_change'] ?? 0 }}%<span><i class='bx bx-{{ ($stats['orders_change_type'] ?? 'increase') === 'increase' ? 'up' : 'down' }}-arrow-alt'></i></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10 bg-gradient-orange">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <h5 class="mb-0 text-white">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</h5>
                                    <div class="ms-auto">
                                        <i class='bx bx-dollar fs-3 text-white'></i>
                                    </div>
                                </div>
                                <div class="progress my-3 bg-light-transparent" style="height:3px;">
                                    <div class="progress-bar bg-white" role="progressbar" style="width: 80%"
                                        aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex align-items-center text-white">
                                    <p class="mb-0">Total Revenue</p>
                                    <p class="mb-0 ms-auto">{{ ($stats['revenue_change_type'] ?? 'increase') === 'increase' ? '+' : '-' }}{{ $stats['revenue_change'] ?? 0 }}%<span><i class='bx bx-{{ ($stats['revenue_change_type'] ?? 'increase') === 'increase' ? 'up' : 'down' }}-arrow-alt'></i></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10 bg-gradient-ohhappiness">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <h5 class="mb-0 text-white">{{ number_format($stats['total_customers'] ?? 0) }}</h5>
                                    <div class="ms-auto">
                                        <i class='bx bx-group fs-3 text-white'></i>
                                    </div>
                                </div>
                                <div class="progress my-3 bg-light-transparent" style="height:3px;">
                                    <div class="progress-bar bg-white" role="progressbar" style="width: 65%"
                                        aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex align-items-center text-white">
                                    <p class="mb-0">Total Customers</p>
                                    <p class="mb-0 ms-auto">{{ ($stats['customers_change_type'] ?? 'increase') === 'increase' ? '+' : '-' }}{{ $stats['customers_change'] ?? 0 }}%<span><i class='bx bx-{{ ($stats['customers_change_type'] ?? 'increase') === 'increase' ? 'up' : 'down' }}-arrow-alt'></i></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10 bg-gradient-ibiza">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <h5 class="mb-0 text-white">{{ number_format($stats['total_products'] ?? 0) }}</h5>
                                    <div class="ms-auto">
                                        <i class='bx bx-package fs-3 text-white'></i>
                                    </div>
                                </div>
                                <div class="progress my-3 bg-light-transparent" style="height:3px;">
                                    <div class="progress-bar bg-white" role="progressbar" style="width: 90%"
                                        aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex align-items-center text-white">
                                    <p class="mb-0">Active Products</p>
                                    <p class="mb-0 ms-auto">{{ number_format($stats['active_products'] ?? 0) }} published</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--end row-->

                <div class="row">
                    <div class="col-12 col-lg-8 col-xl-8 d-flex">
                        <div class="card radius-10 w-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h6 class="mb-0">Site Traffic</h6>
                                    </div>
                                    <div class="font-22 ms-auto"><i class="bx bx-dots-horizontal-rounded"></i>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center ms-auto font-13 gap-2 my-3">
                                    <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1"
                                            style="color: #14abef"></i>New Visitor</span>
                                    <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1"
                                            style="color: #ade2f9"></i>Old Visitor</span>
                                </div>
                                <div class="chart-container-1">
                                    <canvas id="chart1"></canvas>
                                </div>
                            </div>
                            <div class="row row-cols-1 row-cols-md-3 row-cols-xl-3 g-0 row-group text-center border-top">
                                <div class="col">
                                    <div class="p-3">
                                        <h5 class="mb-0">45.87M</h5>
                                        <small class="mb-0">Overall Visitor <span> <i
                                                    class="bx bx-up-arrow-alt align-middle"></i> 2.43%</span></small>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-3">
                                        <h5 class="mb-0">15:48</h5>
                                        <small class="mb-0">Visitor Duration <span> <i
                                                    class="bx bx-up-arrow-alt align-middle"></i> 12.65%</span></small>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-3">
                                        <h5 class="mb-0">245.65</h5>
                                        <small class="mb-0">Pages/Visit <span> <i
                                                    class="bx bx-up-arrow-alt align-middle"></i> 5.62%</span></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4 col-xl-4 d-flex">
                        <div class="card radius-10 overflow-hidden w-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h6 class="mb-0">Weekly sales</h6>
                                    </div>
                                    <div class="font-22 ms-auto text-white"><i class="bx bx-dots-horizontal-rounded"></i>
                                    </div>
                                </div>
                                <div class="chart-container-2 my-3">
                                    <canvas id="chart2"></canvas>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0">
                                    <tbody>
                                        <tr>
                                            <td><i class="bx bxs-circle me-2" style="color: #14abef"></i> Direct</td>
                                            <td>$5856</td>
                                            <td>+55%</td>
                                        </tr>
                                        <tr>
                                            <td><i class="bx bxs-circle me-2" style="color: #02ba5a"></i>Affiliate
                                            </td>
                                            <td>$2602</td>
                                            <td>+25%</td>
                                        </tr>
                                        <tr>
                                            <td><i class="bx bxs-circle me-2" style="color: #d13adf"></i>E-mail</td>
                                            <td>$1802</td>
                                            <td>+15%</td>
                                        </tr>
                                        <tr>
                                            <td><i class="bx bxs-circle me-2" style="color: #fba540"></i>Other</td>
                                            <td>$1105</td>
                                            <td>+5%</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div><!--End Row-->


                <div class="row row-cols-1 row-cols-lg-3">
                    <div class="col">
                        <div class="card radius-10">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="w_chart easy-dash-chart1" data-percent="60">
                                        <span class="w_percent"></span>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-0">Facebook Followers</h6>
                                        <small class="mb-0">22.14% <i
                                                class='bx bxs-up-arrow align-middle me-1'></i>Since Last Week</small>
                                    </div>
                                    <div class="ms-auto fs-1 text-facebook"><i class='bx bxl-facebook'></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="w_chart easy-dash-chart2" data-percent="65">
                                        <span class="w_percent"></span>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-0">Twitter Tweets</h6>
                                        <small class="mb-0">32.15% <i
                                                class='bx bxs-up-arrow align-middle me-1'></i>Since Last Week</small>
                                    </div>
                                    <div class="ms-auto fs-1 text-twitter"><i class='bx bxl-twitter'></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="w_chart easy-dash-chart3" data-percent="75">
                                        <span class="w_percent"></span>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-0">Youtube Subscribers</h6>
                                        <small class="mb-0">58.24% <i
                                                class='bx bxs-up-arrow align-middle me-1'></i>Since Last Week</small>
                                    </div>
                                    <div class="ms-auto fs-1 text-youtube"><i class='bx bxl-youtube'></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--End Row-->


                <div class="row">
                    <div class="col-12 col-lg-12 col-xl-6">
                        <div class="card radius-10">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div>
                                        <h6 class="mb-0">World Selling Region</h6>
                                    </div>
                                    <div class="font-22 ms-auto text-white"><i class="bx bx-dots-horizontal-rounded"></i>
                                    </div>
                                </div>
                                <div id="dashboard-map" style="height: 330px;"></div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-items-center">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Country</th>
                                            <th>Income</th>
                                            <th>Trend</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><i class="flag-icon flag-icon-ca me-2"></i> USA</td>
                                            <td>$4,586</td>
                                            <td><span id="trendchart1"></span></td>
                                        </tr>
                                        <tr>
                                            <td><i class="flag-icon flag-icon-us me-2"></i>Canada</td>
                                            <td>$2,089</td>
                                            <td><span id="trendchart2"></span></td>
                                        </tr>

                                        <tr>
                                            <td><i class="flag-icon flag-icon-in me-2"></i>India</td>
                                            <td>$3,039</td>
                                            <td><span id="trendchart3"></span></td>
                                        </tr>

                                        <tr>
                                            <td><i class="flag-icon flag-icon-gb me-2"></i>UK</td>
                                            <td>$2,309</td>
                                            <td><span id="trendchart4"></span></td>
                                        </tr>

                                        <tr>
                                            <td><i class="flag-icon flag-icon-de me-2"></i>Germany</td>
                                            <td>$7,209</td>
                                            <td><span id="trendchart5"></span></td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-12 col-xl-6">
                        <div class="row">
                            <div class="col-12 col-lg-6">
                                <div class="card radius-10 overflow-hidden">
                                    <div class="card-body">
                                        <p>Page Views</p>
                                        <h4 class="mb-0">8,293 <small class="font-13">5.2% <i
                                                    class="zmdi zmdi-long-arrow-up"></i></small></h4>
                                    </div>
                                    <div class="chart-container-2">
                                        <canvas id="chart3"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <div class="card radius-10 overflow-hidden">
                                    <div class="card-body">
                                        <p>Total Clicks</p>
                                        <h4 class="mb-0">7,493 <small class="font-13">1.4% <i
                                                    class="zmdi zmdi-long-arrow-up"></i></small></h4>
                                    </div>
                                    <div class="chart-container-2">
                                        <canvas id="chart4"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <div class="card radius-10">
                                    <div class="card-body text-center">
                                        <p class="mb-4">Total Downloads</p>
                                        <input class="knob" data-width="190" data-height="190" data-readOnly="true"
                                            data-thickness=".2" data-angleoffset="90" data-linecap="round"
                                            data-bgcolor="rgba(0, 0, 0, 0.08)" data-fgcolor="#843cf7" data-max="15000"
                                            value="8550" />
                                        <hr>
                                        <p class="mb-0 small-font text-center">3.4% <i
                                                class="zmdi zmdi-long-arrow-up"></i> since yesterday</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <div class="card radius-10">
                                    <div class="card-body">
                                        <p>Device Storage</p>
                                        <h4 class="mb-3">42620/50000</h4>
                                        <hr>
                                        <div class="progress-wrapper mb-4">
                                            <p>Documents <span class="float-right">12GB</span></p>
                                            <div class="progress" style="height:5px;">
                                                <div class="progress-bar bg-success" style="width:80%"></div>
                                            </div>
                                        </div>

                                        <div class="progress-wrapper mb-4">
                                            <p>Images <span class="float-right">10GB</span></p>
                                            <div class="progress" style="height:5px;">
                                                <div class="progress-bar bg-danger" style="width:60%"></div>
                                            </div>
                                        </div>

                                        <div class="progress-wrapper mb-4">
                                            <p>Mails <span class="float-right">5GB</span></p>
                                            <div class="progress" style="height:5px;">
                                                <div class="progress-bar bg-primary" style="width:40%"></div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--End Row-->

                <div class="row">
                    <div class="col-12 col-lg-6 col-xl-4 d-flex">
                        <div class="card radius-10 overflow-hidden w-100">
                            <div class="card-body">
                                <p>Total Earning</p>
                                <h4 class="mb-0">$287,493</h4>
                                <small>1.4% <i class="zmdi zmdi-long-arrow-up"></i> Since Last Month</small>
                                <hr>
                                <p>Total Sales</p>
                                <h4 class="mb-0">$87,493</h4>
                                <small>5.43% <i class="zmdi zmdi-long-arrow-up"></i> Since Last Month</small>
                                <div class="mt-5">
                                    <div class="chart-container-4">
                                        <canvas id="chart5"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6 col-xl-8 d-flex">
                        <div class="card radius-10 w-100">
                            <div class="card-header border-bottom bg-transparent">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h6 class="mb-0">Customer Review</h6>
                                    </div>
                                    <div class="font-22 ms-auto"><i class="bx bx-dots-horizontal-rounded"></i>
                                    </div>
                                </div>
                            </div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset('admin/assets/images/avatars/avatar-1.png') }}"
                                            alt="user avatar" class="rounded-circle" width="55" height="55">
                                        <div class="ms-3">
                                            <h6 class="mb-0">iPhone X <small class="ms-4">08.34 AM</small></h6>
                                            <p class="mb-0 small-font">Sara Jhon : This is svery Nice phone in low
                                                budget.</p>
                                        </div>
                                        <div class="ms-auto star">
                                            <i class='bx bxs-star text-warning'></i>
                                            <i class='bx bxs-star text-warning'></i>
                                            <i class='bx bxs-star text-warning'></i>
                                            <i class='bx bxs-star text-light-4'></i>
                                            <i class='bx bxs-star text-light-4'></i>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item bg-transparent">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset('admin/assets/images/avatars/avatar-2.png') }}"
                                            alt="user avatar" class="rounded-circle" width="55" height="55">
                                        <div class="ms-3">
                                            <h6 class="mb-0">Air Pod <small class="ml-4">05.26 PM</small></h6>
                                            <p class="mb-0 small-font">Danish Josh : The brand apple is original !</p>
                                        </div>
                                        <div class="ms-auto star">
                                            <i class='bx bxs-star text-warning'></i>
                                            <i class='bx bxs-star text-warning'></i>
                                            <i class='bx bxs-star text-warning'></i>
                                            <i class='bx bxs-star text-light-4'></i>
                                            <i class='bx bxs-star text-light-4'></i>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item bg-transparent">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset('admin/assets/images/avatars/avatar-3.png') }}"
                                            alt="user avatar" class="rounded-circle" width="55" height="55">
                                        <div class="ms-3">
                                            <h6 class="mb-0">Mackbook Pro <small class="ml-4">06.45 AM</small>
                                            </h6>
                                            <p class="mb-0 small-font">Jhon Doe : Excllent product and awsome quality
                                            </p>
                                        </div>
                                        <div class="ms-auto star">
                                            <i class='bx bxs-star text-warning'></i>
                                            <i class='bx bxs-star text-warning'></i>
                                            <i class='bx bxs-star text-warning'></i>
                                            <i class='bx bxs-star text-light-4'></i>
                                            <i class='bx bxs-star text-light-4'></i>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item bg-transparent">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset('admin/assets/images/avatars/avatar-4.png') }}"
                                            alt="user avatar" class="rounded-circle" width="55" height="55">
                                        <div class="ms-3">
                                            <h6 class="mb-0">Air Pod <small class="ml-4">08.34 AM</small></h6>
                                            <p class="mb-0 small-font">Christine : The brand apple is original !</p>
                                        </div>
                                        <div class="ms-auto star">
                                            <i class='bx bxs-star text-warning'></i>
                                            <i class='bx bxs-star text-warning'></i>
                                            <i class='bx bxs-star text-warning'></i>
                                            <i class='bx bxs-star text-light-4'></i>
                                            <i class='bx bxs-star text-light-4'></i>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item bg-transparent">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset('admin/assets/images/avatars/avatar-7.png') }}"
                                            alt="user avatar" class="rounded-circle" width="55" height="55">
                                        <div class="ms-3">
                                            <h6 class="mb-0">Mackbook <small class="ml-4">08.34 AM</small></h6>
                                            <p class="mb-0 small-font">Michle : The brand apple is original !</p>
                                        </div>
                                        <div class="ms-auto star">
                                            <i class='bx bxs-star text-warning'></i>
                                            <i class='bx bxs-star text-warning'></i>
                                            <i class='bx bxs-star text-warning'></i>
                                            <i class='bx bxs-star text-light-4'></i>
                                            <i class='bx bxs-star text-light-4'></i>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div><!--End Row-->


                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <h5 class="mb-0">Orders Summary</h5>
                            </div>
                            <div class="font-22 ms-auto"><i class="bx bx-dots-horizontal-rounded"></i>
                            </div>
                        </div>
                        <hr>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order id</th>
                                        <th>Product</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentOrders as $order)
                                    <tr>
                                        <td>#{{ $order->order_number }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="recent-product-img">
                                                    <i class="bx bx-package fs-4 text-primary"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <h6 class="mb-1 font-14">{{ $order->customer_email }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $order->customer_name }}</td>
                                        <td>{{ $order->created_at }}</td>
                                        <td>${{ number_format($order->total, 2) }}</td>
                                        <td>
                                            @if($order->status === 'delivered' || $order->status === 'completed')
                                                <div class="badge rounded-pill bg-light-success text-success w-100">Completed</div>
                                            @elseif($order->status === 'cancelled')
                                                <div class="badge rounded-pill bg-light-danger text-danger w-100">Cancelled</div>
                                            @else
                                                <div class="badge rounded-pill bg-light-info text-info w-100">{{ ucfirst($order->status) }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex order-actions">
                                                <a href="{{ route('admin.orders.show', $order->id) }}" class="text-primary" title="View Order">
                                                    <i class="bx bx-show fs-5"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No recent orders found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ═══════════════════════════════════════════════════════
                     PRODUCT RECOMMENDATION ENGINE
                     ═══════════════════════════════════════════════════════ --}}
                <div class="row mt-4 mb-2">
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0 fw-bold"><i class="bx bx-brain me-2 text-primary"></i>Product Recommendation Engine</h5>
                            <span class="badge bg-primary ms-2">AI Insights</span>
                        </div>
                        <p class="text-muted small mt-1 mb-0">Data-driven actions to maximize revenue and inventory health</p>
                        <hr>
                    </div>
                </div>

                {{-- Row 1: KPI summary cards --}}
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-4">
                    <div class="col">
                        <div class="card radius-10 border-start border-danger border-4">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="bg-danger bg-opacity-10 rounded p-3">
                                    <i class="bx bx-error-circle fs-2 text-danger"></i>
                                </div>
                                <div>
                                    <div class="fs-4 fw-bold text-danger">{{ $lowStockProducts->count() }}</div>
                                    <div class="text-muted small">Low Stock Products</div>
                                    <div class="text-danger small fw-semibold">Need Restocking</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10 border-start border-success border-4">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="bg-success bg-opacity-10 rounded p-3">
                                    <i class="bx bx-trending-up fs-2 text-success"></i>
                                </div>
                                <div>
                                    <div class="fs-4 fw-bold text-success">{{ $trendingProducts->count() }}</div>
                                    <div class="text-muted small">Trending (7 days)</div>
                                    <div class="text-success small fw-semibold">Promote These</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10 border-start border-warning border-4">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="bg-warning bg-opacity-10 rounded p-3">
                                    <i class="bx bx-archive fs-2 text-warning"></i>
                                </div>
                                <div>
                                    <div class="fs-4 fw-bold text-warning">{{ $deadStockProducts->count() }}</div>
                                    <div class="text-muted small">Dead Stock (30 days)</div>
                                    <div class="text-warning small fw-semibold">Discount / Bundle</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10 border-start border-info border-4">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="bg-info bg-opacity-10 rounded p-3">
                                    <i class="bx bx-category fs-2 text-info"></i>
                                </div>
                                <div>
                                    <div class="fs-4 fw-bold text-info">{{ $categoryRevenue->count() }}</div>
                                    <div class="text-muted small">Active Categories</div>
                                    <div class="text-info small fw-semibold">Revenue Tracked</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row 2: Trending + Dead Stock --}}
                <div class="row mb-4">
                    {{-- Trending Products --}}
                    <div class="col-12 col-xl-6 mb-4 mb-xl-0">
                        <div class="card radius-10 h-100">
                            <div class="card-header bg-transparent border-bottom d-flex align-items-center">
                                <i class="bx bx-trending-up text-success me-2 fs-5"></i>
                                <h6 class="mb-0 fw-bold">🔥 Trending Products <small class="text-muted fw-normal">(Last 7 days)</small></h6>
                                <span class="badge bg-success ms-auto">Promote</span>
                            </div>
                            <div class="card-body p-0">
                                @forelse($trendingProducts as $product)
                                <div class="d-flex align-items-center px-3 py-2 border-bottom">
                                    <div class="bg-success bg-opacity-10 rounded p-2 me-3">
                                        <i class="bx bx-package text-success"></i>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="fw-semibold text-truncate" style="max-width:200px;">{{ $product->name }}</div>
                                        <div class="text-muted small">${{ number_format($product->price, 2) }} &bull; {{ number_format($product->quantity) }} in stock</div>
                                    </div>
                                    <div class="text-end ms-2">
                                        <div class="fw-bold text-success">{{ $product->units_sold }} sold</div>
                                        <div class="small text-muted">${{ number_format($product->week_revenue, 0) }}</div>
                                    </div>
                                    <div class="ms-2">
                                        <span class="badge bg-success bg-opacity-10 text-success px-2">Hot</span>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-5 text-muted">
                                    <i class="bx bx-bar-chart-alt-2 fs-1 d-block mb-2"></i>
                                    No trending products in the last 7 days
                                </div>
                                @endforelse
                            </div>
                            @if($trendingProducts->count())
                            <div class="card-footer bg-transparent text-center">
                                <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-success">View All Products</a>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Dead Stock --}}
                    <div class="col-12 col-xl-6">
                        <div class="card radius-10 h-100">
                            <div class="card-header bg-transparent border-bottom d-flex align-items-center">
                                <i class="bx bx-archive text-warning me-2 fs-5"></i>
                                <h6 class="mb-0 fw-bold">📦 Dead Stock <small class="text-muted fw-normal">(0 orders in 30 days)</small></h6>
                                <span class="badge bg-warning text-dark ms-auto">Action Needed</span>
                            </div>
                            <div class="card-body p-0">
                                @forelse($deadStockProducts as $product)
                                <div class="d-flex align-items-center px-3 py-2 border-bottom">
                                    <div class="bg-warning bg-opacity-10 rounded p-2 me-3">
                                        <i class="bx bx-box text-warning"></i>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="fw-semibold text-truncate" style="max-width:200px;">{{ $product->name }}</div>
                                        <div class="text-muted small">
                                            ${{ number_format($product->price, 2) }}
                                            @if($product->category)
                                             &bull; {{ $product->category->name }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-end ms-2">
                                        <div class="fw-bold text-warning">{{ number_format($product->quantity) }}</div>
                                        <div class="small text-muted">units idle</div>
                                    </div>
                                    <div class="ms-2 d-flex flex-column gap-1">
                                        <span class="badge bg-warning bg-opacity-10 text-warning px-2 small">Discount</span>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-5 text-muted">
                                    <i class="bx bx-check-circle fs-1 d-block mb-2 text-success"></i>
                                    All products sold in the last 30 days!
                                </div>
                                @endforelse
                            </div>
                            @if($deadStockProducts->count())
                            <div class="card-footer bg-transparent text-center">
                                <small class="text-muted">💡 Tip: Apply a discount or bundle these products to clear inventory</small>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Row 3: Low Stock Alerts + Category Revenue Chart --}}
                <div class="row mb-4">
                    {{-- Low Stock Alerts --}}
                    <div class="col-12 col-xl-6 mb-4 mb-xl-0">
                        <div class="card radius-10 h-100">
                            <div class="card-header bg-transparent border-bottom d-flex align-items-center">
                                <i class="bx bx-error-circle text-danger me-2 fs-5"></i>
                                <h6 class="mb-0 fw-bold">⚠️ Low Stock Alerts <small class="text-muted fw-normal">(≤10 units)</small></h6>
                                <span class="badge bg-danger ms-auto">Urgent</span>
                            </div>
                            <div class="card-body p-0">
                                @forelse($lowStockProducts as $product)
                                @php
                                    $stockPct = $product->quantity <= 3 ? 'danger' : ($product->quantity <= 7 ? 'warning' : 'info');
                                @endphp
                                <div class="d-flex align-items-center px-3 py-2 border-bottom">
                                    <div class="bg-{{ $stockPct }} bg-opacity-10 rounded p-2 me-3">
                                        <i class="bx bx-package text-{{ $stockPct }}"></i>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="fw-semibold text-truncate" style="max-width:200px;">{{ $product->name }}</div>
                                        <div class="mt-1" style="max-width:160px;">
                                            <div class="progress" style="height:4px;">
                                                <div class="progress-bar bg-{{ $stockPct }}" style="width: {{ min($product->quantity * 10, 100) }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-end ms-3">
                                        <span class="badge bg-{{ $stockPct }} fs-6">{{ $product->quantity }}</span>
                                        <div class="small text-muted mt-1">left</div>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-5 text-muted">
                                    <i class="bx bx-check-shield fs-1 d-block mb-2 text-success"></i>
                                    All products are well stocked!
                                </div>
                                @endforelse
                            </div>
                            @if($lowStockProducts->count())
                            <div class="card-footer bg-transparent text-center">
                                <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-danger">Manage Inventory</a>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Category Revenue Doughnut --}}
                    <div class="col-12 col-xl-6">
                        <div class="card radius-10 h-100">
                            <div class="card-header bg-transparent border-bottom d-flex align-items-center">
                                <i class="bx bx-pie-chart text-info me-2 fs-5"></i>
                                <h6 class="mb-0 fw-bold">📊 Category Revenue Breakdown</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-6 d-flex align-items-center justify-content-center">
                                        <canvas id="categoryRevenueChart" width="220" height="220"></canvas>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        @php
                                            $catColors = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69','#2e59d9'];
                                            $totalCatRev = $categoryRevenue->sum('revenue');
                                        @endphp
                                        @forelse($categoryRevenue as $i => $cat)
                                        @php $pct = $totalCatRev > 0 ? round(($cat->revenue / $totalCatRev) * 100, 1) : 0; @endphp
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="me-2 rounded-circle d-inline-block" style="width:12px;height:12px;background:{{ $catColors[$i % count($catColors)] }};flex-shrink:0;"></span>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="text-truncate small fw-semibold" style="max-width:120px;">{{ $cat->category }}</div>
                                                <div class="text-muted" style="font-size:11px;">${{ number_format($cat->revenue, 0) }} &bull; {{ $cat->product_count }} products</div>
                                            </div>
                                            <span class="badge bg-light text-dark ms-1">{{ $pct }}%</span>
                                        </div>
                                        @empty
                                        <p class="text-muted small">No revenue data yet.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recommendation Action Cards --}}
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card radius-10 bg-gradient-deepblue text-white">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="bx bx-bulb me-2"></i>Smart Recommendations</h6>
                                <div class="row row-cols-1 row-cols-md-3 g-3">
                                    @if($lowStockProducts->count() > 0)
                                    <div class="col">
                                        <div class="bg-white bg-opacity-10 rounded p-3 h-100">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="bx bx-refresh fs-4 me-2"></i>
                                                <strong>Restock Now</strong>
                                            </div>
                                            <p class="mb-2 small opacity-75">{{ $lowStockProducts->count() }} products are running low. Reorder before they sell out.</p>
                                            <div class="small opacity-75">
                                                @foreach($lowStockProducts->take(3) as $p)
                                                <div>• {{ Str::limit($p->name, 30) }} ({{ $p->quantity }} left)</div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    @if($trendingProducts->count() > 0)
                                    <div class="col">
                                        <div class="bg-white bg-opacity-10 rounded p-3 h-100">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="bx bx-broadcast fs-4 me-2"></i>
                                                <strong>Feature in Promotions</strong>
                                            </div>
                                            <p class="mb-2 small opacity-75">These products are selling fast. Add them to sliders or homepage sections.</p>
                                            <div class="small opacity-75">
                                                @foreach($trendingProducts->take(3) as $p)
                                                <div>• {{ Str::limit($p->name, 30) }} ({{ $p->units_sold }} sold this week)</div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    @if($deadStockProducts->count() > 0)
                                    <div class="col">
                                        <div class="bg-white bg-opacity-10 rounded p-3 h-100">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="bx bx-purchase-tag fs-4 me-2"></i>
                                                <strong>Clear Dead Stock</strong>
                                            </div>
                                            <p class="mb-2 small opacity-75">{{ $deadStockProducts->count() }} products haven't sold in 30 days. Try a discount or bundle deal.</p>
                                            <div class="small opacity-75">
                                                @foreach($deadStockProducts->take(3) as $p)
                                                <div>• {{ Str::limit($p->name, 30) }} ({{ $p->quantity }} idle)</div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

@push('scripts')
<script>
(function() {
    // Category Revenue Doughnut Chart
    var catCtx = document.getElementById('categoryRevenueChart');
    if (!catCtx) return;

    var catData = @json($categoryRevenue->pluck('revenue'));
    var catLabels = @json($categoryRevenue->pluck('category'));
    var catColors = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69','#2e59d9'];

    new Chart(catCtx, {
        type: 'doughnut',
        data: {
            labels: catLabels,
            datasets: [{
                data: catData,
                backgroundColor: catColors.slice(0, catLabels.length),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: false,
            cutout: '65%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ' $' + Number(ctx.raw).toLocaleString();
                        }
                    }
                }
            }
        }
    });
})();
</script>
@endpush
@endsection
