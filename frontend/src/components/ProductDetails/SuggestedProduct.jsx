import React, { Fragment, useEffect, useState } from "react";
import { Container, Row, Col, Card } from "react-bootstrap";
import { Link } from "react-router-dom";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";
import apiClient from "../../services/apiClient";
import AppURL from "../../utils/AppURL";
import ToastMessages from "../../toast-messages/toast";

const SuggestedProduct = ({ product_id }) => {
  const [relatedProducts, setRelatedProducts] = useState([]);
  const [isLoading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    // Fetch related products from the backend API
    const fetchRelatedProducts = async () => {
      if (!product_id) {
        setLoading(false);
        return;
      }

      try {
        const response = await apiClient.get(AppURL.relatedProduct(product_id));
        const data = response.data?.data?.related_products ?? response.data?.related_products ?? response.data;
        setRelatedProducts(data || []);
      } catch (err) {
        const msg = err?.response?.data?.message || "Failed to load related products.";
        setError(ToastMessages.showError(msg));
      } finally {
        setLoading(false);
      }
    };

    fetchRelatedProducts();
  }, [product_id]);

  const renderSkeletons = Array.from({ length: 4 }).map((_, index) => (
    <Col className="p-1" key={index} xl={2} lg={2} md={2} sm={4} xs={6}>
      <Card className="image-box card">
        <Skeleton height={200} />
        <Card.Body>
          <Skeleton count={2} />
        </Card.Body>
      </Card>
    </Col>
  ));

  if (isLoading) {
    return (
      <Container className="text-center" fluid={true}>
        <div className="section-title text-center mb-55">
          <h2>YOU MAY ALSO LIKE</h2>
          <p>Some Of Our Exclusive Collection, You May Like</p>
          {renderSkeletons}
        </div>
      </Container>
    );
  }

  if (error) {
    return (
      <Container className="text-center">
        <h4>{ToastMessages.showError(error)}</h4>
      </Container>
    );
  }

  if (relatedProducts.length === 0) {
    return (
      <Container className="text-center">
        <div className="section-title text-center mb-55">
          <h2>YOU MAY ALSO LIKE</h2>
          <p>Some Of Our Exclusive Collection, You May Like</p>
        </div>
        <h4 className="text-muted">No related products found.</h4>
      </Container>
    );
  }

  const MyView = relatedProducts.map((product, index) => (
    <Col className="p-1" key={index} xl={2} lg={2} md={2} sm={4} xs={6}>
      <Link className="text-link" to={`/product-details/${product.id}`}>
        <Card className="image-box card">
          <img
            className="center"
            src={product.image || product.product_details?.image_one}
            alt={product.title}
            style={{ height: "200px", objectFit: "cover" }}
          />
          <Card.Body>
            <p className="product-name-on-card">{product.title}</p>
            <p className="product-price-on-card">Price: ${product.price}</p>
          </Card.Body>
        </Card>
      </Link>
    </Col>
  ));

  return (
    <>
      <Container className="text-center" fluid={true}>
        <div className="section-title text-center mb-55">
          <h2>YOU MAY ALSO LIKE</h2>
          <p>Some Of Our Exclusive Collection, You May Like</p>
        </div>
        <Row>{MyView}</Row>
      </Container>
    </>
  );
};

export default SuggestedProduct;
