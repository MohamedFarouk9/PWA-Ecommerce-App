import React, { useContext, useEffect, useMemo, useRef, useState } from "react";
import {
  Container,
  Row,
  Col,
  Card,
  Button,
  Form,
  Badge,
  ListGroup,
  Alert,
  Spinner,
} from "react-bootstrap";
import { Link, useLocation, useNavigate } from "react-router-dom";
import { ResponsiveLayout } from "../../layouts/ResponsiveLayout";
import { CartContext } from "../../utils/CartContext";
import { AuthContext } from "../../utils/AuthContext";
import apiClient from "../../services/apiClient";
import AppURL from "../../utils/AppURL";
import ToastMessages from "../../toast-messages/toast";

const paymentMethods = [
  {
    id: "stripe",
    name: "Stripe",
    subtitle: "Credit & Debit Cards (Global Visa / Mastercard)",
    icon: "fa fa-credit-card",
    accent: "#635bff",
  },
  {
    id: "paymob",
    name: "Paymob",
    subtitle: "MENA Cards, Mobile Wallets & ValU",
    icon: "fa fa-mobile-alt",
    accent: "#2563eb",
  },
  {
    id: "myfatoorah",
    name: "MyFatoorah",
    subtitle: "GCC Cards, KNET, Mada & Apple Pay",
    icon: "fa fa-globe",
    accent: "#10b981",
  },
];

const TEST_CARD_PRESETS = [
  { label: "Visa Success (4242)", number: "4242 4242 4242 4242", expiry: "12/28", cvc: "424", scenario: "success", variant: "outline-success" },
  { label: "Paymob Visa (8769)", number: "4987 6543 2109 8769", expiry: "12/28", cvc: "123", scenario: "success", variant: "outline-primary" },
  { label: "Declined (9995)", number: "4000 0000 0000 9995", expiry: "12/28", cvc: "123", scenario: "decline", variant: "outline-danger" },
  { label: "Expired (0002)", number: "4242 4242 4242 0002", expiry: "01/22", cvc: "123", scenario: "expired", variant: "outline-warning" },
];

const formatCardNumber = (val) => {
  const digits = val.replace(/\D/g, "").slice(0, 16);
  return digits.replace(/(.{4})/g, "$1 ").trim();
};

const formatExpiry = (val) => {
  const digits = val.replace(/\D/g, "").slice(0, 4);
  if (digits.length >= 3) return `${digits.slice(0, 2)}/${digits.slice(2)}`;
  return digits;
};

const detectCardBrand = (digits) => {
  if (/^4/.test(digits)) return "visa";
  if (/^5[1-5]/.test(digits)) return "mastercard";
  if (/^3[47]/.test(digits)) return "amex";
  return "card";
};

