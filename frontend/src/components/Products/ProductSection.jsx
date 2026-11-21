import React from 'react';
import { Container, Row } from 'react-bootstrap';
import ProductCard from './ProductCard';
import { ProductSkeleton } from '../ui/LoadingStates';
import { ErrorAlert, EmptyState } from '../ui/ErrorStates';

/**
 * Reusable Product Section Component
 * @param {string} title - Section title
 * @param {Array} products - Array of products to display
 * @param {boolean} loading - Loading state
 * @param {string} error - Error message
 * @param {function} onRetry - Retry callback
 * @returns {JSX.Element}
 */
const ProductSection = ({
  title,
  products = [],
  loading = false,
  error = null,
  onRetry = null,
}) => {
  // Ensure products is always an array
  const productArray = Array.isArray(products) ? products : [];

  if (error) {
    return <ErrorAlert message={error} onRetry={onRetry} />;
  }

  if (loading) {
    return (
      <Container className="my-5">
        <div className="ecom-section-header">
          <div>
            <h2 className="ecom-section-title">{title}</h2>
          </div>
        </div>
        <Row>
          <ProductSkeleton count={8} />
        </Row>
      </Container>
    );
  }

  if (!productArray || productArray.length === 0) {
    return <EmptyState title={title} message="No products available in this section" />;
  }

  return (
    <Container className="my-5">
      <div className="ecom-section-header">
        <div>
          <h2 className="ecom-section-title">{title}</h2>
          <p className="ecom-section-subtitle">Handpicked top-rated items updated daily</p>
        </div>
      </div>
      <Row className="g-2">
        {productArray.map((product) => (
          <ProductCard key={product.id} product={product} />
        ))}
      </Row>
    </Container>
  );
};

export default ProductSection;
