import React from 'react';
import { Link } from 'react-router-dom';
import { Card, Col } from 'react-bootstrap';
import './ProductCard.css';

/**
 * Reusable Product Card Component
 * @param {Object} product - Product data
 * @param {number} product.id - Product ID
 * @param {string} product.title - Product title
 * @param {string} product.image - Product image URL
 * @param {number} product.price - Product price
 * @param {number} product.special_price - Special/discount price
 * @returns {JSX.Element}
 */
const ProductCard = ({ product }) => {
  if (!product) return null;

  const hasDiscount = product.special_price && product.special_price !== 'na';

  return (
    <Col className="p-2" xl={3} lg={3} md={4} sm={6} xs={12}>
      <Link
        className="text-link"
        to={`/product-details/${product.id}`}
        style={{ textDecoration: 'none' }}
      >
        <Card className="product-card h-100 shadow-sm hover-shadow">
          <Card.Body className="text-center">
            {/* Product Image */}
            <div className="product-image-container mb-3">
              <Card.Img
                className="product-image"
                src={product.image}
                alt={product.title}
                loading="lazy"
              />
              {hasDiscount && (
                <div className="discount-badge">
                  Sale
                </div>
              )}
            </div>

            {/* Product Title */}
            <h6 className="product-title mb-2">{product.title}</h6>

            {/* Product Price */}
            <div className="product-price">
              {hasDiscount ? (
                <>
                  <del className="text-muted me-2">${product.price}</del>
                  <span className="text-danger fw-bold">${product.special_price}</span>
                </>
              ) : (
                <span className="text-primary fw-bold">${product.price}</span>
              )}
            </div>

            {/* Rating / Stock Status */}
            {product.rating && (
              <div className="product-rating mt-2">
                <span className="text-warning">★★★★★ ({product.rating})</span>
              </div>
            )}
          </Card.Body>
        </Card>
      </Link>
    </Col>
  );
};

export default ProductCard;
