{{-- 1-Click Quick Flash Sale Coupon Modal --}}
<div class="modal fade" id="quickFlashSaleModal" tabindex="-1" aria-labelledby="quickFlashSaleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form action="{{ route('admin.dashboard.quick-flash-sale') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="quickFlashSaleModalLabel">
                        <i class='bx bxs-zap text-warning me-1'></i> Launch Instant Flash Sale Coupon
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Create an instant live discount code to recover abandoned carts, clear idle stock, or run social ads.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Coupon Code</label>
                        <input type="text" name="code" class="form-control text-uppercase fw-bold" value="FLASH{{ rand(10, 99) }}" required maxlength="32">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Discount (%)</label>
                            <input type="number" name="discount_amount" class="form-control" value="15" min="1" max="80" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Min. Order Spend ($)</label>
                            <input type="number" name="min_spend" class="form-control" value="50" min="0" step="1">
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Duration (Hours)</label>
                            <select name="duration_hours" class="form-select">
                                <option value="24">24 Hours (1 Day)</option>
                                <option value="48" selected>48 Hours (Weekend Flash)</option>
                                <option value="72">72 Hours (3 Days)</option>
                                <option value="168">7 Days (Weekly Campaign)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Usage Limit</label>
                            <input type="number" name="usage_limit" class="form-control" value="200" min="1" max="100000">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class='bx bx-rocket me-1'></i> Activate Coupon Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
