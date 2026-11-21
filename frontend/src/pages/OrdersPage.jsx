import React, { useContext, useEffect, useState } from "react";
import { Container, Row, Col, Card, Badge, Table, Spinner, Alert } from "react-bootstrap";
import { Link, useNavigate } from "react-router-dom";
import { ResponsiveLayout } from "../layouts/ResponsiveLayout";
import { AuthContext } from "../utils/AuthContext";
import apiClient from "../services/apiClient";
import AppURL from "../utils/AppURL";

const statusBadgeVariant = {
  pending: "warning",
  processing: "primary",
  shipped: "info",
  delivered: "success",
  cancelled: "danger",
};

const paymentStatusVariant = {
  unpaid: "danger",
  paid: "success",
  refunded: "secondary",
};

const OrdersPage = () => {
  const { token } = useContext(AuthContext) || {};
  const navigate = useNavigate();

  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    window.scrollTo({ top: 0, behavior: "smooth" });

    if (!token) {
      navigate("/login");
      return;
    }

    const fetchOrders = async () => {
      try {
        setLoading(true);
        const response = await apiClient.get(AppURL.Orders);
        const ordersData = response?.data?.data || [];
        setOrders(Array.isArray(ordersData) ? ordersData : []);
      } catch (err) {
        console.error("Failed to load orders:", err);
        setError("Unable to load order history. Please try again later.");
      } finally {
        setLoading(false);
      }
    };

    fetchOrders();
  }, [token, navigate]);

  return (
    <ResponsiveLayout>
      <Container className="py-4">
        <div className="ecom-section-header">
          <div>
            <h2 className="ecom-section-title">My Orders</h2>
            <p className="ecom-section-subtitle">Track your order statuses, invoices, and shipment tracking</p>
          </div>
          <Link to="/" className="btn btn-outline-primary btn-sm">
            <i className="fa fa-shopping-bag me-1"></i> Continue Shopping
          </Link>
        </div>

        {error && <Alert variant="danger">{error}</Alert>}

        {loading ? (
          <div className="text-center py-5">
            <Spinner animation="border" variant="primary" />
            <p className="mt-2 text-muted">Retrieving your orders...</p>
          </div>
        ) : orders.length === 0 ? (
          <Card className="shadow-sm border-0 text-center py-5">
            <Card.Body>
              <i className="fa fa-box-open fa-4x text-muted mb-3"></i>
              <h4>No orders found</h4>
              <p className="text-muted">You have not placed any orders yet.</p>
              <Link to="/" className="btn btn-primary mt-2">
                Start Shopping
              </Link>
            </Card.Body>
          </Card>
        ) : (
          <Row className="g-4">
            {orders.map((order) => (
              <Col xs={12} key={order.id}>
                <Card className="shadow-sm border-0">
                  <Card.Header className="bg-white d-flex flex-wrap justify-content-between align-items-center py-3">
                    <div>
                      <span className="text-muted small">ORDER NUMBER:</span>
                      <strong className="ms-2">{order.order_number}</strong>
                      <span className="text-muted small ms-3">
                        PLACED: {new Date(order.created_at).toLocaleDateString()}
                      </span>
                    </div>
                    <div className="d-flex align-items-center gap-2 mt-2 mt-md-0">
                      <Badge bg={statusBadgeVariant[order.status] || "secondary"} className="text-uppercase px-2 py-1">
                        {order.status}
                      </Badge>
                      <Badge bg={paymentStatusVariant[order.payment_status] || "secondary"} className="text-uppercase px-2 py-1">
                        {order.payment_status}
                      </Badge>
                    </div>
                  </Card.Header>
                  <Card.Body>
                    <Table responsive hover className="align-middle mb-0">
                      <thead>
                        <tr>
                          <th>Item</th>
                          <th>Price</th>
                          <th>Qty</th>
                          <th className="text-end">Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        {(order.items || []).map((item) => (
                          <tr key={item.id}>
                            <td>
                              <div className="fw-semibold">
                                {item.product?.title || `Product #${item.product_id}`}
                              </div>
                            </td>
                            <td>${Number(item.price).toFixed(2)}</td>
                            <td>{item.quantity}</td>
                            <td className="text-end fw-bold">
                              ${Number(item.total).toFixed(2)}
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </Table>

                    <hr />

                    <div className="d-flex flex-wrap justify-content-between align-items-center">
                      <div className="small text-muted mb-2 mb-md-0">
                        <div>
                          <strong>Shipping:</strong> {order.shipping_address || "Standard Delivery"}
                        </div>
                        <div>
                          <strong>Payment Method:</strong> {order.payment_method?.toUpperCase() || "N/A"}
                        </div>
                      </div>
                      <div className="text-end">
                        <span className="text-muted small me-2">Order Total:</span>
                        <span className="fs-5 fw-bold text-primary">
                          ${Number(order.total_amount).toFixed(2)}
                        </span>
                      </div>
                    </div>
                  </Card.Body>
                </Card>
              </Col>
            ))}
          </Row>
        )}
      </Container>
    </ResponsiveLayout>
  );
};

export default OrdersPage;
