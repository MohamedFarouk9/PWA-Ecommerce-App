import React from "react";
import { Col, Container, Row, Card } from "react-bootstrap";
import { Link } from "react-router-dom";
import Breadcrumb from "react-bootstrap/Breadcrumb";
import ProductCard from "../Products/ProductCard";
import { ProductSkeleton } from "../ui/LoadingStates";

const SearchList = ({ ProductData = [], SearchKey, isLoading }) => {
  const items = Array.isArray(ProductData)
    ? ProductData
    : ProductData?.data || ProductData?.products || [];

  return (
    <Container className="py-3">
      <div className="ecom-breadcrumb-bar">
        <Breadcrumb>
          <Breadcrumb.Item linkAs={Link} linkProps={{ to: "/" }}>
            Home
          </Breadcrumb.Item>
          <Breadcrumb.Item active>Search: "{SearchKey}"</Breadcrumb.Item>
        </Breadcrumb>
      </div>

      <div className="ecom-section-header">
        <div>
          <h2 className="ecom-section-title">Search Results for "{SearchKey}"</h2>
          <p className="ecom-section-subtitle">
            Found {items.length} matching products
          </p>
        </div>
      </div>

      <Row className="g-2">
        {isLoading ? (
          <ProductSkeleton count={8} />
        ) : items.length === 0 ? (
          <Col xs={12}>
            <Card className="text-center py-5">
              <Card.Body>
                <i className="fa fa-search fa-3x text-muted mb-3"></i>
                <h5 className="fw-bold">No products found for "{SearchKey}"</h5>
                <p className="text-muted mb-3">
                  Try searching with a different keyword or browse our categories.
                </p>
                <Link to="/" className="site-btn text-decoration-none">
                  Back to Home
                </Link>
              </Card.Body>
            </Card>
          </Col>
        ) : (
          items.map((product, index) => (
            <ProductCard key={product.id || index} product={product} />
          ))
        )}
      </Row>
    </Container>
  );
};

export default SearchList;
