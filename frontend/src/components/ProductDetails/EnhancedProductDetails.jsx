import React, { useContext, useState, useEffect } from "react";
import {
  Container,
  Row,
  Col,
  Breadcrumb,
  Badge,
  Spinner,
  Tooltip,
  OverlayTrigger,
} from "react-bootstrap";
import { Link, useNavigate } from "react-router-dom";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";
import ToastMessages from "../../toast-messages/toast";
import { AuthContext } from "../../utils/AuthContext";
import { CartContext } from "../../utils/CartContext";
import AppURL from "../../utils/AppURL";
import apiClient from "../../services/apiClient";
import "./AmazonProductPage.css";

const EnhancedProductDetails = ({ productData, message }) => {
  const [previewImg, setPreviewImg] = useState(null);
  const [qty, setQuantity] = useState(1);
  const [selectedColor, setSelectedColor] = useState(null);
  const [selectedSize, setSelectedSize] = useState(null);
  const [activeTab, setActiveTab] = useState("description");
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const { token } = useContext(AuthContext);
  const { addToCart } = useContext(CartContext);
  const navigate = useNavigate();

  const product = productData?.product || productData;

  useEffect(() => {
    if (product?.images?.length) {
      setPreviewImg(product.images[0]?.url || product.images[0]);
    }
    if (product?.color?.length) {
      setSelectedColor(product.color[0]?.name || product.color[0]);
    }
    if (product?.size?.length) {
      setSelectedSize(product.size[0]?.name || product.size[0]);
    }
  }, [product]);

  useEffect(() => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  }, []);

  const handleQuantityChange = (e) => {
    const value = parseInt(e.target.value, 10);
    if (value > (product?.quantity || 0)) {
      ToastMessages.showWarning(
        `Sorry, we only have ${product?.quantity || 0} units in stock.`
      );
    }
    setQuantity(value >= 1 ? value : 1);
  };

  const handleColorSelect = (color) => {
    setSelectedColor(color);
    // Update preview image based on color if variants exist
  };

  const handleSizeSelect = (size) => {
    setSelectedSize(size);
  };

  const handleAddToCart = async () => {
    if (qty > (product?.quantity || 0)) {
      ToastMessages.showWarning(
        `Sorry, we only have ${product?.quantity || 0} units in stock.`
      );
      return;
    }

    try {
      const result = await addToCart(product, qty, {
        color: selectedColor,
        size: selectedSize,
      });
      if (result.success) {
        ToastMessages.showSuccess("Product added to cart successfully!");
      } else if (result.isUnauthorized) {
        sessionStorage.setItem("redirectUrl", window.location.pathname);
        navigate("/login");
        ToastMessages.showWarning("Please login to add items to cart");
      } else {
        ToastMessages.showError("Failed to add product to cart");
      }
    } catch (error) {
      console.error("Add to cart error:", error);
      ToastMessages.showError("An unexpected error occurred");
    }
  };

  const handleBuyNow = () => {
    // Add to cart and redirect to checkout
    addToCart(product, qty, {
      color: selectedColor,
      size: selectedSize,
    }).then((result) => {
      if (result.success) {
        navigate("/cart");
      }
    });
  };

  // Rating Breakdown Display
  const renderRatingBreakdown = () => {
    const rating = product?.rating || { average: 0, count: 0, breakdown: {} };
    const labels = [
      { label: "5 star", value: rating.breakdown?.["5_star"] || 0 },
      { label: "4 star", value: rating.breakdown?.["4_star"] || 0 },
      { label: "3 star", value: rating.breakdown?.["3_star"] || 0 },
      { label: "2 star", value: rating.breakdown?.["2_star"] || 0 },
      { label: "1 star", value: rating.breakdown?.["1_star"] || 0 },
    ];

    const maxCount = Math.max(...labels.map((l) => l.value), 1);

    return (
      <div className="rating-breakdown">
        {labels.map((item, index) => (
          <div key={index} className="rating-bar-row">
            <span className="rating-bar-stars">★★★★★</span>
            <span className="rating-bar-fill">
              <span
                className="rating-bar-fill-inner"
                style={{ width: `${(item.value / maxCount) * 100}%` }}
              ></span>
            </span>
            <span className="rating-percentage">{item.label}</span>
          </div>
        ))}
      </div>
    );
  };

  // Tab Content
  const renderTabContent = () => {
    switch (activeTab) {
      case "description":
        return (
          <div className="tab-content">
            <h4>Product Description</h4>
            <div
              className="product-description-content"
              dangerouslySetInnerHTML={{ __html: product?.description }}
            />
            {product?.long_description && (
              <div
                className="product-description-content"
                dangerouslySetInnerHTML={{ __html: product.long_description }}
              />
            )}
          </div>
        );

      case "specifications":
        return (
          <div className="tab-content">
            <h4>Technical Specifications</h4>
            <table className="specifications-table">
              <tbody>
                {product?.specifications?.map((spec, index) => (
                  <tr key={index}>
                    <th>{spec.attribute_name}</th>
                    <td>
                      {spec.value}
                      {spec.unit && ` ${spec.unit}`}
                    </td>
                  </tr>
                ))}
                {(!product?.specifications || product.specifications.length === 0) && (
                  <tr>
                    <td colSpan="2" className="text-center text-muted">
                      No specifications available
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        );

      case "reviews":
        return (
          <div className="tab-content">
            <h4>Customer Reviews ({product?.rating?.count || 0})</h4>
            {product?.reviews?.length > 0 ? (
              product.reviews.map((review, index) => (
                <div key={index} className="review-item">
                  <div className="review-author">{review.user?.name || "Anonymous"}</div>
                  <div className="review-rating">
                    {"★".repeat(review.rating)}
                    {"☆".repeat(5 - review.rating)}
                  </div>
                  <div className="review-date">
                    {new Date(review.created_at).toLocaleDateString()}
                  </div>
                  <div className="review-content">{review.comment}</div>
                </div>
              ))
            ) : (
              <p className="text-muted">No reviews yet. Be the first to review!</p>
            )}
          </div>
        );

      case "qna":
        return (
          <div className="tab-content">
            <h4>Questions & Answers</h4>
            <p className="text-muted">
              Be the first to ask a question about this product.
            </p>
          </div>
        );

      default:
        return null;
    }
  };

  // Loading state
  if (!productData && loading) {
    return (
      <Container className="product-details-container">
        <Row>
          <Col md={6} lg={6} sm={12} xs={12}>
            <Skeleton height={400} />
            <Skeleton count={5} />
          </Col>
          <Col md={6} lg={6} sm={12} xs={12}>
            <Skeleton height={50} width="30%" />
            <Skeleton height={30} width="15%" />
            <Skeleton height={40} width="30%" />
            <Skeleton height={50} />
          </Col>
        </Row>
      </Container>
    );
  }

  // Error state
  if (error) {
    return (
      <Container className="text-center py-5">
        <h3 className="text-danger">{error}</h3>
      </Container>
    );
  }

  return (
    <Container className="product-details-container">
      {/* Breadcrumb */}
      <div className="breadcrumb-section mb-3">
        <Breadcrumb>
          <Breadcrumb.Item linkAs={Link} linkProps={{ to: "/" }}>
            Home
          </Breadcrumb.Item>
          {product?.category?.slug ? (
            <Breadcrumb.Item
              linkAs={Link}
              linkProps={{ to: `/${product.category.slug}` }}
            >
              {product.category.name}
            </Breadcrumb.Item>
          ) : (
            <Breadcrumb.Item>{product?.category?.name || "Products"}</Breadcrumb.Item>
          )}
          <Breadcrumb.Item active>{product?.name || "Product"}</Breadcrumb.Item>
        </Breadcrumb>
      </div>

      <Row className="g-4">
        {/* Product Images */}
        <Col md={6} lg={6} sm={12} xs={12}>
          <div className="product-image-gallery">
            <img
              className="main-product-image"
              src={
                previewImg ||
                product?.image ||
                "/images/no-image.png"
              }
              alt={product?.name}
            />
            {product?.images?.length > 1 && (
              <div className="thumbnail-strip">
                {product.images.map((img, index) => (
                  <img
                    key={index}
                    className={`thumbnail-item ${
                      previewImg === (img.url || img.path) ? "active" : ""
                    }`}
                    src={img.url || img.path}
                    alt={`${product?.name} ${index + 1}`}
                    onClick={() => setPreviewImg(img.url || img.path)}
                  />
                ))}
              </div>
            )}
          </div>
        </Col>

        {/* Product Info */}
        <Col md={6} lg={6} sm={12} xs={12}>
          <div className="product-info-section">
            {/* Title */}
            <div className="product-title-section">
              <h1 className="product-title">{product?.name}</h1>
            </div>

            {/* Breadcrumbs already shown above */}

            {/* Brand & SKU */}
            {product?.brand && (
              <p className="text-muted mb-2">
                By{" "}
                <Link
                  to={`/brand/${product.brand.toLowerCase()}`}
                  className="text-decoration-none"
                >
                  {product.brand}
                </Link>
                {product?.product_code && (
                  <>
                    {" • "}
                    <span>Model: {product.product_code}</span>
                  </>
                )}
              </p>
            )}

            {/* Pricing */}
            <div className="product-pricing">
              <div className="product-price-main">
                ${product?.price?.toFixed(2) || "0.00"}
              </div>
              {product?.special_price && product.special_price < product.price && (
                <>
                  <div className="product-price-special">
                    ${product.special_price.toFixed(2)}
                    <span className="product-savings-badge">
                      Save $
                      {(product.price - product.special_price).toFixed(2)}
                    </span>
                  </div>
                  <div className="product-price-original">
                    Was ${product.price.toFixed(2)}
                  </div>
                  <div className="product-savings">
                    {`${(((product.price - product.special_price) / product.price) * 100).toFixed(0)}% off`}
                  </div>
                </>
              )}
            </div>

            {/* Stock Status */}
            <div className="stock-status-section mb-3">
              <Badge
                bg={product?.quantity > 0 ? "success" : "danger"}
                className={product?.quantity > 0 ? "in-stock" : "out-of-stock"}
              >
                {product?.quantity > 0 ? "In Stock" : "Out of Stock"}
              </Badge>
              {product?.quantity > 0 && (
                <span className="text-muted ms-2">
                  {product.quantity} available
                </span>
              )}
            </div>

            {/* Rating & Reviews */}
            <div className="rating-summary">
              <span className="rating-stars">
                {"★".repeat(Math.floor(product?.rating?.average || 0))}
                {"☆".repeat(5 - Math.floor(product?.rating?.average || 0))}
              </span>
              <span className="rating-average">
                {(product?.rating?.average || 0).toFixed(1)}
              </span>
              <a
                href="#reviews"
                className="review-count-link"
                onClick={(e) => {
                  e.preventDefault();
                  setActiveTab("reviews");
                }}
              >
                ({product?.rating?.count || 0} ratings)
              </a>
              {renderRatingBreakdown()}
            </div>

            {/* Color Options */}
            {product?.color?.length > 0 && (
              <div className="color-options">
                <h6 className="mb-2">Color</h6>
                {product.color.map((color, index) => {
                  const colorName = typeof color === "string" ? color : color.name;
                  return (
                    <button
                      key={index}
                      className={`color-option ${
                        selectedColor === colorName ? "active" : ""
                      }`}
                      onClick={() => handleColorSelect(colorName)}
                      aria-label={colorName}
                      style={{
                        backgroundColor:
                          colorName.toLowerCase().includes("red")
                            ? "#f44336"
                            : colorName.toLowerCase().includes("blue")
                            ? "#2196F3"
                            : colorName.toLowerCase().includes("green")
                            ? "#4CAF50"
                            : "#9E9E9E",
                      }}
                    />
                  );
                })}
              </div>
            )}

            {/* Size Options */}
            {product?.size?.length > 0 && (
              <div className="size-options">
                <h6 className="mb-2">Size</h6>
                {product.size.map((size, index) => {
                  const sizeName = typeof size === "string" ? size : size.name;
                  return (
                    <button
                      key={index}
                      className={`size-option ${
                        selectedSize === sizeName ? "active" : ""
                      }`}
                      onClick={() => handleSizeSelect(sizeName)}
                    >
                      {sizeName}
                    </button>
                  );
                })}
              </div>
            )}

            {/* Quantity */}
            <div className="quantity-selector">
              <label className="me-2">Qty:</label>
              <input
                type="number"
                className="quantity-input"
                value={qty}
                onChange={handleQuantityChange}
                min="1"
                max={product?.quantity || 999}
                disabled={product?.quantity <= 0}
              />
            </div>

            {/* Action Buttons */}
            <div className="purchase-actions">
              <div className="action-buttons">
                <button
                  className="btn btn-primary"
                  onClick={handleAddToCart}
                  disabled={product?.quantity <= 0}
                >
                  <i className="fa fa-shopping-cart me-1"></i>
                  Add to Cart
                </button>
                <button className="btn btn-success" onClick={handleBuyNow}>
                  <i className="fa fa-arrow-right me-1"></i>
                  Buy Now
                </button>
                <OverlayTrigger
                  placement="top"
                  trigger="hover"
                  overlay={<Tooltip>Favorite</Tooltip>}
                >
                  <button className="btn btn-outline-primary">
                    <i className="fa fa-heart"></i>
                  </button>
                </OverlayTrigger>
                <OverlayTrigger
                  placement="top"
                  trigger="hover"
                  overlay={<Tooltip>Share</Tooltip>}
                >
                  <button className="btn btn-outline-primary">
                    <i className="fa fa-share"></i>
                  </button>
                </OverlayTrigger>
              </div>
            </div>

            {/* Shipping Information */}
            <div className="shipping-info mt-4 p-3 bg-light rounded">
              <h6 className="mb-2">Shipping & Returns</h6>
              <ul className="mb-0" style={{ fontSize: "14px" }}>
                <li className="mb-1">
                  <i className="fa fa-truck text-primary me-2"></i>
                  Free shipping on orders over $50
                </li>
                <li className="mb-1">
                  <i className="fa fa-undo text-primary me-2"></i>
                  30-day return policy
                </li>
                <li>
                  <i className="fa fa-shipping-fast text-primary me-2"></i>
                  3-5 business days delivery
                </li>
              </ul>
            </div>
          </div>
        </Col>
      </Row>

      {/* Tabs Section */}
      <div className="product-info-tabs mt-5">
        <div className="tab-navigation">
          {[
            { id: "description", label: "Product Description" },
            { id: "specifications", label: "Specifications" },
            { id: "reviews", label: "Reviews" },
            { id: "qna", label: "Q&A" },
          ].map((tab) => (
            <button
              key={tab.id}
              className={`tab-item ${activeTab === tab.id ? "active" : ""}`}
              onClick={() => setActiveTab(tab.id)}
            >
              {tab.label}
            </button>
          ))}
        </div>
        {renderTabContent()}
      </div>

      {/* Related Products */}
      <div className="related-products-section mt-5">
        <h3 className="related-products-title">YOU MAY ALSO LIKE</h3>
        {/* This will be populated by SuggestedProduct component */}
        <SuggestedProduct product_id={product?.id} />
      </div>
    </Container>
  );
};

export default EnhancedProductDetails;