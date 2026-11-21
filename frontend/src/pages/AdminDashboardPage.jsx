import React, { useEffect, useState } from 'react';
import { Container, Row, Col, Card, Table, Badge, Button } from 'react-bootstrap';
import { useNavigate } from 'react-router-dom';
import apiClient from '../services/apiClient';
import NavMenuDesktop from '../components/common/NavMenuDesktop';
import FooterDesktop from '../components/common/FooterDesktop';
import Skeleton from 'react-loading-skeleton';
import 'react-loading-skeleton/dist/skeleton.css';

const AdminDashboardPage = () => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const navigate = useNavigate();

  const fetchDashboardData = async () => {
    try {
      setLoading(true);
      setError(null);
      const res = await apiClient.get('/admin/dashboard/stats');
      setData(res.data?.data || null);
    } catch (err) {
      console.error('Failed to load admin stats:', err);
      setError(err.response?.data?.message || 'Failed to connect to admin analytics API.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchDashboardData();
  }, []);

  const stats = data?.stats || {};
  const recentOrders = data?.recent_orders || [];
  const topProducts = data?.top_products || [];
  const chartData = data?.chart_data || { labels: [], data: [] };

  return (
    <>
      <NavMenuDesktop />
      <Container fluid className="px-4 py-4 bg-light" style={{ minHeight: '85vh' }}>
        {/* Header */}
        <div className="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h2 className="fw-bold mb-1">Store Performance Dashboard</h2>
            <p className="text-muted mb-0">Real-time revenue, order processing, and catalog metrics</p>
          </div>
          <div className="d-flex gap-2">
            <Button variant="outline-primary" size="sm" onClick={fetchDashboardData} disabled={loading}>
              <i className="fa fa-refresh me-1"></i> Refresh Metrics
            </Button>
            <Button variant="primary" size="sm" onClick={() => navigate('/orders')}>
              <i className="fa fa-list me-1"></i> View Orders
            </Button>
          </div>
        </div>

        {error && (
          <div className="alert alert-danger py-2 mb-4" role="alert">
            <i className="fa fa-exclamation-triangle me-2"></i> {error}
          </div>
        )}

        {/* 4 Primary KPI Cards */}
        <Row className="g-3 mb-4">
          {/* Revenue */}
          <Col xl={3} md={6}>
            <Card className="border-0 shadow-sm h-100" style={{ borderLeft: '4px solid #0d6efd' }}>
              <Card.Body>
                <div className="d-flex justify-content-between align-items-center mb-2">
                  <span className="text-muted text-uppercase fw-semibold small">Total Revenue</span>
                  <div className="p-2 bg-primary bg-opacity-10 rounded-circle text-primary">
                    <i className="fa fa-dollar fs-5"></i>
                  </div>
                </div>
                <h3 className="fw-bold mb-1">
                  {loading ? <Skeleton width={120} /> : `$${Number(stats.total_revenue || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`}
                </h3>
                <div className="small text-muted">
                  <span className={`fw-semibold ${stats.revenue_change_type === 'increase' ? 'text-success' : 'text-danger'}`}>
                    {stats.revenue_change_type === 'increase' ? '▲' : '▼'} {stats.revenue_change || 0}%
                  </span>{' '}
                  vs last month
                </div>
              </Card.Body>
            </Card>
          </Col>

          {/* Orders */}
          <Col xl={3} md={6}>
            <Card className="border-0 shadow-sm h-100" style={{ borderLeft: '4px solid #198754' }}>
              <Card.Body>
                <div className="d-flex justify-content-between align-items-center mb-2">
                  <span className="text-muted text-uppercase fw-semibold small">Total Orders</span>
                  <div className="p-2 bg-success bg-opacity-10 rounded-circle text-success">
                    <i className="fa fa-shopping-bag fs-5"></i>
                  </div>
                </div>
                <h3 className="fw-bold mb-1">
                  {loading ? <Skeleton width={90} /> : Number(stats.total_orders || 0).toLocaleString()}
                </h3>
                <div className="small text-muted">
                  <span className="badge bg-warning text-dark me-1">{stats.pending_orders || 0} Pending</span>
                  <span className={`fw-semibold ${stats.orders_change_type === 'increase' ? 'text-success' : 'text-danger'}`}>
                    {stats.orders_change_type === 'increase' ? '▲' : '▼'} {stats.orders_change || 0}%
                  </span>
                </div>
              </Card.Body>
            </Card>
          </Col>

          {/* Customers */}
          <Col xl={3} md={6}>
            <Card className="border-0 shadow-sm h-100" style={{ borderLeft: '4px solid #0dcaf0' }}>
              <Card.Body>
                <div className="d-flex justify-content-between align-items-center mb-2">
                  <span className="text-muted text-uppercase fw-semibold small">Active Customers</span>
                  <div className="p-2 bg-info bg-opacity-10 rounded-circle text-info">
                    <i className="fa fa-users fs-5"></i>
                  </div>
                </div>
                <h3 className="fw-bold mb-1">
                  {loading ? <Skeleton width={90} /> : Number(stats.total_customers || 0).toLocaleString()}
                </h3>
                <div className="small text-muted">
                  <span className="text-success fw-semibold">▲ {stats.customers_change || 0}%</span> new signups
                </div>
              </Card.Body>
            </Card>
          </Col>

          {/* Products */}
          <Col xl={3} md={6}>
            <Card className="border-0 shadow-sm h-100" style={{ borderLeft: '4px solid #ffc107' }}>
              <Card.Body>
                <div className="d-flex justify-content-between align-items-center mb-2">
                  <span className="text-muted text-uppercase fw-semibold small">Catalog Inventory</span>
                  <div className="p-2 bg-warning bg-opacity-10 rounded-circle text-warning">
                    <i className="fa fa-cubes fs-5"></i>
                  </div>
                </div>
                <h3 className="fw-bold mb-1">
                  {loading ? <Skeleton width={90} /> : Number(stats.total_products || 0).toLocaleString()}
                </h3>
                <div className="small text-muted">
                  <span className="badge bg-success me-1">{stats.active_products || 0} Live</span>
                  <span>Elasticsearch synced</span>
                </div>
              </Card.Body>
            </Card>
          </Col>
        </Row>

        {/* 30-Day Revenue Trend & Top Products */}
        <Row className="g-3 mb-4">
          <Col lg={8}>
            <Card className="border-0 shadow-sm h-100">
              <Card.Header className="bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 className="fw-bold mb-0">30-Day Revenue Velocity</h5>
                <small className="text-muted">Daily Settled Payments ($)</small>
              </Card.Header>
              <Card.Body>
                {loading ? (
                  <Skeleton height={200} />
                ) : (
                  <div>
                    <div className="d-flex align-items-end gap-1" style={{ height: '180px', overflowX: 'auto' }}>
                      {chartData.data.map((val, idx) => {
                        const maxVal = Math.max(...chartData.data, 1);
                        const pct = Math.max(5, Math.round((val / maxVal) * 100));
                        return (
                          <div
                            key={idx}
                            className="d-flex flex-column align-items-center flex-fill"
                            title={`${chartData.labels[idx]}: $${val}`}
                            style={{ minWidth: '18px' }}
                          >
                            <div
                              className="w-100 rounded-top bg-primary"
                              style={{ height: `${pct}%`, opacity: 0.85, transition: 'height 0.3s ease' }}
                            ></div>
                            <span style={{ fontSize: '9px', whiteSpace: 'nowrap' }} className="text-muted mt-1">
                              {idx % 5 === 0 ? chartData.labels[idx]?.split(' ')[1] : ''}
                            </span>
                          </div>
                        );
                      })}
                    </div>
                  </div>
                )}
              </Card.Body>
            </Card>
          </Col>

          {/* Top Products */}
          <Col lg={4}>
            <Card className="border-0 shadow-sm h-100">
              <Card.Header className="bg-white border-0 py-3">
                <h5 className="fw-bold mb-0">Top Performing Products</h5>
              </Card.Header>
              <Card.Body className="p-0">
                {loading ? (
                  <div className="p-3"><Skeleton count={4} height={40} className="mb-2" /></div>
                ) : topProducts.length === 0 ? (
                  <div className="text-center py-4 text-muted small">No completed sales recorded yet.</div>
                ) : (
                  <ul className="list-group list-group-flush">
                    {topProducts.map((p, idx) => (
                      <li key={p.id || idx} className="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div style={{ maxWidth: '70%' }}>
                          <h6 className="mb-0 text-truncate font-14 fw-semibold">{p.name}</h6>
                          <small className="text-muted">Stock: {p.stock} units</small>
                        </div>
                        <div className="text-end">
                          <span className="fw-bold text-success">${Number(p.total_revenue || 0).toLocaleString()}</span>
                          <div className="text-muted" style={{ fontSize: '11px' }}>{p.sales_count || 0} orders</div>
                        </div>
                      </li>
                    ))}
                  </ul>
                )}
              </Card.Body>
            </Card>
          </Col>
        </Row>

        {/* Recent Orders Data Table */}
        <Card className="border-0 shadow-sm mb-4">
          <Card.Header className="bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 className="fw-bold mb-0">Recent Order Activity</h5>
            <small className="text-muted">Showing latest 10 customer orders</small>
          </Card.Header>
          <Card.Body className="p-0">
            <div className="table-responsive">
              <Table hover className="align-middle mb-0">
                <thead className="table-light">
                  <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  {loading ? (
                    <tr>
                      <td colSpan={6} className="py-4"><Skeleton count={3} height={30} /></td>
                    </tr>
                  ) : recentOrders.length === 0 ? (
                    <tr>
                      <td colSpan={6} className="text-center py-4 text-muted">No recent orders found.</td>
                    </tr>
                  ) : (
                    recentOrders.map((order) => (
                      <tr key={order.id}>
                        <td className="fw-semibold text-primary">#{order.order_number}</td>
                        <td>
                          <div>{order.customer_name}</div>
                          <small className="text-muted">{order.customer_email}</small>
                        </td>
                        <td>{order.created_at}</td>
                        <td className="fw-bold">${Number(order.total || 0).toFixed(2)}</td>
                        <td>
                          <Badge bg={order.payment_status === 'paid' ? 'success' : 'warning'}>
                            {order.payment_status?.toUpperCase()}
                          </Badge>
                        </td>
                        <td>
                          <Badge bg={
                            order.status === 'delivered' ? 'success' :
                            order.status === 'shipped' ? 'info' :
                            order.status === 'processing' ? 'primary' :
                            order.status === 'cancelled' ? 'danger' : 'secondary'
                          }>
                            {order.status?.toUpperCase()}
                          </Badge>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </Table>
            </div>
          </Card.Body>
        </Card>
      </Container>
      <FooterDesktop />
    </>
  );
};

export default AdminDashboardPage;
