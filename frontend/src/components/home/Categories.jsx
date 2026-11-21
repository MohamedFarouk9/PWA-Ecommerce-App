import React from "react";
import Container from "react-bootstrap/Container";
import Row from "react-bootstrap/Row";
import Col from "react-bootstrap/Col";
import { Link } from "react-router-dom";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";
import useCategories from "../../hooks/useCategories";

function Categories({ categories: propCategories }) {
  const { data: hookCategories, loading, error } = useCategories('all');
  const categories = propCategories || hookCategories || [];

  const renderSkeletons = Array.from({ length: 6 }).map((_, index) => (
    <Col key={index} xl={2} lg={2} md={3} sm={4} xs={6}>
      <div className="ecom-category-card">
        <Skeleton circle width={84} height={84} className="mb-2" />
        <Skeleton width={90} height={16} />
      </div>
    </Col>
  ));

  if (!propCategories && loading && categories.length === 0) {
    return (
      <Container className="text-center" fluid={true}>
        <div className="section-title text-center mb-55">
          <h2>Categories</h2>
        </div>
        <Row>{renderSkeletons}</Row>
      </Container>
    );
  }

  if (error && categories.length === 0) {
    return (
      <Container className="text-center my-4">
        <p className="text-danger">{error}</p>
      </Container>
    );
  }

  return (
    <Container className="my-5">
      <div className="ecom-section-header">
        <div>
          <h2 className="ecom-section-title">Shop by Category</h2>
          <p className="ecom-section-subtitle">Explore our curated collections across top departments</p>
        </div>
      </div>
      <Row className="g-3">
        {categories.map((category, index) => (
          <Col key={category.id || index} xl={2} lg={2} md={3} sm={4} xs={6}>
            <Link
              className="ecom-category-card"
              to={`/${category.slug || category.category_name || ''}`}
            >
              <div className="ecom-category-img-wrap">
                <img
                  className="ecom-category-img"
                  src={
                    category.image ||
                    category.category_image ||
                    "https://images.unsplash.com/photo-1498049794561-7780e7231661?auto=format&fit=crop&w=600&q=80"
                  }
                  alt={category.name || category.category_name}
                  loading="lazy"
                  onError={(e) => {
                    e.target.onerror = null;
                    e.target.src = "https://images.unsplash.com/photo-1498049794561-7780e7231661?auto=format&fit=crop&w=600&q=80";
                  }}
                />
              </div>
              <h6 className="ecom-category-title">
                {category.name || category.category_name}
              </h6>
            </Link>
          </Col>
        ))}
      </Row>
    </Container>
  );
}

export default React.memo(Categories);
