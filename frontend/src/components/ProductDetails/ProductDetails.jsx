import React, { useContext, useState, useEffect } from "react";
import { Container, Row, Col, Breadcrumb } from "react-bootstrap";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css"; // Import the required styles
import ToastMessages from "../../toast-messages/toast";
import { Link, useNavigate } from "react-router-dom";
import SuggestedProduct from "./SuggestedProduct";
import Review from "./Review";
import { AuthContext } from "../../utils/AuthContext";
import { CartContext } from "../../utils/CartContext";

const ProductDetails = ({ productData, message }) => {
  const [previewImg, setPreviewImg] = useState(null);
  const [qty, setQuantity] = useState(1);
  const { token } = useContext(AuthContext); // Get token and loading state from context
  const { addToCart } = useContext(CartContext);
  const navigate = useNavigate();

  useEffect(() => {
    const productImages = productData?.product?.images?.length
      ? productData.product.images.map((img) => img.url || img.path).filter(Boolean)
      : [productData?.product?.image].filter(Boolean);

    setPreviewImg(productImages[0] ?? null);
  }, [productData]);

  // Handle the case where productData is not loaded yet
  if (!productData || !productData.product) {
    return (
      <Container fluid={true} className="BetweenTwoSection">
        <Row className="p-2">
          <Col className="shadow-sm bg-white pb-3 mt-4" md={12} lg={12} sm={12} xs={12}>
            <Skeleton height={400} />
            <Skeleton count={5} />
          </Col>
        </Row>
      </Container>
    );
  }

  const {
    title,
    brand,
    category,
    image,
    quantity: stockQuantity, // Available stock in the database
    price,
    product_code,
    remark,
    special_price,
    product_id,
    product_details: {
      color,
      size,
      short_description,
      long_description,
    } = {},
  } = productData.product;

  // Ensure we have a usable product id (some APIs return `id`, others `product_id`)
  const actualProductId = product_id ?? productData.product.id ?? null;

  const handleQuantityChange = (e) => {
    const value = parseInt(e.target.value, 10);
    if (value > stockQuantity) {
      ToastMessages.showWarning(
        `Sorry, we only have ${stockQuantity} units in stock.`
      );
    }
    setQuantity(value >= 1 ? value : 1); // Ensure quantity doesn't go below 1
  };

  const handleThumbnailClick = (img) => {
    setPreviewImg(img);
  };

  const handleLoginClick = () => {
    // Store the current location in sessionStorage
    sessionStorage.setItem("redirectUrl", window.location.pathname);
    // Navigate to login page
    navigate("/login");
  };

  const handleAddToCart = async () => {
    if (qty > stockQuantity) {
      ToastMessages.showWarning(
        `Sorry, we only have ${stockQuantity} units in stock.`
      );
      return;
    }

    try {
      const result = await addToCart(productData.product, qty);
      if (result.success) {
        ToastMessages.showSuccess("Product added to cart successfully!");
      } else if (result.isUnauthorized) {
        // This will handle the 401 case
        handleLoginClick();
        ToastMessages.showWarning("Please login to add items to cart");
      } else {
        ToastMessages.showError("Failed to add product to cart");
      }
    } catch (error) {
      console.error("Add to cart error:", error);
      ToastMessages.showError("An unexpected error occurred");
    }
  };

  const productImages = productData?.product?.images?.length
    ? productData.product.images.map((img) => img.url || img.path).filter(Boolean)
    : [image].filter(Boolean);

  const renderProductImages = () => {
    const mainImage = previewImg || productImages[0] || "/images/no-image.png";

    return (
      <Col className="p-3" md={6} lg={6} sm={12} xs={12}>
        <img className="big-image" src={mainImage} alt={title} />
        <Container className="my-3">
          <Row>
            {productImages.map((img, index) => (
              <Col key={index} className="p-0 m-0" md={3} lg={3} sm={3} xs={3}>
                <img
                  className="small-image"
                  src={img}
                  alt={`${title} ${index + 1}`}
                  onClick={() => handleThumbnailClick(img)}
                  style={{ cursor: "pointer" }}
                />
              </Col>
            ))}
          </Row>
        </Container>
      </Col>
    );
  };

  const renderProductInfo = () => (
    <Col className="p-3" md={6} lg={6} sm={12} xs={12}>
      <h5 className="Product-Name">{title}</h5>
      <h6 className="section-sub-title">{short_description}</h6>

      <div className="input-group">
        <div className="Product-price-card d-inline">
          Was: ${special_price ? <del>{price}</del> : price}
        </div>
        {special_price && (
          <div className="Product-price-card d-inline">
            Now: ${special_price}
          </div>
        )}
        <div className="Product-price-card d-inline">
          Remark: {remark}
        </div>
      </div>

      {special_price && (
        <h6 className="text-success mt-2">
          <b>Saving: ${price - special_price}</b>
        </h6>
      )}

      <h6 className="mt-2">
        Category: <b>{category?.category_name || "No Category"}</b>
      </h6>
      <h6 className="mt-2">
        Brand: <b>{brand}</b>
      </h6>
      <h6 className="mt-2">
        Product Code: <b>{product_code}</b>
      </h6>

      {color?.length > 0 && (
        <>
          <h6 className="mt-2">Choose Color</h6>
          <div className="input-group">
            {color.map((colorOption, index) => (
              <div key={index} className="form-check mx-1">
                <input
                  className="form-check-input"
                  type="radio"
                  name="colorOptions"
                  value={colorOption}
                  id={`color-${index}`}
                />
                <label className="form-check-label" htmlFor={`color-${index}`}>
                  {colorOption}
                </label>
              </div>
            ))}
          </div>
        </>
      )}

      {size?.length > 0 && (
        <>
          <h6 className="mt-2">Choose Size</h6>
          <div className="input-group">
            {size.map((sizeOption, index) => (
              <div key={index} className="form-check mx-1">
                <input
                  className="form-check-input"
                  type="radio"
                  name="sizeOptions"
                  value={sizeOption}
                  id={`size-${index}`}
                />
                <label className="form-check-label" htmlFor={`size-${index}`}>
                  {sizeOption}
                </label>
              </div>
            ))}
          </div>
        </>
      )}

      <h6 className="mt-2">Quantity</h6>
      <input
        className="form-control text-center w-50"
        type="number"
        value={qty}
        onChange={handleQuantityChange}
        min="1"
        max={stockQuantity}
      />

      <div className="input-group mt-3">
        <button className="btn site-btn m-1" onClick={handleAddToCart}>
          <i className="fa fa-shopping-cart"></i> Add To Cart
        </button>
        <button className="btn btn-primary m-1">
          <i className="fa fa-car"></i> Order Now
        </button>
        <button className="btn btn-primary m-1">
          <i className="fa fa-heart"></i> Favourite
        </button>
      </div>
    </Col>
  );

  const renderProductDescription = () => (
    <Row>
      <Col md={6} lg={6} sm={12} xs={12}>
        <h6 className="mt-2">DETAILS</h6>
        <p>{long_description || "No detailed description available."}</p>
      </Col>

      {!token && (
        <Col md={6} lg={6} sm={12} xs={12}>
          <button className="btn site-btn m-1" onClick={handleLoginClick}>
            <i className="fa fa-comments"></i> Login to View Reviews
          </button>
        </Col>
      )}

      {token && <Review product_id={actualProductId} />}
    </Row>
  );

  return (
    <>
      <Container fluid={true} className="BetweenTwoSection">
        <div className="breadbody">
          <Breadcrumb>
            <Breadcrumb.Item linkAs={Link} linkProps={{ to: '/' }}>
              Home
            </Breadcrumb.Item>
            {category?.slug ? (
              <Breadcrumb.Item linkAs={Link} linkProps={{ to: `/${category.slug}` }}>
                {category.name}
              </Breadcrumb.Item>
            ) : (
              <Breadcrumb.Item>{category?.name || 'Category'}</Breadcrumb.Item>
            )}
            <Breadcrumb.Item active linkAs="span">
              {title}
            </Breadcrumb.Item>
          </Breadcrumb>
        </div>

        <Row className="p-2">
          <Col className="shadow-sm bg-white pb-3 mt-4" md={12} lg={12} sm={12} xs={12}>
            <Row>
              {renderProductImages()}
              {renderProductInfo()}
            </Row>
            {renderProductDescription()}
          </Col>
        </Row>
      </Container>

      <SuggestedProduct product_id={actualProductId} />
    </>
  );
};

export default ProductDetails;
