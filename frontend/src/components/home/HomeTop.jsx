import React from "react";
import Container from "react-bootstrap/Container";
import Row from "react-bootstrap/Row";
import Col from "react-bootstrap/Col";
import MegaMenu from "./MegaMenu";
import HomeSlider from "./HomeSlider";
import useCategories from "../../hooks/useCategories";
import useSliders from "../../hooks/useSliders";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";

function HomeTop({ categories: propCategories, sliders: propSliders }) {
  const { data: hookCategories, loading: categoriesLoading } = useCategories('all');
  const { data: hookSliders, loading: slidersLoading } = useSliders('active');

  const categories = propCategories || hookCategories || [];
  const sliders = propSliders || hookSliders || [];
  const isLoading = (!propCategories && categoriesLoading) || (!propSliders && slidersLoading);

  return (
    <Container className="ecom-hero-section">
      <Row className="g-3">
        <Col lg={3} md={4} className="d-none d-md-block">
          {isLoading && categories.length === 0 ? (
            <Skeleton count={9} height={42} className="mb-2 rounded-3" />
          ) : (
            <MegaMenu data={categories} />
          )}
        </Col>
        <Col lg={9} md={8} sm={12}>
          {isLoading && sliders.length === 0 ? (
            <Skeleton height={410} className="w-100 rounded-4" />
          ) : (
            <HomeSlider data={sliders} />
          )}
        </Col>
      </Row>

      {/* E-Commerce Value / Trust Guarantee Strip */}
      <div className="ecom-trust-strip">
        <Row className="g-3">
          <Col lg={3} sm={6} xs={12}>
            <div className="ecom-trust-item">
              <div className="ecom-trust-icon">
                <i className="fa fa-shipping-fast"></i>
              </div>
              <div>
                <h6 className="ecom-trust-title">Free Fast Delivery</h6>
                <p className="ecom-trust-desc">On all orders over $99</p>
              </div>
            </div>
          </Col>
          <Col lg={3} sm={6} xs={12}>
            <div className="ecom-trust-item">
              <div className="ecom-trust-icon">
                <i className="fa fa-shield-alt"></i>
              </div>
              <div>
                <h6 className="ecom-trust-title">100% Secure Checkout</h6>
                <p className="ecom-trust-desc">Protected payments & data</p>
              </div>
            </div>
          </Col>
          <Col lg={3} sm={6} xs={12}>
            <div className="ecom-trust-item">
              <div className="ecom-trust-icon">
                <i className="fa fa-undo-alt"></i>
              </div>
              <div>
                <h6 className="ecom-trust-title">30-Day Easy Returns</h6>
                <p className="ecom-trust-desc">Hassle-free money back</p>
              </div>
            </div>
          </Col>
          <Col lg={3} sm={6} xs={12}>
            <div className="ecom-trust-item">
              <div className="ecom-trust-icon">
                <i className="fa fa-headset"></i>
              </div>
              <div>
                <h6 className="ecom-trust-title">24/7 Dedicated Support</h6>
                <p className="ecom-trust-desc">Expert help anytime you need</p>
              </div>
            </div>
          </Col>
        </Row>
      </div>
    </Container>
  );
}

export default React.memo(HomeTop);
