import React, { useState } from "react";
import { Container, Row, Col, Form, Button } from "react-bootstrap";
import { Link } from "react-router-dom";
import ToastMessages from "../../toast-messages/toast";
import axios from "axios";
import AppURL from "../../utils/AppURL";
import { ResponsiveLayout } from "../../layouts/ResponsiveLayout";

const ForgetPassword = () => {
  const [email, setEmail] = useState("");
  const [submitting, setSubmitting] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);

    try {
      const response = await axios.post(AppURL.ForgetPassword, { email });
      if (response.status === 200) {
        ToastMessages.showSuccess(response.data.message);
      }
    } catch (err) {
      ToastMessages.showWarning(
        err.response?.data?.message || "Unable to send reset link."
      );
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
                    Password Recovery
                  </span>
                  <h3 className="section-title-login">Forgot Your Password?</h3>
                  <p className="section-sub-title mb-0">
                    Enter your registered email address and we'll send you a password reset link.
                  </p>
                </div>

                <div className="mb-3 text-start">
                  <label className="ecom-form-label">Email Address</label>
                  <input
                    className="form-control ecom-form-control"
                    type="email"
                    placeholder="name@example.com"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    required
                  />
                </div>

                <Button
                  className="site-btn-login w-100 mt-2"
                  type="submit"
                  disabled={submitting}
                >
                  <i className="fa fa-paper-plane"></i>
                  <span>{submitting ? "Sending Link..." : "Send Reset Link"}</span>
                </Button>

                <div className="text-center mt-4 pt-3 border-top small text-muted">
                  Remembered your password?{" "}
                  <Link to="/login" className="text-primary fw-bold text-decoration-none">
                    Back to Sign In
                  </Link>
                </div>
              </Form>
            </Col>

            <Col lg={6} md={6} className="d-none d-md-flex ecom-auth-visual-col">
              <div className="pe-lg-4">
                <h2 className="fw-bold mb-3">Quick & Secure Account Recovery</h2>
                <p className="text-light opacity-75 mb-0">
                  We protect your account with encrypted reset tokens. Check your inbox (and spam folder) after submitting.
                </p>
              </div>
            </Col>
          </Row>
        </div>
      </Container>
    </ResponsiveLayout>
  );
};

export default ForgetPassword;
