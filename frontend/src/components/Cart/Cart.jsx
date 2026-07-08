import React, { useContext, useEffect, useMemo } from "react";
import { Container, Row, Col, Button, Card, Form, Spinner } from "react-bootstrap";
import { useNavigate } from "react-router-dom";
import { ResponsiveLayout } from "../../layouts/ResponsiveLayout";
import { CartContext } from "../../utils/CartContext";

const Cart = () => {
  const navigate = useNavigate();
  const { cart = [], loading, updateCart, removeFromCart, refreshCart } = useContext(CartContext);

  useEffect(() => {
    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });

    refreshCart?.();
  }, [refreshCart]);

  const cartItems = useMemo(() => Array.isArray(cart) ? cart : [], [cart]);

  const totalQuantity = useMemo(
    () => cartItems.reduce((acc, item) => acc + Number(item.quantity || 0), 0),
    [cartItems]
  );

  const totalPrice = useMemo(
    () => cartItems.reduce((acc, item) => {
      const unitPrice = Number(item.price ?? item.product?.price ?? item.product?.special_price ?? 0);
      return acc + Number(item.quantity || 0) * unitPrice;
    }, 0),
    [cartItems]
  );

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
          totalPrice,
        },
      },
    });
  };

  if (loading) {
    return (
      <ResponsiveLayout>
        <Container className="py-4 text-center">
          <Spinner animation="border" />
        </Container>
      </ResponsiveLayout>
    );
  }

  return (
    <ResponsiveLayout>
      <Container className="py-4">
        <div className="section-title text-center mb-55">
          <h2>Product Cart List</h2>
        </div>

        <Row className="g-3">
          {cartItems.length === 0 ? (
            <Col xs={12}>
              <Card className="shadow-sm border-0">
                <Card.Body className="text-center">
                  <h5>Your cart is empty</h5>
                  <p>Add products from the product page to see them here.</p>
                </Card.Body>
              </Card>
            </Col>
          ) : (
            cartItems.map((item) => {
              const product = item.product || {};
              const image = product.image || product.images?.[0]?.url || "/images/no-image.png";
              const name = product.title || product.name || product.product_name || "Product";
              const price = item.price ?? product.price ?? 0;

              return (
                <Col lg={12} md={12} sm={12} xs={12} key={item.id}>
                  <Card className="shadow-sm border-0">
                    <Card.Body>
                      <Row className="align-items-center">
                        <Col md={3} sm={12} className="mb-3 mb-md-0">
                          <img
                            className="cart-product-img w-100"
                            src={image}
                            alt={name}
                          />
                        </Col>

                        <Col md={5} sm={12}>
                          <h5 className="product-name">{name}</h5>
                          <p className="mb-2">Price: <strong>${price}</strong></p>
                          <p className="mb-0">Total: <strong>${item.quantity * price}</strong></p>
                        </Col>

                        <Col md={2} sm={6} className="mb-3 mb-sm-0">
                          <Form.Control
                            type="number"
                            min="1"
                            value={item.quantity}
                            onChange={(e) => handleQuantityChange(item.id, e.target.value)}
                          />
                        </Col>

                        <Col md={2} sm={6}>
                          <Button
                            variant="danger"
                            className="w-100"
                            onClick={() => removeFromCart(item.id)}
                          >
                            <i className="fa fa-trash-alt"></i> Remove
                          </Button>
                        </Col>
                      </Row>
                    </Card.Body>
                  </Card>
                </Col>
              );
            })
          )}

          {cartItems.length > 0 && (
            <Col lg={12} md={12} sm={12} xs={12}>
              <Card className="shadow-sm border-0">
                <Card.Body>
                  <Row className="align-items-center">
                    <Col md={8} sm={12}>
                      <h5 className="mb-2">Cart Summary</h5>
                      <p className="mb-1">Items: {totalQuantity}</p>
                      <p className="mb-1">Products: {cartItems.length}</p>
                      <p className="mb-0">Total Price: ${totalPrice.toFixed(2)}</p>
                    </Col>
                    <Col md={4} sm={12}>
                      <Button className="btn site-btn w-100" onClick={handleCheckout}>
                        <i className="fa fa-shopping-cart"></i> Checkout
                      </Button>
                    </Col>
                  </Row>
                </Card.Body>
              </Card>
            </Col>
          )}
        </Row>
      </Container>
    </ResponsiveLayout>
  );
};

export default Cart;
