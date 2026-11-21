import React, { useContext, useEffect, useMemo } from "react";
import { Container, Row, Col, Button, Card, Spinner } from "react-bootstrap";
import { Link, useNavigate } from "react-router-dom";
import { ResponsiveLayout } from "../../layouts/ResponsiveLayout";
import { CartContext } from "../../utils/CartContext";

const FALLBACK_IMG =
  "https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=80";

const Cart = () => {
  const navigate = useNavigate();
  const { cart = [], loading, updateCart, removeFromCart, refreshCart } =
    useContext(CartContext);

  useEffect(() => {
    window.scrollTo({ top: 0, behavior: "smooth" });
    refreshCart?.();
  }, [refreshCart]);

  const cartItems = useMemo(() => (Array.isArray(cart) ? cart : []), [cart]);

  const totalQuantity = useMemo(
    () => cartItems.reduce((acc, item) => acc + Number(item.quantity || 0), 0),
    [cartItems]
  );

  const subtotal = useMemo(
    () =>
      cartItems.reduce((acc, item) => {
        const unitPrice = Number(
          item.price ?? item.product?.special_price ?? item.product?.price ?? 0
        );
        return acc + Number(item.quantity || 0) * unitPrice;
      }, 0),
    [cartItems]
  );

  const shipping = subtotal > 0 ? (subtotal >= 99 ? 0 : 15) : 0;
  const grandTotal = subtotal + shipping;

  const handleQuantityChange = async (itemId, value) => {
    const quantity = Number(value) >= 1 ? Number(value) : 1;
    await updateCart(itemId, quantity);
  };

  const handleCheckout = () => {
    navigate("/payment", {
      state: {
        order: {
          items: cartItems,
          totalQuantity,
          totalPrice: subtotal,
        },
      },
    });
  };

  if (loading) {
    return (
      <ResponsiveLayout>
        <Container className="py-5 text-center">
          <Spinner animation="border" variant="primary" />
          <p className="text-muted mt-2">Loading your shopping cart...</p>
        </Container>
      </ResponsiveLayout>
    );
  }

  return (
    <ResponsiveLayout>
      <Container className="py-4">
        <div className="ecom-section-header">
          <div>
            <h2 className="ecom-section-title">Shopping Cart</h2>
            <p className="ecom-section-subtitle">
              {totalQuantity} {totalQuantity === 1 ? "item" : "items"} in your bag
            </p>
          </div>
          <Link to="/" className="btn btn-outline-primary btn-sm">
            <i className="fa fa-arrow-left me-1"></i> Continue Shopping
          </Link>
        </div>

        {cartItems.length === 0 ? (
          <Card className="text-center py-5">
            <Card.Body>
              <div
                className="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle"
                style={{
                  width: 72,
                  height: 72,
                  background: "var(--ecom-primary-soft)",
                  color: "var(--ecom-primary)",
                }}
              >
                <i className="fa fa-shopping-bag fa-2x"></i>
              </div>
              <h4 className="fw-bold">Your shopping cart is empty</h4>
              <p className="text-muted mb-4">
                Looks like you haven't added anything to your cart yet.
              </p>
              <Link to="/" className="site-btn text-decoration-none">
                <i className="fa fa-compass"></i> Explore Products
              </Link>
            </Card.Body>
          </Card>
        ) : (
          <Row className="g-4">
            {/* Cart Items List */}
            <Col lg={8} md={12}>
              {cartItems.map((item) => {
                const product = item.product || {};
                const image =
                  product.image ||
                  product.image_url ||
                  product.images?.[0]?.url ||
                  FALLBACK_IMG;
                const name =
                  product.title ||
                  product.name ||
                  product.product_name ||
                  "Product";
                const price = Number(
                  item.price ?? product.special_price ?? product.price ?? 0
                );
                const lineTotal = Number(item.quantity || 1) * price;

                return (
                  <div key={item.id} className="ecom-cart-item-card">
                    <Row className="align-items-center g-3">
                      <Col xs="auto">
                        <Link to={`/product-details/${product.id || item.product_id}`}>
                          <img
                            className="ecom-cart-thumb"
                            src={image}
                            alt={name}
                            onError={(e) => {
                              e.target.onerror = null;
                              e.target.src = FALLBACK_IMG;
                            }}
                          />
                        </Link>
                      </Col>

                      <Col>
                        <Link
                          to={`/product-details/${product.id || item.product_id}`}
                          className="text-link"
                        >
                          <h6 className="fw-bold mb-1 text-dark">{name}</h6>
                        </Link>
                        <div className="small text-muted mb-2">
                          Unit Price: <strong className="text-dark">${price.toFixed(2)}</strong>
                        </div>
                        <div className="d-flex flex-wrap align-items-center gap-3">
                          <div className="ecom-qty-group">
                            <button
                              type="button"
                              className="ecom-qty-btn"
                              onClick={() =>
                                handleQuantityChange(
                                  item.id,
                                  Math.max(1, Number(item.quantity) - 1)
                                )
                              }
                            >
                              −
                            </button>
                            <span className="ecom-qty-val">{item.quantity}</span>
                            <button
                              type="button"
                              className="ecom-qty-btn"
                              onClick={() =>
                                handleQuantityChange(
                                  item.id,
                                  Number(item.quantity) + 1
                                )
                              }
                            >
                              +
                            </button>
                          </div>

                          <button
                            type="button"
                            className="btn btn-sm text-danger p-0"
                            onClick={() => removeFromCart(item.id)}
                          >
                            <i className="fa fa-trash-alt me-1"></i> Remove
                          </button>
                        </div>
                      </Col>

                      <Col xs={12} sm="auto" className="text-sm-end">
                        <div className="small text-muted">Subtotal</div>
                        <div className="fs-5 fw-bold text-dark">
                          ${lineTotal.toFixed(2)}
                        </div>
                      </Col>
                    </Row>
                  </div>
                );
              })}
            </Col>

            {/* Sticky Order Summary */}
            <Col lg={4} md={12}>
              <div className="ecom-summary-card">
                <h5 className="fw-bold mb-3">Order Summary</h5>
                <div className="ecom-summary-row">
                  <span>Subtotal ({totalQuantity} items)</span>
                  <strong className="text-dark">${subtotal.toFixed(2)}</strong>
                </div>
                <div className="ecom-summary-row">
                  <span>Estimated Shipping</span>
                  {shipping === 0 ? (
                    <span className="text-success fw-bold">FREE</span>
                  ) : (
                    <strong className="text-dark">${shipping.toFixed(2)}</strong>
                  )}
                </div>
                {shipping > 0 && (
                  <div className="alert alert-info py-2 px-3 small mb-3">
                    <i className="fa fa-truck me-1"></i> Add{" "}
                    <strong>${(99 - subtotal).toFixed(2)}</strong> more for FREE
                    express shipping!
                  </div>
                )}

                <div className="ecom-summary-total">
                  <span>Total</span>
                  <span className="text-primary">${grandTotal.toFixed(2)}</span>
                </div>

                <Button
                  className="site-btn w-100 mt-4"
                  onClick={handleCheckout}
                >
                  <span>Proceed to Checkout</span>
                  <i className="fa fa-arrow-right"></i>
                </Button>

                <div className="text-center mt-3 small text-muted">
                  <i className="fa fa-lock me-1 text-success"></i> SSL Encrypted &
                  30-Day Money-Back Guarantee
                </div>
              </div>
            </Col>
          </Row>
        )}
      </Container>
    </ResponsiveLayout>
  );
};

export default Cart;
