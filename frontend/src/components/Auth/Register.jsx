import React, { useContext, useState } from "react";
import { Container, Row, Col, Form, Button } from "react-bootstrap";
import { Link, useNavigate } from "react-router-dom";
import ToastMessages from "../../toast-messages/toast";
import apiClient from "../../services/apiClient";
import AppURL from "../../utils/AppURL";
import { AuthContext } from "../../utils/AuthContext";
import { ResponsiveLayout } from "../../layouts/ResponsiveLayout";

const Register = () => {
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const { login } = useContext(AuthContext);

  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    let hasErrors = false;

    if (name.trim().length < 3) {
      ToastMessages.showInfo("Name must be at least 3 characters long");
      hasErrors = true;
    }

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
      ToastMessages.showInfo("Enter a valid email address");
      hasErrors = true;
    }

    if (password.length < 6) {
      ToastMessages.showWarning("Password must be at least 6 characters");
      hasErrors = true;
    }

    if (password !== confirmPassword) {
      ToastMessages.showWarning("Passwords do not match");
      hasErrors = true;
    }
    if (hasErrors) return;

    const data = {
      name,
      email,
      password,
      password_confirmation: confirmPassword,
    };

    try {
      setSubmitting(true);
      const response = await apiClient.post(AppURL.UserRegister, data);
      if (response.data?.token) {
        login(response.data.token, response.data.user);
        ToastMessages.showSuccess("Account created! Welcome aboard.");
        navigate("/profile");
      } else {
        ToastMessages.showSuccess("Registration successful! Please sign in.");
        navigate("/login");
      }
    } catch (error) {
      const errMsg =
        error.response?.data?.errors
          ? Object.values(error.response.data.errors).flat().join(" ")
          : error.response?.data?.message || "Registration failed";
      ToastMessages.showError(errMsg);
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
              <Form className="onboardForm" onSubmit={handleSubmit}>
                <div className="mb-4 text-start">
                  <span className="badge bg-primary bg-opacity-10 text-primary mb-2 px-3 py-1 rounded-pill">
                    Get Started
                  </span>
                  <h3 className="section-title-login">Create Your Account</h3>
                  <p className="section-sub-title mb-0">
                    Join thousands of shoppers enjoying fast checkout and exclusive deals.
                  </p>
                </div>

                <div className="mb-3 text-start">
                  <label className="ecom-form-label">Full Name</label>
                  <input
                    className="form-control ecom-form-control"
                    type="text"
                    name="name"
                    placeholder="John Doe"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    required
                  />
                </div>

                <div className="mb-3 text-start">
                  <label className="ecom-form-label">Email Address</label>
                  <input
                    className="form-control ecom-form-control"
                    type="email"
                    name="email"
                    placeholder="name@example.com"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    required
                  />
                </div>

                <Row className="g-2 mb-3 text-start">
                  <Col sm={6}>
                    <label className="ecom-form-label">Password</label>
                    <input
                      className="form-control ecom-form-control"
                      type="password"
                      name="password"
                      placeholder="••••••••"
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      required
                    />
                  </Col>
                  <Col sm={6}>
                    <label className="ecom-form-label">Confirm Password</label>
                    <input
                      className="form-control ecom-form-control"
                      type="password"
                      name="confirmPassword"
                      placeholder="••••••••"
                      value={confirmPassword}
                      onChange={(e) => setConfirmPassword(e.target.value)}
                      required
                    />
                  </Col>
                </Row>

                <Button
                  className="site-btn-login w-100 mt-2"
                  type="submit"
                  disabled={submitting}
                >
                  <i className="fa fa-user-plus"></i>
                  <span>{submitting ? "Creating Account..." : "Create Account"}</span>
                </Button>

                <div className="text-center mt-4 pt-3 border-top small text-muted">
                  Already have an account?{" "}
                  <Link to="/login" className="text-primary fw-bold text-decoration-none">
                    Sign In
                  </Link>
                </div>
              </Form>
            </Col>

            <Col lg={6} md={6} className="d-none d-md-flex ecom-auth-visual-col">
              <div className="pe-lg-4">
                <span className="badge bg-white bg-opacity-10 text-white mb-3 px-3 py-2 rounded-pill">
                  <i className="fa fa-star me-1 text-warning"></i> Welcome Perks
                </span>
                <h2 className="fw-bold mb-3">Unlock Instant Member Rewards</h2>
                <p className="text-light opacity-75 mb-4">
                  Create your free account today and enjoy 20% off your first order with code <strong>SAVE20</strong>.
                </p>
                <div className="d-flex flex-column gap-3">
                  <div className="d-flex align-items-center gap-3">
                    <div className="rounded-circle bg-white bg-opacity-10 p-2 d-inline-flex">
                      <i className="fa fa-check text-info"></i>
                    </div>
                    <span>One-click reorder & saved addresses</span>
                  </div>
                  <div className="d-flex align-items-center gap-3">
                    <div className="rounded-circle bg-white bg-opacity-10 p-2 d-inline-flex">
                      <i className="fa fa-bell text-info"></i>
                    </div>
                    <span>Early access to seasonal flash sales</span>
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

export default Register;
