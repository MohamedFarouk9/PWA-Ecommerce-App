import React from "react";
import Container from "react-bootstrap/Container";
import Row from "react-bootstrap/Row";
import Col from "react-bootstrap/Col";
import Card from "react-bootstrap/Card";
import { Link } from "react-router-dom";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";
import useCategories from "../../hooks/useCategories";

function Categories({ categories: propCategories }) {
  const { data: hookCategories, loading, error } = useCategories('all');
  const categories = propCategories || hookCategories || [];

  const renderSkeletons = Array.from({ length: 12 }).map((_, index) => (
    <Col key={index} className="p-0" xl={2} lg={2} md={2} sm={6} xs={6}>
      <Card className="h-100 w-100 text-center">
        <Card.Body>
          <Skeleton height={120} />
          <Skeleton width={`60%`} style={{ marginTop: 10 }} />
        </Card.Body>
      </Card>
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
    <Container className="text-center" fluid={true}>
      <div className="section-title text-center mb-55">
        <h2>Categories</h2>
        <p>Some Of Our Exclusive Collections, You Need To See</p>
      </div>
      <Row>
        {categories.map((category, index) => (
          <Col key={category.id || index} className="p-0" xl={2} lg={2} md={2} sm={6} xs={6}>
            <Link className="text-link" to={`/${category.slug || category.category_name || ''}`}>
              <Card className="h-100 w-100 text-center">
                <Card.Body>
                  <Card.Img
                    className="center"
                    src={category.image || category.category_image || "https://img.icons8.com/?size=100&id=20822&format=png"}
                    alt={category.name || category.category_name}
                    loading="lazy"
                  />
                  <h5 className="category-name">{category.name || category.category_name}</h5>
                </Card.Body>
              </Card>
            </Link>
          </Col>
        ))}
      </Row>
    </Container>
  );
}

export default React.memo(Categories);
