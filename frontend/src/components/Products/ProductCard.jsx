import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { Col } from 'react-bootstrap';
import './ProductCard.css';

/**
 * Modern E-Commerce Product Card Component
 */
const ProductCard = ({ product }) => {
  const [wishlisted, setWishlisted] = useState(() => {
    try {
      const list = JSON.parse(localStorage.getItem('ecommerce_wishlist') || '[]');
      return list.some((item) => item.id === product?.id);
    } catch {
      return false;
    }
  });

  if (!product) return null;

  const price = Number(product.price || 0);
  const specialPrice =
    product.special_price && product.special_price !== 'na'
      ? Number(product.special_price)
      : null;
  const hasDiscount = specialPrice !== null && specialPrice > 0 && specialPrice < price;
  const discountPct = hasDiscount
    ? Math.round(((price - specialPrice) / price) * 100)
    : 0;

  const productTitle = product.title || product.name || 'Premium Product';
  const productBrand = product.brand || product.category?.name || 'Featured';
  const fallbackImg =
    'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=80';
  const productImg =
    product.image ||
    product.image_url ||
    (product.images && product.images[0]?.url) ||
    fallbackImg;

  const ratingValue =
    typeof product.rating === 'object'
      ? product.rating?.average || 4.8
      : product.rating || 4.8;

  const toggleWishlist = (e) => {
    e.preventDefault();
    e.stopPropagation();
    try {
      const list = JSON.parse(localStorage.getItem('ecommerce_wishlist') || '[]');
      const exists = list.some((item) => item.id === product.id);
      const updated = exists
        ? list.filter((item) => item.id !== product.id)
        : [
            ...list,
            {
              id: product.id,
              title: productTitle,
              price: hasDiscount ? specialPrice : price,
              image: productImg,
            },
          ];
      localStorage.setItem('ecommerce_wishlist', JSON.stringify(updated));
      setWishlisted(!exists);
    } catch {
      // ignore storage errors
    }
  };

  return (
    <Col className="p-2" xl={3} lg={3} md={4} sm={6} xs={12}>
      <Link
        className="text-link d-block h-100"
        to={`/product-details/${product.id}`}
      >
        <div className="ecom-product-card">
          {/* Image & Badges */}
          <div className="ecom-product-media">
            {hasDiscount && (
              <span className="ecom-discount-pill">-{discountPct}% OFF</span>
            )}
            <button
              type="button"
              onClick={toggleWishlist}
              className={`ecom-wishlist-trigger ${wishlisted ? 'active' : ''}`}
              aria-label="Add to Wishlist"
            >
              <i className="fa fa-heart"></i>
            </button>
            <img
              className="ecom-product-img"
              src={productImg}
              alt={productTitle}
              loading="lazy"
              onError={(e) => {
                e.target.onerror = null;
                e.target.src = fallbackImg;
              }}
            />
          </div>

          {/* Product Details */}
          <div className="ecom-product-body">
            <div className="ecom-product-meta">
              <span className="ecom-product-brand">{productBrand}</span>
              <span className="ecom-product-rating">
                <i className="fa fa-star"></i> {Number(ratingValue).toFixed(1)}
              </span>
            </div>

            <h6 className="ecom-product-title">{productTitle}</h6>

            <div className="ecom-product-footer">
              <div className="ecom-price-wrap">
                {hasDiscount ? (
                  <>
                    <span className="ecom-price-current">
                      ${specialPrice.toFixed(2)}
                    </span>
                    <span className="ecom-price-old">${price.toFixed(2)}</span>
                  </>
                ) : (
                  <span className="ecom-price-current">${price.toFixed(2)}</span>
                )}
              </div>

              <span className="ecom-card-action" title="View Product">
                <i className="fa fa-arrow-right"></i>
              </span>
            </div>
          </div>
        </div>
      </Link>
    </Col>
  );
};

export default ProductCard;
