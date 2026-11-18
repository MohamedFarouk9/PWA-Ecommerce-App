<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->order_number ?? 'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</title>
    <link href="{{ asset('admin/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        body {
            background-color: #f8f9fa;
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
        }

        .invoice-card {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            max-width: 860px;
            margin: 30px auto;
            padding: 40px;
        }

        .invoice-header {
            border-bottom: 2px solid #f1f3f5;
            padding-bottom: 25px;
            margin-bottom: 25px;
        }

        .company-logo {
            font-size: 26px;
            font-weight: 800;
            color: #0d6efd;
            letter-spacing: -0.5px;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: 700;
            color: #212529;
            text-align: right;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .table-invoice th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .table-invoice td {
            vertical-align: middle;
            border-bottom: 1px solid #f1f3f5;
        }

        .status-stamp {
            display: inline-block;
            padding: 6px 14px;
            font-weight: 700;
            font-size: 13px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-paid {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #664d03;
            border: 1px solid #ffecb5;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }

        .action-bar {
            background: #ffffff;
            border-bottom: 1px solid #dee2e6;
            padding: 12px 0;
            margin-bottom: 20px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff;
                margin: 0;
                padding: 0;
            }
            .invoice-card {
                box-shadow: none;
                margin: 0;
                padding: 0;
                max-width: 100%;
            }
            @page {
                size: A4;
                margin: 15mm;
            }
        }
    </style>
</head>
<body>

    <!-- Sticky Action Bar (Hidden when printing / downloading as PDF) -->
    <div class="action-bar no-print">
        <div class="container d-flex justify-content-between align-items-center" style="max-width: 860px;">
            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-arrow-back me-1"></i> Back to Order
            </a>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-primary btn-sm">
                    <i class="bx bx-printer me-1"></i> Print / Save as PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Invoice Sheet -->
    <div class="invoice-card">

        <!-- Top Header -->
        <div class="invoice-header d-flex justify-content-between align-items-start">
            <div>
                <div class="company-logo d-flex align-items-center gap-2 mb-1">
                    <i class="bx bx-shopping-bag"></i> Rukada Store
                </div>
                <div class="text-muted small">
                    123 Commerce Blvd, Suite 400<br>
                    support@rukadastore.com &bull; +1 (800) 555-0199<br>
                    www.rukadastore.com
                </div>
            </div>

            <div>
                <div class="invoice-title">INVOICE</div>
                <div class="text-end small">
                    <strong>Invoice #:</strong> {{ $order->order_number ?? 'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT) }}<br>
                    <strong>Date:</strong> {{ $order->created_at->format('M d, Y') }}<br>
                    <strong>Due Date:</strong> {{ $order->created_at->format('M d, Y') }}<br>
                    <div class="mt-2">
                        @if($order->payment_status === 'paid')
                            <span class="status-stamp status-paid"><i class="bx bx-check-circle me-1"></i> PAID</span>
                        @elseif($order->payment_status === 'pending')
                            <span class="status-stamp status-pending"><i class="bx bx-time me-1"></i> UNPAID</span>
                        @else
                            <span class="status-stamp status-cancelled">{{ strtoupper($order->payment_status) }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Bill To / Ship To Details -->
        <div class="row mb-4">
            <div class="col-6">
                <h6 class="text-muted text-uppercase fw-bold small mb-2">Billed To</h6>
                <div class="fw-bold fs-6">{{ $order->user->name ?? 'Guest Customer' }}</div>
                <div class="text-muted small">
                    Email: {{ $order->user->email ?? 'N/A' }}<br>
                    Phone: {{ $order->user->phone ?? 'N/A' }}<br>
                    Address: {{ $order->billing_address ?? ($order->user->address ?? 'Standard Billing Address') }}
                </div>
            </div>

            <div class="col-6 text-end">
                <h6 class="text-muted text-uppercase fw-bold small mb-2">Shipped To</h6>
                <div class="fw-bold fs-6">{{ $order->user->name ?? 'Guest Customer' }}</div>
                <div class="text-muted small">
                    Address: {{ $order->shipping_address ?? ($order->user->address ?? 'Standard Shipping Address') }}<br>
                    Payment Method: <strong>{{ ucfirst($order->payment_method ?? 'Credit Card') }}</strong><br>
                    Order Status: <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                </div>
            </div>
        </div>

        <!-- Order Items Table -->
        <div class="table-responsive mb-4">
            <table class="table table-invoice mb-0">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 45%;">Item & Description</th>
                        <th class="text-center" style="width: 15%;">Unit Price</th>
                        <th class="text-center" style="width: 15%;">Quantity</th>
                        <th class="text-end" style="width: 20%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $subtotal = 0; @endphp
                    @forelse($order->items as $index => $item)
                        @php
                            $itemPrice = (float) ($item->price ?? $item->unit_price ?? 0);
                            $lineTotal = $itemPrice * (int) ($item->quantity ?? 1);
                            $subtotal += $lineTotal;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-bold">{{ $item->product->name ?? 'Product #' . $item->product_id }}</div>
                                @if($item->product && $item->product->category)
                                    <small class="text-muted">Category: {{ $item->product->category->name }}</small>
                                @endif
                            </td>
                            <td class="text-center">${{ number_format($itemPrice, 2) }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end fw-bold">${{ number_format($lineTotal, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-3 text-muted">No line items in this order.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Summary Calculation -->
        <div class="row justify-content-end mb-4">
            <div class="col-6 col-md-5">
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted">Subtotal:</td>
                            <td class="text-end fw-semibold">${{ number_format($subtotal > 0 ? $subtotal : (float)$order->total_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Shipping & Handling:</td>
                            <td class="text-end fw-semibold">$0.00</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tax (0%):</td>
                            <td class="text-end fw-semibold">$0.00</td>
                        </tr>
                        <tr class="border-top border-dark">
                            <td class="fs-5 fw-bold text-dark pt-2">Total Amount:</td>
                            <td class="fs-5 fw-bold text-primary text-end pt-2">${{ number_format((float)$order->total_amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer / Terms -->
        <div class="border-top pt-3 text-muted small">
            <div class="row">
                <div class="col-8">
                    <strong>Payment Terms:</strong> Payment due upon receipt. All sales are subject to our standard terms and conditions.
                    Thank you for shopping with Rukada!
                </div>
                <div class="col-4 text-end">
                    <strong>Questions?</strong><br>
                    support@rukadastore.com
                </div>
            </div>
        </div>

    </div>

</body>
</html>
