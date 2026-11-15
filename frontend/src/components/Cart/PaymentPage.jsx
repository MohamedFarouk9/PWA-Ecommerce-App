import React, { useContext, useEffect, useMemo, useState } from "react";
import { Container, Row, Col, Card, Button, Form, Badge, ListGroup, Alert, Spinner } from "react-bootstrap";
import { useLocation, useNavigate } from "react-router-dom";
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
    subtitle: "Credit & Debit Cards (Global)",
    icon: "fa fa-credit-card",
    accent: "#635bff",
  },
  {
    id: "paymob",
    name: "Paymob",
    subtitle: "Cards, Wallets & Local Payment",
    icon: "fa fa-mobile-alt",
    accent: "#0d6efd",
  },
  {
    id: "myfatoorah",
    name: "MyFatoorah",
    subtitle: "KNET, GCC Cards & Regional",
    icon: "fa fa-globe",
    accent: "#198754",
  },
];

const PaymentPage = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const { cart, fetchCart } = useContext(CartContext) || {};
  const { user, token } = useContext(AuthContext) || {};

  const orderData = location.state?.order || {
    items: cart && cart.length > 0 ? cart : [
      { id: 1, name: "ASUS TUF A15 FA506IU", quantity: 1, price: 100 },
      { id: 2, name: "Infinix Hot 10 Play", quantity: 1, price: 150 },
    ],
    totalQuantity: cart?.length || 2,
    totalPrice: 250,
  };

  const [selectedMethod, setSelectedMethod] = useState("stripe");
  const [formData, setFormData] = useState({
    fullName: user?.name || "",
    email: user?.email || "",
    phone: "",
    address: "",
    notes: "",
  });

  const [isProcessing, setIsProcessing] = useState(false);
  const [paymentResult, setPaymentResult] = useState(null);
  const [errorMessage, setErrorMessage] = useState(null);

  useEffect(() => {
    window.scrollTo({ top: 0, behavior: "smooth" });
    if (user) {
      setFormData((prev) => ({
        ...prev,
        fullName: prev.fullName || user.name || "",
        email: prev.email || user.email || "",
      }));
    }
  }, [user]);

  const subtotal = useMemo(() => {
    return (orderData.items || []).reduce(
      (acc, item) => acc + (Number(item.quantity) || 1) * (Number(item.price) || 0),
      0
    );
  }, [orderData.items]);

  const shipping = subtotal > 0 ? 15 : 0;
  const total = subtotal + shipping;

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrorMessage(null);

    if (!token) {
      ToastMessages.showError("Please log in to complete your purchase.");
      navigate("/login", { state: { from: location } });
      return;
    }

    setIsProcessing(true);

    try {
      // Step 1: Create Order Authoritatively in Backend
      const orderPayload = {
        shipping_address: `${formData.fullName}, ${formData.address}, Phone: ${formData.phone}`,
        billing_address: `${formData.fullName}, ${formData.address}`,
        payment_method: selectedMethod,
        notes: formData.notes || "Order placed via Web PWA",
      };

      const orderResponse = await apiClient.post(AppURL.Orders, orderPayload);
      const createdOrder = orderResponse?.data?.data;

      if (!createdOrder || !createdOrder.order_id) {
        throw new Error("Unable to create order. Please review your cart and try again.");
      }

      // Step 2: Process Idempotent Payment
      const idempotencyKey = `idemp_${createdOrder.order_id}_${Date.now()}_${Math.random().toString(36).substring(2, 9)}`;

      const chargePayload = {
        order_id: createdOrder.order_id,
        gateway: selectedMethod,
        idempotency_key: idempotencyKey,
        metadata: {
          customer_email: formData.email,
        },
      };

      const chargeResponse = await apiClient.post(AppURL.PaymentCharge, chargePayload);

      if (chargeResponse.data?.success) {
        setPaymentResult({
          transactionId: chargeResponse.data.transaction_id,
          orderNumber: createdOrder.order_number,
          gateway: chargeResponse.data.gateway,
          amount: createdOrder.total_amount,
        });

        if (fetchCart) {
          fetchCart();
        }

        ToastMessages.showSuccess("Payment completed successfully!");
      } else {
        throw new Error(chargeResponse.data?.error || "Payment was declined by gateway.");
      }
    } catch (err) {
      console.error("Checkout error:", err);
      const msg = err.response?.data?.message || err.message || "An unexpected error occurred during payment.";
      setErrorMessage(msg);
      ToastMessages.showError(msg);
    } finally {
      setIsProcessing(false);
    }
  };

  return (
    <ResponsiveLayout>
      <Container className="py-4">
        <div className="section-title text-center mb-4">
          <h2>Checkout & Payment</h2>
          <p>Secure, Idempotent Multi-Gateway Payment Processing</p>
        </div>

        {paymentResult ? (
          <Row className="justify-content-center">
            <Col lg={8}>
              <Card className="shadow-sm border-0 text-center p-4">
                <Card.Body>
                  <div className="mb-3 text-success">
                    <i className="fa fa-check-circle fa-5x"></i>
                  </div>
                  <h3 className="fw-bold mb-2">Order Confirmed!</h3>
                  <p className="text-muted">
                    Thank you for your order. We have received your payment and are processing it.
                  </p>
                  <div className="bg-light p-3 rounded mb-4 text-start">
                    <div className="d-flex justify-content-between py-1">
                      <span className="text-muted">Order Number:</span>
                      <strong>{paymentResult.orderNumber}</strong>
                    </div>
                    <div className="d-flex justify-content-between py-1">
                      <span className="text-muted">Transaction ID:</span>
                      <code>{paymentResult.transactionId}</code>
                    </div>
                    <div className="d-flex justify-content-between py-1">
                      <span className="text-muted">Payment Gateway:</span>
                      <Badge bg="primary" className="text-uppercase">{paymentResult.gateway}</Badge>
                    </div>
                    <div className="d-flex justify-content-between py-1">
                      <span className="text-muted">Total Paid:</span>
                      <strong>${Number(paymentResult.amount).toFixed(2)}</strong>
                    </div>
                  </div>
                  <Button variant="primary" onClick={() => navigate("/")} className="me-2">
                    <i className="fa fa-home"></i> Continue Shopping
                  </Button>
                </Card.Body>
              </Card>
            </Col>
          </Row>
        ) : (
          <Row className="g-4">
            <Col lg={7} md={12}>
              <Card className="shadow-sm border-0">
                <Card.Body>
                  <h5 className="mb-3">Shipping & Contact Details</h5>

                  {errorMessage && (
                    <Alert variant="danger" onClose={() => setErrorMessage(null)} dismissible>
                      {errorMessage}
                    </Alert>
                  )}

                  <Form onSubmit={handleSubmit}>
                    <Row>
                      <Col md={6}>
                        <Form.Group className="mb-3">
                          <Form.Label>Full Name</Form.Label>
                          <Form.Control
                            type="text"
                            name="fullName"
                            value={formData.fullName}
                            onChange={handleChange}
                            placeholder="Enter your name"
                            required
                          />
                        </Form.Group>
                      </Col>
                      <Col md={6}>
                        <Form.Group className="mb-3">
                          <Form.Label>Email</Form.Label>
                          <Form.Control
                            type="email"
                            name="email"
                            value={formData.email}
                            onChange={handleChange}
                            placeholder="Enter your email"
                            required
                          />
                        </Form.Group>
                      </Col>
                    </Row>

                    <Row>
                      <Col md={6}>
                        <Form.Group className="mb-3">
                          <Form.Label>Phone</Form.Label>
                          <Form.Control
                            type="text"
                            name="phone"
                            value={formData.phone}
                            onChange={handleChange}
                            placeholder="e.g. +1 555-0199"
                            required
                          />
                        </Form.Group>
                      </Col>
                      <Col md={6}>
                        <Form.Group className="mb-3">
                          <Form.Label>Delivery Address</Form.Label>
                          <Form.Control
                            type="text"
                            name="address"
                            value={formData.address}
                            onChange={handleChange}
                            placeholder="Street, City, Postal Code"
                            required
                          />
                        </Form.Group>
                      </Col>
                    </Row>

                    <Form.Group className="mb-3">
                      <Form.Label>Order Notes (Optional)</Form.Label>
                      <Form.Control
                        as="textarea"
                        rows={2}
                        name="notes"
                        value={formData.notes}
                        onChange={handleChange}
                        placeholder="Special delivery instructions..."
                      />
                    </Form.Group>

                    <h5 className="mt-4 mb-3">Select Payment Gateway</h5>
                    <div className="d-grid gap-2">
                      {paymentMethods.map((method) => (
                        <Card
                          key={method.id}
                          className={`border ${selectedMethod === method.id ? "border-primary shadow-sm" : ""}`}
                          style={{ cursor: "pointer" }}
                          onClick={() => setSelectedMethod(method.id)}
                        >
                          <Card.Body className="d-flex justify-content-between align-items-center py-2">
                            <div className="d-flex align-items-center gap-3">
                              <div
                                className="rounded-circle d-flex align-items-center justify-content-center"
                                style={{
                                  width: 42,
                                  height: 42,
                                  backgroundColor: `${method.accent}15`,
                                  color: method.accent,
                                }}
                              >
                                <i className={method.icon}></i>
                              </div>
                              <div>
                                <h6 className="mb-0">{method.name}</h6>
                                <small className="text-muted">{method.subtitle}</small>
                              </div>
                            </div>
                            <Form.Check
                              type="radio"
                              name="paymentMethod"
                              checked={selectedMethod === method.id}
                              onChange={() => setSelectedMethod(method.id)}
                            />
                          </Card.Body>
                        </Card>
                      ))}
                    </div>

                    <Button
                      type="submit"
                      disabled={isProcessing}
                      className="btn site-btn w-100 mt-4 py-2 fw-bold"
                    >
                      {isProcessing ? (
                        <>
                          <Spinner as="span" animation="border" size="sm" role="status" className="me-2" />
                          Processing Transaction...
                        </>
                      ) : (
                        <>
                          <i className="fa fa-lock me-2"></i> Pay ${total.toFixed(2)} with {selectedMethod.toUpperCase()}
                        </>
                      )}
                    </Button>
                  </Form>
                </Card.Body>
              </Card>
            </Col>

            <Col lg={5} md={12}>
              <Card className="shadow-sm border-0">
                <Card.Body>
                  <div className="d-flex justify-content-between align-items-center mb-3">
                    <h5 className="mb-0">Order Summary</h5>
                    <Badge bg="secondary">{orderData.items?.length || 0} items</Badge>
                  </div>

                  <ListGroup variant="flush">
                    {(orderData.items || []).map((item, idx) => (
                      <ListGroup.Item key={item.id || idx} className="px-0">
                        <div className="d-flex justify-content-between align-items-center">
                          <div>
                            <strong>{item.product?.title || item.name || "Product"}</strong>
                            <div className="text-muted small">Qty: {item.quantity || 1}</div>
                          </div>
                          <span>${((item.quantity || 1) * (item.price || item.product?.price || 0)).toFixed(2)}</span>
                        </div>
                      </ListGroup.Item>
                    ))}
                  </ListGroup>

                  <div className="mt-3">
                    <div className="d-flex justify-content-between mb-2">
                      <span>Subtotal</span>
                      <span>${subtotal.toFixed(2)}</span>
                    </div>
                    <div className="d-flex justify-content-between mb-2">
                      <span>Shipping</span>
                      <span>${shipping.toFixed(2)}</span>
                    </div>
                    <hr />
                    <div className="d-flex justify-content-between fw-bold fs-5">
                      <span>Total</span>
                      <span>${total.toFixed(2)}</span>
                    </div>
                  </div>

                  <Button
                    variant="outline-secondary"
                    className="w-100 mt-4"
                    onClick={() => navigate(-1)}
                  >
                    <i className="fa fa-arrow-left me-1"></i> Back to Cart
                  </Button>
                </Card.Body>
              </Card>
            </Col>
          </Row>
        )}
      </Container>
    </ResponsiveLayout>
  );
};

export default PaymentPage;
