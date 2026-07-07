import React, { useEffect, useState } from "react";
import { Container, Row, Col, Button, Card } from "react-bootstrap";
import { useNavigate } from "react-router-dom";
import Product1 from "../../assets/images/product/product1.png";
import { ResponsiveLayout } from "../../layouts/ResponsiveLayout";

const Cart = () => {
  const navigate = useNavigate();

  useEffect(() => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  }, []);

  // State to manage cart items
  const [cartItems, setCartItems] = useState([
    {
      id: 1,
      name: "ASUS TUF A15 FA506IU Ryzen 7 4800H GTX",
      quantity: 5,
      price: 100,
      image: Product1,
    },
    {
      id: 2,
      name: "Infinix Hot 10 Play",
      quantity: 2,
      price: 150,
      image:
        "https://rukminim1.flixcart.com/image/416/416/knm2s280/mobile/j/x/c/hot-10-play-x688b-infinix-original-imag29gxqzuxkmnk.jpeg?q=70",
    },
  ]);

  // Function to remove an item from the cart
  const removeItem = (id) => {
    setCartItems(cartItems.filter((item) => item.id !== id));
  };

  // Function to update the quantity of an item
  const updateQuantity = (id, newQuantity) => {
    setCartItems(
      cartItems.map((item) =>
        item.id === id ? { ...item, quantity: newQuantity } : item
      )
    );
  };

  // Calculate total quantity and total price
  const totalQuantity = cartItems.reduce((acc, item) => acc + item.quantity, 0);
  const totalPrice = cartItems.reduce(
    (acc, item) => acc + item.quantity * item.price,
    0
  );

  return (
    <>
      <ResponsiveLayout>
        <Container>
          <div className="section-title text-center mb-55">
            <h2>Product Cart List</h2>
          </div>

          <Row>
            {cartItems.map((item) => (
              <Col
                className="p-1"
                lg={12}
                md={12}
                sm={12}
                xs={12}
                key={item.id}
              >
                <Card>
                  <Card.Body>
                    <Row>
                      <Col md={3} lg={3} sm={6} xs={6}>
                        <img
                          className="cart-product-img"
                          src={item.image}
                          alt={item.name}
                        />
                      </Col>

                      <Col md={6} lg={6} sm={6} xs={6}>
                        <h5 className="product-name">{item.name}</h5>
                        <h6> Quantity = {item.quantity} </h6>
                        <h6>
                          Price = {item.quantity} x ${item.price} = $
                          {item.quantity * item.price}
                        </h6>
                      </Col>

                      <Col md={3} lg={3} sm={12} xs={12}>
                        <input
                          value={item.quantity}
                          onChange={(e) =>
                            updateQuantity(item.id, parseInt(e.target.value))
                          }
                          className="form-control text-center"
                          type="number"
                        />
                        <Button
                          className="btn btn-block w-100 mt-3 site-btn"
                          onClick={() => removeItem(item.id)}
                        >
                          <i className="fa fa-trash-alt"></i> Remove
                        </Button>
                      </Col>
                    </Row>
                  </Card.Body>
                </Card>
              </Col>
            ))}

            <Col className="p-1" lg={12} md={12} sm={12} xs={12}>
              <Card>
                <Card.Body>
                  <Row>
                    <Col md={4} lg={4} sm={6} xs={6}>
                      <h5>Total Items = {totalQuantity}</h5>
                      <h5>Total Price = ${totalPrice}</h5>
                      <Button
                        className="btn btn-block w-100 mt-3 site-btn"
                        onClick={() =>
                          navigate("/payment", {
                            state: {
                              order: {
                                items: cartItems,
                                totalQuantity,
                                totalPrice,
                              },
                            },
                          })
                        }
                      >
                        <i className="fa fa-shopping-cart"></i> CheckOut
                      </Button>
                    </Col>
                  </Row>
                </Card.Body>
              </Card>
            </Col>
          </Row>
        </Container>
      </ResponsiveLayout>
    </>
  );
};

export default Cart;
