import React from "react";
import { Container, Row, Col, Card, Button, Badge } from "react-bootstrap";
import { Link, useNavigate } from "react-router-dom";

const Profile = ({ userData, logout }) => {
  const navigate = useNavigate();
  const profileUser = userData?.user || userData || {};

  const handleLogout = () => {
    logout();
    navigate("/");
  };

  const initials = (profileUser.name || "U")
    .split(" ")
    .map((n) => n[0])
    .join("")
    .toUpperCase()
    .slice(0, 2);

  return (
    <Container className="py-4">
      <div className="ecom-section-header">
        <div>
          <h2 className="ecom-section-title">My Account</h2>
          <p className="ecom-section-subtitle">
            Manage your personal profile, orders, and preferences
          </p>
        </div>
      </div>

      <Row className="g-4">
        <Col lg={4} md={5}>
          <Card className="text-center p-4 h-100">
            <Card.Body className="d-flex flex-column align-items-center justify-content-center">
              <div
                className="rounded-circle d-flex align-items-center justify-content-center fw-bold mb-3"
                style={{
                  width: 84,
                  height: 84,
                  fontSize: 28,
                  background: "var(--ecom-primary-soft)",
                  color: "var(--ecom-primary)",
                }}
              >
                {initials}
              </div>
              <h5 className="fw-bold mb-1">{profileUser.name || "Customer"}</h5>
              <p className="text-muted small mb-2">{profileUser.email}</p>
              <Badge bg="success" pill className="px-3 py-1 mb-4">
                Verified Customer
              </Badge>

              <div className="d-grid gap-2 w-100">
                <Button
                  className="site-btn"
                  onClick={() => navigate("/orders")}
                >
                  <i className="fa fa-box"></i> View My Orders
                </Button>
                <Link
                  to="/favourite"
                  className="btn btn-outline-primary"
                >
                  <i className="fa fa-heart me-1"></i> Saved Wishlist
                </Link>
                <Button
                  variant="outline-danger"
                  onClick={handleLogout}
                >
                  <i className="fa fa-sign-out-alt me-1"></i> Sign Out
                </Button>
              </div>
            </Card.Body>
          </Card>
        </Col>

        <Col lg={8} md={7}>
          <Card className="p-3 h-100">
            <Card.Body>
              <h5 className="fw-bold mb-4 pb-2 border-bottom">
                Account Details
              </h5>
              <Row className="g-3">
                <Col sm={6}>
                  <div className="p-3 rounded-3 bg-light">
                    <div className="small text-muted mb-1">Full Name</div>
                    <div className="fw-semibold">
                      {profileUser.name || "Not provided"}
                    </div>
                  </div>
                </Col>
                <Col sm={6}>
                  <div className="p-3 rounded-3 bg-light">
                    <div className="small text-muted mb-1">Email Address</div>
                    <div className="fw-semibold">
                      {profileUser.email || "Not provided"}
                    </div>
                  </div>
                </Col>
                <Col sm={6}>
                  <div className="p-3 rounded-3 bg-light">
                    <div className="small text-muted mb-1">Phone Number</div>
                    <div className="fw-semibold">
                      {profileUser.phone || "Not provided"}
                    </div>
                  </div>
                </Col>
                <Col sm={6}>
                  <div className="p-3 rounded-3 bg-light">
                    <div className="small text-muted mb-1">Shipping Address</div>
                    <div className="fw-semibold">
                      {profileUser.address || "Not provided"}
                    </div>
                  </div>
                </Col>
                <Col xs={12}>
                  <div className="p-3 rounded-3 bg-light">
                    <div className="small text-muted mb-1">Bio / Notes</div>
                    <div className="fw-semibold">
                      {profileUser.bio || "No additional notes added."}
                    </div>
                  </div>
                </Col>
              </Row>
            </Card.Body>
          </Card>
        </Col>
      </Row>
    </Container>
  );
};

export default Profile;
