import React, { useContext, useState } from "react";
import { Container, Row, Col, Form, Button } from "react-bootstrap";
import { Link, useNavigate } from "react-router-dom";
import apiClient from "../../services/apiClient";
import AppURL from "../../utils/AppURL";
import ToastMessages from "../../toast-messages/toast";
import { AuthContext } from "../../utils/AuthContext";
import { ResponsiveLayout } from "../../layouts/ResponsiveLayout";

const UserLogin = () => {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const { login } = useContext(AuthContext);
  const [message, setMessage] = useState("");
  const navigate = useNavigate();

  const formSubmit = async (e) => {
    e.preventDefault();
    setMessage("");
    setSubmitting(true);

    try {
      const response = await apiClient.post(AppURL.UserLogin, { email, password });
      if (response.status === 200 && response.data?.token) {
        login(response.data.token, response.data.user);
        ToastMessages.showSuccess("Login successful!");
        navigate("/profile");
      }
    } catch (error) {
      const errMsg =
        error.response?.data?.errors
          ? Object.values(error.response.data.errors).flat().join(" ")
          : error.response?.data?.message || "Login failed. Please check your credentials.";
      ToastMessages.showWarning(errMsg);
      setMessage(errMsg);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <ResponsiveLayout>
      <Container className="py-4">
        <div className="ecom-auth-card">
          <Row className="g-0">
            <Col lg={6} md={6} sm={12} className="ecom-auth-form-col">
              <Form className="onboardForm" onSubmit={formSubmit}>
                <div className="mb-4 text-start">
                  <span className="badge bg-primary bg-opacity-10 text-primary mb-2 px-3 py-1 rounded-pill">
                    Welcome Back
                  </span>
                  <h3 className="section-title-login">Sign In to Your Account</h3>
                  <p className="section-sub-title mb-0">
                    Access your orders, saved wishlist, and faster checkout.
                  </p>
                </div>

                <div className="mb-3 text-start">
                  <label className="ecom-form-label">Email Address</label>
                  <input
                    className="form-control ecom-form-control"
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder="name@example.com"
                    required
                  />
                </div>

                <div className="mb-3 text-start">
                  <div className="d-flex justify-content-between align-items-center">
                    <label className="ecom-form-label mb-1">Password</label>
                    <Link
                      to="/forgot-password"
                      className="small text-primary text-decoration-none fw-semibold"
                    >
                      Forgot password?
                    </Link>
                  </div>
                  <input
                    className="form-control ecom-form-control"
                    type="password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    placeholder="Enter your password"
                    required
                  />
                </div>

                <Button
                  type="submit"
                  disabled={submitting}
                  className="site-btn-login w-100 mt-2"
                >
                  <i className="fa fa-sign-in-alt"></i>
                  <span>{submitting ? "Signing in..." : "Sign In"}</span>
                </Button>

                {message && (
                  <p className="text-center text-danger small mt-3 mb-0">{message}</p>
                )}

                <div className="text-center mt-4 pt-3 border-top small text-muted">
                  Don't have an account yet?{" "}
                  <Link to="/register" className="text-primary fw-bold text-decoration-none">
                    Create an Account
                  </Link>
                </div>
              </Form>
            </Col>

            <Col lg={6} md={6} className="d-none d-md-flex ecom-auth-visual-col">
              <div className="pe-lg-4">
                <span className="badge bg-white bg-opacity-10 text-white mb-3 px-3 py-2 rounded-pill">
                  <i className="fa fa-shield-alt me-1 text-warning"></i> Member Benefits
                </span>
                <h2 className="fw-bold mb-3">Shop Smarter with Personalized Deals</h2>
                <p className="text-light opacity-75 mb-4">
                  Track deliveries in real time, save items across all your devices, and unlock member-only coupons.
                </p>
                <div className="d-flex flex-column gap-3">
                  <div className="d-flex align-items-center gap-3">
                    <div className="rounded-circle bg-white bg-opacity-10 p-2 d-inline-flex">
                      <i className="fa fa-shipping-fast text-info"></i>
                    </div>
                    <span>Free express delivery on qualifying orders</span>
                  </div>
                  <div className="d-flex align-items-center gap-3">
                    <div className="rounded-circle bg-white bg-opacity-10 p-2 d-inline-flex">
                      <i className="fa fa-undo text-info"></i>
                    </div>
                    <span>Instant 30-day returns & order protection</span>
                  </div>
                </div>
              </div>
            </Col>
          </Row>
        </div>
      </Container>
    </ResponsiveLayout>
  );
};

export default UserLogin;
