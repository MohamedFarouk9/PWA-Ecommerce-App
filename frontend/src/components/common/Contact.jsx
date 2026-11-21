import React, { useEffect, useState } from "react";
import { NameRegx } from "../../validation/Validation";
import axios from "axios";
import ToastMessage from "../../toast-messages/toast";
import { Container, Row, Col, Form, Button } from "react-bootstrap";
import AppURL from "../../utils/AppURL";
import { ResponsiveLayout } from "../../layouts/ResponsiveLayout";

const Contact = () => {
  const [formData, setFormData] = useState({
    name: "",
    email: "",
    message: "",
  });
  const [isSubmitting, setIsSubmitting] = useState(false);

  useEffect(() => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  }, []);

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    const { name, email, message } = formData;

    if (message.trim().length === 0) {
      ToastMessage.showError("Please write your message");
    } else if (name.trim().length === 0) {
      ToastMessage.showError("Please write your name");
    } else if (email.trim().length === 0) {
      ToastMessage.showError("Please enter your email");
    } else if (!NameRegx.test(name)) {
      ToastMessage.showError("Invalid name. Please enter a valid name.");
    } else {
      setIsSubmitting(true);
      try {
        const requestData = new FormData();
        requestData.append("name", name);
        requestData.append("email", email);
        requestData.append("message", message);
        const response = await axios.post(AppURL.PostContact, requestData);

        if (response.status === 201 || response.status === 200) {
          ToastMessage.showSuccess("Your message was sent! Our team will reply shortly.");
          setFormData({ name: "", email: "", message: "" });
        } else {
          ToastMessage.showError("Error occurred while sending the message");
        }
      } catch (error) {
        ToastMessage.showError(
          "Error: " + (error.response?.data?.message || error.message)
        );
      } finally {
        setIsSubmitting(false);
      }
    }
  };

  return (
    <ResponsiveLayout>
      <Container className="py-4">
        <div className="ecom-section-header">
          <div>
            <h2 className="ecom-section-title">Contact Customer Support</h2>
            <p className="ecom-section-subtitle">
              Have a question about an order or product? We're here 24/7.
            </p>
          </div>
        </div>

        <div className="ecom-auth-card mt-0">
          <Row className="g-0">
            <Col lg={6} md={6} sm={12} className="ecom-auth-form-col">
              <Form className="onboardForm" onSubmit={handleSubmit}>
                <h4 className="fw-bold mb-2 text-start">Send Us a Message</h4>
                <p className="text-muted small mb-4 text-start">
                  Fill out the form below and our support specialists will get back to you within 24 hours.
                </p>

                <div className="mb-3 text-start">
                  <label className="ecom-form-label">Your Name</label>
                  <input
                    className="form-control ecom-form-control"
                    type="text"
                    placeholder="John Doe"
                    name="name"
                    value={formData.name}
                    onChange={handleChange}
                  />
                </div>

                <div className="mb-3 text-start">
                  <label className="ecom-form-label">Email Address</label>
                  <input
                    className="form-control ecom-form-control"
                    type="email"
                    placeholder="name@example.com"
                    name="email"
                    value={formData.email}
                    onChange={handleChange}
                  />
                </div>

                <div className="mb-3 text-start">
                  <label className="ecom-form-label">How can we help?</label>
                  <Form.Control
                    className="ecom-form-control"
                    as="textarea"
                    name="message"
                    value={formData.message}
                    placeholder="Write your question or order number..."
                    rows={4}
                    onChange={handleChange}
                  />
                </div>

                <Button
                  className="site-btn-login w-100 mt-2"
                  id="sendBtn"
                  disabled={isSubmitting}
                  type="submit"
                >
                  <i className="fa fa-paper-plane"></i>
                  <span>{isSubmitting ? "Sending..." : "Send Message"}</span>
                </Button>
              </Form>
            </Col>

            <Col lg={6} md={6} sm={12} className="bg-light p-4 d-flex flex-column justify-content-between">
              <div className="mb-3">
                <h5 className="fw-bold mb-3">Direct Support Channels</h5>
                <div className="d-flex align-items-center gap-3 mb-3">
                  <div className="ecom-trust-icon">
                    <i className="fa fa-map-marker-alt"></i>
                  </div>
                  <div>
                    <div className="fw-bold small">Headquarters</div>
                    <div className="text-muted small">
                      1635 Franklin Street, Montgomery, AL 36104
                    </div>
                  </div>
                </div>
                <div className="d-flex align-items-center gap-3 mb-3">
                  <div className="ecom-trust-icon">
                    <i className="fa fa-envelope"></i>
                  </div>
                  <div>
                    <div className="fw-bold small">Support Email</div>
                    <div className="text-muted small">Support@Mohamed.com</div>
                  </div>
                </div>
              </div>

              <div className="rounded-3 overflow-hidden border" style={{ height: 300 }}>
                <iframe
                  src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d162771.1102477064!2d-74.10054944459704!3d40.70681480276415!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2sbd!4v1627241390779!5m2!1sen!2sbd"
                  width="100%"
                  height="100%"
                  title="Store Location Map"
                  style={{ border: 0 }}
                  allowFullScreen=""
                  loading="lazy"
                ></iframe>
              </div>
            </Col>
          </Row>
        </div>
      </Container>
    </ResponsiveLayout>
  );
};

export default Contact;
