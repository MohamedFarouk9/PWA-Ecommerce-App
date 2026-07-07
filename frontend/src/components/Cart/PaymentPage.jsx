import React, { useEffect, useMemo, useState } from "react";
import { Container, Row, Col, Card, Button, Form, Badge, ListGroup } from "react-bootstrap";
import { useLocation, useNavigate } from "react-router-dom";
import { ResponsiveLayout } from "../../layouts/ResponsiveLayout";

const paymentMethods = [
  {
    id: "stripe",
    name: "Stripe",
    subtitle: "Fast card payment",
    icon: "fa fa-credit-card",
    accent: "#635bff",
  },
  {
    id: "paymob",
    name: "Paymob",
    subtitle: "Egyptian local gateway",
    icon: "fa fa-mobile-alt",
    accent: "#0d6efd",
  },
  {
    id: "myfatoorah",
    name: "MyFatoorah",
    subtitle: "Regional payment method",
    icon: "fa fa-globe",
    accent: "#198754",
  },
];

const PaymentPage = () => {
  const location = useLocation();
  const navigate = useNavigate();

  const orderData = location.state?.order || {
    items: [
      { id: 1, name: "ASUS TUF A15 FA506IU", quantity: 1, price: 100 },
      { id: 2, name: "Infinix Hot 10 Play", quantity: 1, price: 150 },
    ],
    totalQuantity: 2,
    totalPrice: 250,
  };

  const [selectedMethod, setSelectedMethod] = useState("stripe");
  const [formData, setFormData] = useState({
    fullName: "",
    email: "",
    phone: "",
    address: "",
  });
  const [submitted, setSubmitted] = useState(false);

  useEffect(() => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  }, []);

  const subtotal = useMemo(() => {
    return (orderData.items || []).reduce(
      (acc, item) => acc + item.quantity * item.price,
      0
    );
  }, [orderData.items]);

  const shipping = subtotal > 0 ? 15 : 0;
  const total = subtotal + shipping;

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    setSubmitted(true);
  };

  return (
    <ResponsiveLayout>
      <Container className="py-4">
        <div className="section-title text-center mb-4">
          <h2>Checkout & Payment</h2>
          <p>Choose your payment method and complete your order</p>
        </div>

        <Row className="g-4">
          <Col lg={7} md={12}>
            <Card className="shadow-sm border-0">
              <Card.Body>
                <h5 className="mb-3">Billing Details</h5>
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
                          placeholder="Enter your phone"
                          required
                        />
                      </Form.Group>
                    </Col>
                    <Col md={6}>
                      <Form.Group className="mb-3">
                        <Form.Label>Address</Form.Label>
                        <Form.Control
                          type="text"
                          name="address"
                          value={formData.address}
                          onChange={handleChange}
                          placeholder="Enter delivery address"
                          required
                        />
                      </Form.Group>
                    </Col>
                  </Row>

                  <h5 className="mt-4 mb-3">Payment Method</h5>
                  <div className="d-grid gap-2">
                    {paymentMethods.map((method) => (
                      <Card
                        key={method.id}
                        className={`border ${selectedMethod === method.id ? "border-primary" : ""}`}
                        style={{ cursor: "pointer" }}
                        onClick={() => setSelectedMethod(method.id)}
                      >
                        <Card.Body className="d-flex justify-content-between align-items-center">
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

                  <Button type="submit" className="btn site-btn w-100 mt-4">
                    <i className="fa fa-lock"></i> Pay Now
                  </Button>
                </Form>

                {submitted && (
                  <div className="alert alert-success mt-3 mb-0">
                    Your order is ready for <strong>{selectedMethod}</strong> payment.
                  </div>
                )}
              </Card.Body>
            </Card>
          </Col>

          <Col lg={5} md={12}>
            <Card className="shadow-sm border-0">
              <Card.Body>
                <div className="d-flex justify-content-between align-items-center mb-3">
                  <h5 className="mb-0">Order Summary</h5>
                  <Badge bg="secondary">{orderData.totalQuantity || 0} items</Badge>
                </div>

                <ListGroup variant="flush">
                  {(orderData.items || []).map((item) => (
                    <ListGroup.Item key={item.id} className="px-0">
                      <div className="d-flex justify-content-between align-items-center">
                        <div>
                          <strong>{item.name}</strong>
                          <div className="text-muted small">Qty: {item.quantity}</div>
                        </div>
                        <span>${item.quantity * item.price}</span>
                      </div>
                    </ListGroup.Item>
                  ))}
                </ListGroup>

                <div className="mt-3">
                  <div className="d-flex justify-content-between mb-2">
                    <span>Subtotal</span>
                    <span>${subtotal}</span>
                  </div>
                  <div className="d-flex justify-content-between mb-2">
                    <span>Shipping</span>
                    <span>${shipping}</span>
                  </div>
                  <hr />
                  <div className="d-flex justify-content-between fw-bold">
                    <span>Total</span>
                    <span>${total}</span>
                  </div>
                </div>

                <Button
                  variant="outline-secondary"
                  className="w-100 mt-4"
                  onClick={() => navigate(-1)}
                >
                  <i className="fa fa-arrow-left"></i> Back to Cart
                </Button>
              </Card.Body>
            </Card>
          </Col>
        </Row>
      </Container>
    </ResponsiveLayout>
  );
};

export default PaymentPage;
