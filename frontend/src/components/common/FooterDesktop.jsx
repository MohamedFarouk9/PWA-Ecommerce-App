import React from "react";
import { Col, Container, Row } from "react-bootstrap";
import { Link } from "react-router-dom";
import Apple from "../../assets/images/apple.png";
import Google from "../../assets/images/google.png";
import { useSiteSettings } from "../../hooks/useSiteSettings";
import { safeParse } from "../../utils/htmlParser";

const FooterDesktop = () => {
  const { settings, isLoading } = useSiteSettings();

  return (
    <footer className="ecom-footer">
      <Container>
        <Row className="g-4">
          <Col lg={4} md={6} sm={12}>
            <h5 className="ecom-footer-title">About Our Store</h5>
            <p className="text-secondary mb-3" style={{ lineHeight: 1.7 }}>
              Your destination for flagship electronics, modern fashion, and everyday essentials with fast delivery and 30-day buyer protection.
            </p>
            {!isLoading && (
              <div className="small text-secondary mb-3">
                {safeParse(settings.address)}
              </div>
            )}
            <div className="ecom-social-list">
              <a href={settings.facebookLink || "#"} target="_blank" rel="noreferrer" className="ecom-social-btn">
                <i className="fab fa-facebook-f"></i>
              </a>
              <a href={settings.instagramLink || "#"} target="_blank" rel="noreferrer" className="ecom-social-btn">
                <i className="fab fa-instagram"></i>
              </a>
              <a href={settings.twitterLink || "#"} target="_blank" rel="noreferrer" className="ecom-social-btn">
                <i className="fab fa-twitter"></i>
              </a>
            </div>
          </Col>

          <Col lg={2} md={6} sm={6}>
            <h5 className="ecom-footer-title">Company</h5>
            <ul className="ecom-footer-links">
              <li><Link to="/content/about">About Us</Link></li>
              <li><Link to="/">Store Directory</Link></li>
              <li><Link to="/contact">Contact Support</Link></li>
              <li><Link to="/orders">Track My Order</Link></li>
            </ul>
          </Col>

          <Col lg={3} md={6} sm={6}>
            <h5 className="ecom-footer-title">Customer Care</h5>
            <ul className="ecom-footer-links">
              <li><Link to="/content/purchase_guide">How to Order</Link></li>
              <li><Link to="/content/privacy">Privacy Policy</Link></li>
              <li><Link to="/content/refund">Returns & Refunds</Link></li>
              <li><Link to="/favourite">Saved Wishlist</Link></li>
            </ul>
          </Col>

          <Col lg={3} md={6} sm={12}>
            <h5 className="ecom-footer-title">Shop On The Go</h5>
            <p className="text-secondary small mb-3">
              Download our mobile app for exclusive app-only flash deals and real-time order notifications.
            </p>
            <div className="d-flex flex-column gap-2 align-items-start">
              <a href={settings.androidAppLink || "#"} target="_blank" rel="noopener noreferrer">
                <img src={Google} alt="Google Play" style={{ height: "40px", borderRadius: "8px" }} />
              </a>
              <a href={settings.iosAppLink || "#"} target="_blank" rel="noopener noreferrer">
                <img src={Apple} alt="App Store" style={{ height: "40px", borderRadius: "8px" }} />
              </a>
            </div>
            <div id="google_translate_element" className="mt-3"></div>
          </Col>
        </Row>
      </Container>

      <div className="ecom-footer-bottom">
        <Container className="d-flex flex-wrap justify-content-between align-items-center">
          <div>
            {safeParse(settings.copyrightText) || `© ${new Date().getFullYear()} All Rights Reserved.`}
          </div>
          <div className="d-flex gap-3">
            <span><i className="fab fa-cc-visa fa-lg"></i></span>
            <span><i className="fab fa-cc-mastercard fa-lg"></i></span>
            <span><i className="fab fa-cc-paypal fa-lg"></i></span>
            <span><i className="fab fa-cc-apple-pay fa-lg"></i></span>
          </div>
        </Container>
      </div>
    </footer>
  );
};

export default FooterDesktop;
