import React, { useEffect, useState, useContext } from "react";
import { Container, Row, Col, Card, Badge, Spinner } from "react-bootstrap";
import { Link } from "react-router-dom";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";
import apiClient from "../../services/apiClient";
import AppURL from "../../utils/AppURL";

const EnhancedSuggestedProduct = ({ product_id }) => {
  const [relatedProducts, setRelatedProducts] = useState([]);
  const [isLoading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (!product_id) {
      setLoading(false);
      return;
    }

    const fetchRelatedProducts = async () => {
      setLoading(true);
      try {
        const response = await apiClient.get(AppURL.relatedProduct(product_id));
        const data =
          response.data?.data?.related_products ??
          response.data?.related_products ??
          response.data;

        setRelatedProducts(data?.products ?? data ?? []);
      } catch (err) {
        const msg =
          err?.response?.data?.message ?? "Failed to load related products.";
        setError(msg);
      } finally {
        setLoading(false);
      }
    };

    fetchRelatedProducts();
  }, [product_id]);

  const renderSkeletons = Array.from({ length: 4 }).map((_, index) => (
    <Col key={index} xl={2} lg={2} md={3} sm={4} xs={6}>
      <Card className="image-box card h-100">
        <Skeleton height={180} />
        <Card.Body>
          <Skeleton count={2} height={16} />
          <Skeleton height={14} width={60} />
        </Card.Body>
      </Card>
    </Col>
  ));

  if (isLoading) {
    return (
      <Container className="text-center" fluid={true}>
        <div className="section-title text-center mb-4">
          <h3>MORE LIKE THIS</h3>
          <p>Based on your viewing history and purchase patterns</p>
        </div>
        <Row>{renderSkeletons}</Row>
      </Container>
    );
  }

  if (error) {
    return null; // Don't show error to avoid breaking the page
  }

  if (!relatedProducts || relatedProducts.length === 0) {
    return (
      <Container className="text-center" fluid={true}>
        <div className="section-title text-center mb-4">
          <h3>MORE LIKE THIS</h3>
        </div>
        <p className="text-muted">No related products found.</p>
      </Container>
    );
  }

  const renderRelatedProducts = relatedProducts.map((product, index) => {
    const imageUrl =
      product.image ||
      product.product_details?.image_one ||
      "/images/no-image.png";

    const price = product.price ?? 0;
    const specialPrice = product.special_price;
    const isOnSale = specialPrice && specialPrice < price;

    return (
      <Col key={index} xl={2} lg={2} md={3} sm={4} xs={6}>
        <Link to={`/product-details/${product.id}`} className="text-link">
          <Card className="image-box card h-100 product-card">
            <div className="product-card-image-container">
              {isOnSale && (
                <Badge
                  bg="danger"
                  pill
                  className="product-card-badge"
                >
                  {Math.round(
                    ((price - specialPrice) / price) * 100
                  )}
                  % OFF
                </Badge>
              )}
              <img
                className="product-card-image center"
                src={imageUrl}
                alt={product.title || product.name}
                style={{ height: "180px", objectFit: "cover" }}
              />
            </div>
            <Card.Body className="product-card-body">
              <p
                className="product-card-name product-card-name-truncated"
                title={product.title || product.name}
              >
                {product.title || product.name}
              </p>
              <div className="product-card-pricing">
                {isOnSale ? (
                  <>
                    <span className="product-card-price-current">
                      ${specialPrice.toFixed(2)}
                    </span>
                    <span className="product-card-price-original">
                      ${price.toFixed(2)}
                    </span>
                  </>
                ) : (
                  <span className="product-card-price">
                    ${price.toFixed(2)}
                  </span>
                )}
              </div>
              {product?.rating && (
                <div className="product-card-rating">
                  <span className="rating-stars">
                    {"★".repeat(Math.floor(product.rating.average || 0))}
                    {"☆".repeat(5 - Math.floor(product.rating.average || 0))}
                  </span>
                  <span className="rating-count">
                    {" ("}
                    {product.rating.count || 0}
                    {")"}
                  </span>
                </div>
              )}
            </Card.Body>
          </Card>
        </Link>
      </Col>
    );
  });

  return (
    <Container className="text-center" fluid={true}>
      <div className="section-title text-center mb-4">
        <h3>MORE LIKE THIS</h3>
        <p>Based on your viewing history and purchase patterns</p>
      </div>
      <Row className="g-3">{renderRelatedProducts}</Row>
    </Container>
  );
};

export default EnhancedSuggestedProduct;