import React from 'react';
import Skeleton from 'react-loading-skeleton';
import 'react-loading-skeleton/dist/skeleton.css';
import { Col } from 'react-bootstrap';

/**
 * Generic skeleton loader for products
 * @param {number} count - Number of skeleton items to render
 * @returns {JSX.Element}
 */
export const ProductSkeleton = ({ count = 8 }) =>
  Array.from({ length: count }).map((_, index) => (
    <Col className="p-0" key={index} xl={3} lg={3} md={3} sm={6} xs={6}>
      <div className="image-box w-100 p-3">
        <Skeleton
          className="center"
          style={{ width: '75%', height: '150px', margin: '0 auto' }}
        />
        <h5 className="product-name-on-card">
          <Skeleton width="80%" />
        </h5>
        <p className="product-price-on-card">
          <Skeleton width="60%" />
        </p>
      </div>
    </Col>
  ));

/**
 * Generic section loader
 * @returns {JSX.Element}
 */
export const SectionSkeleton = () => (
  <>
    <div className="section-title text-center mb-4">
      <h2><Skeleton width={200} /></h2>
    </div>
    <div className="row">
      <ProductSkeleton count={8} />
    </div>
  </>
);

/**
 * Page loading fallback
 * @returns {JSX.Element}
 */
export const PageLoadingFallback = () => (
  <div className="text-center p-5">
    <div className="spinner-border text-primary" role="status">
      <span className="visually-hidden">Loading...</span>
    </div>
  </div>
);
