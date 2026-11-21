import React, { useEffect, useState } from "react";
import { Container, Row, Col, Form, Button } from "react-bootstrap";
import Forget from "../../assets/images/forget.jpg";
import ToastMessages from "../../toast-messages/toast";
import axios from "axios";
import AppURL from "../../utils/AppURL";
import { useParams } from "react-router-dom";
import { ResponsiveLayout } from "../../layouts/ResponsiveLayout";

const ResetPassword = () => {
  const { token } = useParams(); // Extract token from URL
  const [formData, setFormData] = useState({
    email: "",
    password: "",
    password_confirmation: "",
    token: "", // Store token in state
  });

  useEffect(() => {
    if (token) {
      setFormData((prevData) => ({
        ...prevData,
        token: token, // Automatically set token in form data
      }));
    }
  }, [token]);

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value, // Correct field names
    });
  };

  const validateForm = () => {
    const { email, password, password_confirmation } = formData;

    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      ToastMessages.showWarning("Please enter a valid email address.");
      return false;
    }

    // Validate password match
    if (password !== password_confirmation) {
      ToastMessages.showWarning("Passwords do not match");
      return false;
    }

    // Validate password length
    if (password.length < 6) {
      ToastMessages.showWarning("Password must be at least 6 characters long.");
      return false;
    }
    return true;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!validateForm()) {
      return; // Stop submission if validation fails
    }

    try {
      const response = await axios.post(AppURL.PasswordReset, formData);
      if (response.status === 200) {
        ToastMessages.showSuccess(response.data.message);
        setFormData({
          email: "",
          password: "",
          password_confirmation: "",
          token: "",
        }); // reset the form
      }
    } catch (error) {
      ToastMessages.showError(
        error.response?.data?.message || "Error resetting password"
      );
    }
  };

  return (
    <>
      <ResponsiveLayout>
        <Container>
          <Row className="p-2">
            <Col
              className="shadow-sm bg-white mt-2"
              md={12}
              lg={12}
              sm={12}
              xs={12}
            >
              <Row className="text-center">
                <Col
                  className="d-flex justify-content-center"
                  md={6}
                  lg={6}
                  sm={12}
                  xs={12}
                >
                  <Form className="onboardForm" onSubmit={handleSubmit}>
                    <h4 className="section-title-login"> RESET PASSWORD </h4>

                    <input
                      className="form-control m-2"
                      type="email"
                      name="email"
                      placeholder="Enter Your Email"
                      value={formData.email}
                      onChange={handleChange}
                      required
                    />
                    <input
                      className="form-control m-2"
                      type="password"
                      name="password"
                      placeholder="Your New Password"
                      value={formData.password}
                      onChange={handleChange}
                      required
                    />
                    <input
                      className="form-control m-2"
                      type="password"
                      name="password_confirmation"
                      placeholder="Confirm Your Password"
                      value={formData.password_confirmation}
                      onChange={handleChange}
                      required
                    />
                    <Button
                      className="btn btn-block m-2 site-btn-login"
                      type="submit"
                    >
                      {" "}
                      Reset Password{" "}
                    </Button>
                  </Form>
                </Col>
                <Col className="p-0 Desktop m-0" md={6} lg={6} sm={6} xs={6}>
                  <img
                    className="onboardBanner"
                    src={Forget}
                    alt="Reset Password"
                  />
                </Col>
              </Row>
            </Col>
          </Row>
        </Container>
      </ResponsiveLayout>
    </>
  );
};

export default ResetPassword;