const PaymentPage = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const { cart, refreshCart } = useContext(CartContext) || {};
  const { user, token } = useContext(AuthContext) || {};
  const profileUser = user?.user || user || null;

  const orderData = useMemo(() => {
    if (location.state?.order?.items?.length) {
      return location.state.order;
    }
    const items = Array.isArray(cart) ? cart : [];
    return {
      items,
      totalQuantity: items.reduce((a, i) => a + Number(i.quantity || 1), 0),
    };
  }, [location.state, cart]);

  const [selectedMethod, setSelectedMethod] = useState("stripe");
  const [formData, setFormData] = useState({
    fullName: profileUser?.name || "",
    email: profileUser?.email || "",
    phone: profileUser?.phone || "",
    address: profileUser?.address || "",
    notes: "",
  });

  // Card inputs (kept client-side only; raw PAN/CVC are NEVER sent to backend)
  const [card, setCard] = useState({
    holder: profileUser?.name || "",
    number: "",
    expiry: "",
    cvc: "",
    scenario: "success",
  });

  // Stable order & idempotency key per checkout attempt (prevents double charges on retry)
  const pendingOrderRef = useRef(null);
  const idempotencyKeyRef = useRef(
    `chk_${Date.now()}_${Math.random().toString(36).slice(2, 10)}`
  );

  const [isProcessing, setIsProcessing] = useState(false);
  const [paymentResult, setPaymentResult] = useState(null);
  const [errorMessage, setErrorMessage] = useState(null);

  // Coupon state
  const [couponCode, setCouponCode] = useState("");
  const [appliedCoupon, setAppliedCoupon] = useState(null);
  const [couponError, setCouponError] = useState(null);
  const [isApplyingCoupon, setIsApplyingCoupon] = useState(false);

  useEffect(() => {
    window.scrollTo({ top: 0, behavior: "smooth" });
    if (profileUser) {
      setFormData((prev) => ({
        ...prev,
        fullName: prev.fullName || profileUser.name || "",
        email: prev.email || profileUser.email || "",
        phone: prev.phone || profileUser.phone || "",
        address: prev.address || profileUser.address || "",
      }));
      setCard((prev) => ({ ...prev, holder: prev.holder || profileUser.name || "" }));
    }
  }, [profileUser]);

  const subtotal = useMemo(() => {
    return (orderData.items || []).reduce((acc, item) => {
      const unitPrice = Number(
        item.price ?? item.product?.special_price ?? item.product?.price ?? 0
      );
      return acc + (Number(item.quantity) || 1) * unitPrice;
    }, 0);
  }, [orderData.items]);

  const shipping = subtotal > 0 ? (subtotal >= 99 ? 0 : 15) : 0;
  const discountAmount = appliedCoupon ? Number(appliedCoupon.discount_amount || 0) : 0;
  const total = Math.max(0, subtotal - discountAmount + shipping);

  const applyTestCard = (preset) => {
    setCard((prev) => ({
      ...prev,
      number: preset.number,
      expiry: preset.expiry,
      cvc: preset.cvc,
      scenario: preset.scenario,
    }));
    // Reset idempotency key when switching test card scenario
    idempotencyKeyRef.current = `chk_${Date.now()}_${Math.random().toString(36).slice(2, 10)}`;
    setErrorMessage(null);
  };

  const handleApplyCoupon = async () => {
    if (!couponCode.trim()) return;
    setCouponError(null);
    setIsApplyingCoupon(true);
    try {
      const res = await apiClient.post(AppURL.ApplyCoupon, {
        code: couponCode.trim(),
        subtotal,
      });
      if (res.data?.success) {
        setAppliedCoupon({
          code: res.data.coupon?.code || couponCode.trim().toUpperCase(),
          discount_amount: Number(res.data.discount_amount || 0),
          new_total: Number(res.data.new_total || 0),
        });
        ToastMessages.showSuccess(res.data.message || "Coupon applied successfully!");
      }
    } catch (err) {
      const msg = err.response?.data?.message || "Invalid coupon code.";
      setCouponError(msg);
      ToastMessages.showError(msg);
    } finally {
      setIsApplyingCoupon(false);
    }
  };

  const handleRemoveCoupon = () => {
    setAppliedCoupon(null);
    setCouponCode("");
    setCouponError(null);
    ToastMessages.showSuccess("Coupon removed.");
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrorMessage(null);

    if (!token) {
      ToastMessages.showError("Please sign in to complete your purchase.");
      navigate("/login", { state: { from: location } });
      return;
    }

    const rawDigits = card.number.replace(/\D/g, "");
    if (rawDigits.length < 13) {
      setErrorMessage("Please enter a valid card number (or click a test card preset below).");
      return;
    }
    if (!/^\d{2}\/\d{2}$/.test(card.expiry)) {
      setErrorMessage("Please enter card expiry in MM/YY format.");
      return;
    }
    if (card.cvc.replace(/\D/g, "").length < 3) {
      setErrorMessage("Please enter a valid 3-4 digit CVC.");
      return;
    }

    setIsProcessing(true);

    try {
      // Step 1: Create authoritative order once (reuse if retrying a declined card)
      let createdOrder = pendingOrderRef.current;
      if (!createdOrder) {
        const orderPayload = {
          shipping_address: `${formData.fullName}, ${formData.address}, Phone: ${formData.phone}`,
          billing_address: `${formData.fullName}, ${formData.address}`,
          payment_method: selectedMethod,
          notes: formData.notes || "Order placed via Web PWA",
          coupon_code: appliedCoupon ? appliedCoupon.code : null,
        };

        const orderResponse = await apiClient.post(AppURL.Orders, orderPayload);
        createdOrder = orderResponse?.data?.data;

        if (!createdOrder || !createdOrder.order_id) {
          throw new Error("Unable to create order. Please review your cart and try again.");
        }
        pendingOrderRef.current = createdOrder;
      }

      // Step 2: Process Idempotent Payment (PCI-safe: only last4 & brand sent, never raw PAN/CVC)
      const cardLast4 = rawDigits.slice(-4);
      const cardBrand = detectCardBrand(rawDigits);

      const chargePayload = {
        order_id: createdOrder.order_id,
        gateway: selectedMethod,
        idempotency_key: `${idempotencyKeyRef.current}_${createdOrder.order_id}_${selectedMethod}`,
        metadata: {
          customer_email: formData.email,
          billing: {
            full_name: formData.fullName,
            phone: formData.phone,
            address: formData.address,
          },
          card_last4: cardLast4,
          card_brand: cardBrand,
          test_card_scenario: card.scenario,
        },
      };

      const chargeResponse = await apiClient.post(AppURL.PaymentCharge, chargePayload);

      // Handle live redirect gateways (Paymob Unified Checkout / MyFatoorah InvoiceURL)
      if (chargeResponse.data?.payment_url) {
        ToastMessages.showInfo(`Redirecting to ${selectedMethod.toUpperCase()} secure checkout...`);
        window.location.href = chargeResponse.data.payment_url;
        return;
      }

      if (chargeResponse.data?.success) {
        pendingOrderRef.current = null;
        setPaymentResult({
          transactionId: chargeResponse.data.transaction_id,
          orderNumber: createdOrder.order_number,
          gateway: chargeResponse.data.gateway,
          mode: chargeResponse.data.mode || "sandbox",
          amount: createdOrder.total_amount,
          cardLast4,
        });

        refreshCart?.();
        ToastMessages.showSuccess("Payment completed successfully!");
      } else {
        // Rotate idempotency key so user can retry with another card on the same order
        idempotencyKeyRef.current = `chk_${Date.now()}_${Math.random().toString(36).slice(2, 10)}`;
        throw new Error(chargeResponse.data?.error || "Payment was declined by gateway.");
      }
    } catch (err) {
      idempotencyKeyRef.current = `chk_${Date.now()}_${Math.random().toString(36).slice(2, 10)}`;
      const msg =
        err.response?.data?.error ||
        err.response?.data?.message ||
        err.message ||
        "An unexpected error occurred during payment.";
      setErrorMessage(msg);
      ToastMessages.showError(msg);
    } finally {
      setIsProcessing(false);
    }
  };

  if (!paymentResult && (!orderData.items || orderData.items.length === 0)) {
    return (
      <ResponsiveLayout>
        <Container className="py-5">
          <Card className="text-center py-5">
            <Card.Body>
              <i className="fa fa-shopping-bag fa-3x text-muted mb-3"></i>
              <h4 className="fw-bold">No items to check out</h4>
              <p className="text-muted mb-4">Add products to your cart before proceeding to payment.</p>
              <Link to="/" className="site-btn text-decoration-none">
                Continue Shopping
              </Link>
            </Card.Body>
          </Card>
        </Container>
      </ResponsiveLayout>
    );
  }

  return (
    <ResponsiveLayout>
      <Container className="py-4">
        <div className="ecom-section-header">
          <div>
            <h2 className="ecom-section-title">Secure Checkout & Payment</h2>
            <p className="ecom-section-subtitle">
              Multi-Gateway Idempotent Payment Processing (Stripe • Paymob • MyFatoorah)
            </p>
          </div>
          <Badge bg="success" className="px-3 py-2 rounded-pill">
            <i className="fa fa-lock me-1"></i> 256-Bit SSL Protected
          </Badge>
        </div>

        {paymentResult ? (
          <Row className="justify-content-center">
            <Col lg={7}>
              <Card className="text-center p-4">
                <Card.Body>
                  <div className="mb-3 text-success">
                    <i className="fa fa-check-circle fa-5x"></i>
                  </div>
                  <h3 className="fw-bold mb-2">Order Confirmed!</h3>
                  <p className="text-muted">
                    Thank you for your purchase. Your payment has been verified and your order is now processing.
                  </p>
                  <div className="bg-light p-3 rounded-3 mb-4 text-start border">
                    <div className="d-flex justify-content-between py-1">
                      <span className="text-muted">Order Number</span>
                      <strong>{paymentResult.orderNumber}</strong>
                    </div>
                    <div className="d-flex justify-content-between py-1">
                      <span className="text-muted">Transaction ID</span>
                      <code>{paymentResult.transactionId}</code>
                    </div>
                    <div className="d-flex justify-content-between py-1">
                      <span className="text-muted">Payment Gateway</span>
                      <div>
                        <Badge bg="primary" className="text-uppercase me-1">{paymentResult.gateway}</Badge>
                        <Badge bg="secondary" className="text-uppercase">{paymentResult.mode}</Badge>
                      </div>
                    </div>
                    <div className="d-flex justify-content-between py-1">
                      <span className="text-muted">Card Used</span>
                      <strong>•••• {paymentResult.cardLast4}</strong>
                    </div>
                    <div className="d-flex justify-content-between py-1 border-top mt-2 pt-2">
                      <span className="text-muted fw-semibold">Total Paid</span>
                      <strong className="text-primary fs-5">${Number(paymentResult.amount).toFixed(2)}</strong>
                    </div>
                  </div>
                  <div className="d-flex justify-content-center gap-2">
                    <Button className="site-btn" onClick={() => navigate("/orders")}>
                      <i className="fa fa-box"></i> View My Orders
                    </Button>
                    <Button variant="outline-secondary" onClick={() => navigate("/")}>
                      Continue Shopping
                    </Button>
                  </div>
                </Card.Body>
              </Card>
            </Col>
          </Row>
        ) : (
          <Row className="g-4">
            <Col lg={7} md={12}>
              <Card className="p-2">
                <Card.Body>
                  <h5 className="fw-bold mb-3">1. Shipping & Contact Details</h5>

                  {errorMessage && (
                    <Alert variant="danger" onClose={() => setErrorMessage(null)} dismissible>
                      <i className="fa fa-exclamation-triangle me-2"></i>
                      {errorMessage}
                    </Alert>
                  )}

                  <Form onSubmit={handleSubmit}>
                    <Row className="g-3">
                      <Col md={6}>
                        <label className="ecom-form-label">Full Name</label>
                        <Form.Control
                          className="ecom-form-control"
                          type="text"
                          name="fullName"
                          value={formData.fullName}
                          onChange={handleChange}
                          placeholder="John Doe"
                          required
                        />
                      </Col>
                      <Col md={6}>
                        <label className="ecom-form-label">Email Address</label>
                        <Form.Control
                          className="ecom-form-control"
                          type="email"
                          name="email"
                          value={formData.email}
                          onChange={handleChange}
                          placeholder="name@example.com"
                          required
                        />
                      </Col>
                      <Col md={6}>
                        <label className="ecom-form-label">Phone Number</label>
                        <Form.Control
                          className="ecom-form-control"
                          type="text"
                          name="phone"
                          value={formData.phone}
                          onChange={handleChange}
                          placeholder="+1 555-0199"
                          required
                        />
                      </Col>
                      <Col md={6}>
                        <label className="ecom-form-label">Delivery Address</label>
                        <Form.Control
                          className="ecom-form-control"
                          type="text"
                          name="address"
                          value={formData.address}
                          onChange={handleChange}
                          placeholder="Street, City, Postal Code"
                          required
                        />
                      </Col>
                      <Col xs={12}>
                        <label className="ecom-form-label">Order Notes (Optional)</label>
                        <Form.Control
                          className="ecom-form-control"
                          as="textarea"
                          rows={2}
                          name="notes"
                          value={formData.notes}
                          onChange={handleChange}
                          placeholder="Special delivery instructions..."
                        />
                      </Col>
                    </Row>

                    <h5 className="fw-bold mt-4 mb-3">2. Select Payment Gateway</h5>
                    <div className="d-grid gap-2">
                      {paymentMethods.map((method) => (
                        <div
                          key={method.id}
                          className={`p-3 rounded-3 border d-flex justify-content-between align-items-center ${
                            selectedMethod === method.id ? "border-primary bg-primary bg-opacity-10" : "bg-white"
                          }`}
                          style={{ cursor: "pointer", transition: "all 0.2s ease" }}
                          onClick={() => setSelectedMethod(method.id)}
                        >
                          <div className="d-flex align-items-center gap-3">
                            <div
                              className="rounded-circle d-flex align-items-center justify-content-center"
                              style={{
                                width: 42,
                                height: 42,
                                backgroundColor: `${method.accent}18`,
                                color: method.accent,
                              }}
                            >
                              <i className={method.icon}></i>
                            </div>
                            <div>
                              <div className="fw-bold">{method.name}</div>
                              <small className="text-muted">{method.subtitle}</small>
                            </div>
                          </div>
                          <Form.Check
                            type="radio"
                            name="paymentMethod"
                            checked={selectedMethod === method.id}
                            onChange={() => setSelectedMethod(method.id)}
                          />
                        </div>
                      ))}
                    </div>

                    <h5 className="fw-bold mt-4 mb-2">3. Card Details</h5>
                    <div className="p-3 rounded-3 bg-light border mb-3">
                      <div className="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <small className="text-muted fw-semibold">
                          <i className="fa fa-flask text-primary me-1"></i> Quick-Fill Sandbox Test Cards:
                        </small>
                        <div className="d-flex flex-wrap gap-1">
                          {TEST_CARD_PRESETS.map((preset) => (
                            <Button
                              key={preset.label}
                              type="button"
                              size="sm"
                              variant={preset.variant}
                              className="py-0 px-2"
                              style={{ fontSize: "11.5px" }}
                              onClick={() => applyTestCard(preset)}
                            >
                              {preset.label}
                            </Button>
                          ))}
                        </div>
                      </div>

                      <Row className="g-2">
                        <Col xs={12}>
                          <label className="ecom-form-label">Card Number</label>
                          <Form.Control
                            className="ecom-form-control bg-white"
                            type="text"
                            inputMode="numeric"
                            placeholder="4242 4242 4242 4242"
                            value={card.number}
                            onChange={(e) =>
                              setCard((prev) => ({
                                ...prev,
                                number: formatCardNumber(e.target.value),
                                scenario: "custom",
                              }))
                            }
                            required
                          />
                        </Col>
                        <Col sm={6}>
                          <label className="ecom-form-label">Expiry Date</label>
                          <Form.Control
                            className="ecom-form-control bg-white"
                            type="text"
                            placeholder="MM/YY"
                            value={card.expiry}
                            onChange={(e) =>
                              setCard((prev) => ({
                                ...prev,
                                expiry: formatExpiry(e.target.value),
                              }))
                            }
                            required
                          />
                        </Col>
                        <Col sm={6}>
                          <label className="ecom-form-label">CVC / CVV</label>
                          <Form.Control
                            className="ecom-form-control bg-white"
                            type="password"
                            maxLength={4}
                            placeholder="123"
                            value={card.cvc}
                            onChange={(e) =>
                              setCard((prev) => ({
                                ...prev,
                                cvc: e.target.value.replace(/\D/g, "").slice(0, 4),
                              }))
                            }
                            required
                          />
                        </Col>
                      </Row>
                    </div>

                    <Button
                      type="submit"
                      disabled={isProcessing}
                      className="site-btn w-100 py-3 fw-bold"
                    >
                      {isProcessing ? (
                        <>
                          <Spinner as="span" animation="border" size="sm" role="status" className="me-2" />
                          Processing Secure Payment...
                        </>
                      ) : (
                        <>
                          <i className="fa fa-lock"></i>
                          <span>Pay ${total.toFixed(2)} via {selectedMethod.toUpperCase()}</span>
                        </>
                      )}
                    </Button>
                  </Form>
                </Card.Body>
              </Card>
            </Col>

            <Col lg={5} md={12}>
              <div className="ecom-summary-card">
                <div className="d-flex justify-content-between align-items-center mb-3">
                  <h5 className="fw-bold mb-0">Order Summary</h5>
                  <Badge bg="primary" pill>{orderData.items?.length || 0} items</Badge>
                </div>

                <ListGroup variant="flush" className="mb-3">
                  {(orderData.items || []).map((item, idx) => {
                    const unitPrice = Number(
                      item.price ?? item.product?.special_price ?? item.product?.price ?? 0
                    );
                    return (
                      <ListGroup.Item key={item.id || idx} className="px-0">
                        <div className="d-flex justify-content-between align-items-center">
                          <div>
                            <div className="fw-semibold small">
                              {item.product?.title || item.product?.name || item.name || "Product"}
                            </div>
                            <div className="text-muted small">Qty: {item.quantity || 1}</div>
                          </div>
                          <strong>${((item.quantity || 1) * unitPrice).toFixed(2)}</strong>
                        </div>
                      </ListGroup.Item>
                    );
                  })}
                </ListGroup>

                <div className="ecom-summary-row">
                  <span>Subtotal</span>
                  <strong>${subtotal.toFixed(2)}</strong>
                </div>

                {appliedCoupon && (
                  <div className="ecom-summary-row text-success">
                    <span><i className="fa fa-tag me-1"></i> Coupon ({appliedCoupon.code})</span>
                    <strong>-${discountAmount.toFixed(2)}</strong>
                  </div>
                )}

                <div className="ecom-summary-row">
                  <span>Shipping</span>
                  {shipping === 0 ? (
                    <span className="text-success fw-bold">FREE</span>
                  ) : (
                    <strong>${shipping.toFixed(2)}</strong>
                  )}
                </div>

                {/* Coupon Input Box */}
                <div className="my-3 p-2 bg-light rounded-3 border">
                  <div className="d-flex gap-2">
                    <Form.Control
                      type="text"
                      size="sm"
                      placeholder="Coupon code (e.g. SAVE20)"
                      value={couponCode}
                      onChange={(e) => setCouponCode(e.target.value.toUpperCase())}
                      disabled={!!appliedCoupon || isApplyingCoupon}
                    />
                    {appliedCoupon ? (
                      <Button variant="outline-danger" size="sm" onClick={handleRemoveCoupon}>
                        Remove
                      </Button>
                    ) : (
                      <Button
                        variant="primary"
                        size="sm"
                        onClick={handleApplyCoupon}
                        disabled={!couponCode.trim() || isApplyingCoupon}
                      >
                        {isApplyingCoupon ? <Spinner size="sm" animation="border" /> : "Apply"}
                      </Button>
                    )}
                  </div>
                  {couponError && <small className="text-danger d-block mt-1">{couponError}</small>}
                  {appliedCoupon && (
                    <small className="text-success d-block mt-1">
                      <i className="fa fa-check me-1"></i> Saved ${discountAmount.toFixed(2)}
                    </small>
                  )}
                </div>

                <div className="ecom-summary-total">
                  <span>Total Due</span>
                  <span className="text-primary">${total.toFixed(2)}</span>
                </div>

                <Button
                  variant="outline-secondary"
                  className="w-100 mt-3"
                  onClick={() => navigate("/cart")}
                >
                  <i className="fa fa-arrow-left me-1"></i> Back to Cart
                </Button>
              </div>
            </Col>
          </Row>
        )}
      </Container>
    </ResponsiveLayout>
  );
};

export default PaymentPage;
