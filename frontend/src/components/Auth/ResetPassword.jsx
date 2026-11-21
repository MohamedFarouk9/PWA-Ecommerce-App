import React, { useEffect, useState } from "react";
import { Container, Row, Col, Form, Button } from "react-bootstrap";
import { Link, useParams } from "react-router-dom";
import ToastMessages from "../../toast-messages/toast";
import axios from "axios";
import AppURL from "../../utils/AppURL";
import { ResponsiveLayout } from "../../layouts/ResponsiveLayout";

const ResetPassword = () => {
  const { token } = useParams();
  const [formData, setFormData] = useState({
    email: "",
    password: "",
    password_confirmation: "",
    token: "",
  });

  useEffect(() => {
    if (token) {
      setFormData((prevData) => ({
        ...prevData,
        token: token,
      }));
    }
  }, [token]);

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
  };

  const validateForm = () => {
    const { email, password, password_confirmation } = formData;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      ToastMessages.showWarning("Please enter a valid email address.");
      return false;
    }
    if (password !== password_confirmation) {
      ToastMessages.showWarning("Passwords do not match");
      return false;
    }
    if (password.length < 6) {
      ToastMessages.showWarning("Password must be at least 6 characters long.");
      return false;
    }
    return true;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!validateForm()) return;

    try {
      const response = await axios.post(AppURL.PasswordReset, formData);
      if (response.status === 200) {
        ToastMessages.showSuccess(response.data.message);
        setFormData({
          email: "",
          password: "",
          password_confirmation: "",
          token: "",
        });
      }
    } catch (error) {
      ToastMessages.showError(
        error.response?.data?.message || "Error resetting password"
      );
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
                    Security Update
                  </span>
                  <h3 className="section-title-login">Set a New Password</h3>
                  <p className="section-sub-title mb-0">
                    Choose a strong password of at least 6 characters.
                  </p>
                </div>

                <div className="mb-3 text-start">
                  <label className="ecom-form-label">Email Address</label>
                  <input
                    className="form-control ecom-form-control"
                    type="email"
                    name="email"
                    placeholder="name@example.com"
                    value={formData.email}
                    onChange={handleChange}
                    required
                  />
                </div>

                <div className="mb-3 text-start">
                  <label className="ecom-form-label">New Password</label>
                  <input
                    className="form-control ecom-form-control"
                    type="password"
                    name="password"
                    placeholder="••••••••"
                    value={formData.password}
                    onChange={handleChange}
                    required
                  />
                </div>

                <div className="mb-3 text-start">
                  <label className="ecom-form-label">Confirm Password</label>
                  <input
                    className="form-control ecom-form-control"
                    type="password"
                    name="password_confirmation"
                    placeholder="••••••••"
                    value={formData.password_confirmation}
                    onChange={handleChange}
                    required
                  />
                </div>

                <Button className="site-btn-login w-100 mt-2" type="submit">
                  <i className="fa fa-key"></i>
                  <span>Update Password</span>
                </Button>

                <div className="text-center mt-4 pt-3 border-top small text-muted">
                  <Link to="/login" className="text-primary fw-bold text-decoration-none">
                    Return to Sign In
                  </Link>
                </div>
              </Form>
            </Col>

            <Col lg={6} md={6} className="d-none d-md-flex ecom-auth-visual-col">
              <div className="pe-lg-4">
                <h2 className="fw-bold mb-3">Keep Your Account Protected</h2>
                <p className="text-light opacity-75 mb-0">
                  Once updated, you can immediately sign in with your new credentials.
                </p>
              </div>
            </Col>
          </Row>
        </div>
      </Container>
    </ResponsiveLayout>
  );
};

export default ResetPassword;
