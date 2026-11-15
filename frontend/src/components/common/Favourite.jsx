import React, { useContext, useEffect, useState } from "react";
import { Container, Row, Col, Card, Button, Badge } from "react-bootstrap";
import { Link } from "react-router-dom";
import { ResponsiveLayout } from "../../layouts/ResponsiveLayout";
import { CartContext } from "../../utils/CartContext";
import apiClient from "../../services/apiClient";
import AppURL from "../../utils/AppURL";
import ToastMessages from "../../toast-messages/toast";

const Favourite = () => {
  const [favorites, setFavorites] = useState([]);
  const { fetchCart } = useContext(CartContext) || {};

  useEffect(() => {
    window.scrollTo({ top: 0, behavior: "smooth" });
    loadFavorites();
  }, []);

  const loadFavorites = () => {
    try {
      const stored = localStorage.getItem("ecommerce_wishlist");
      if (stored) {
        setFavorites(JSON.parse(stored));
      } else {
        // Fallback default sample favorites if none stored yet
        const defaultSample = [
          {
            id: 1,
            name: "ASUS TUF Gaming A15",
            price: 999.99,
            image: "https://images.unsplash.com/photo-1603302576837-37561b2e2302?auto=format&fit=crop&w=500&q=80",
          },
          {
            id: 2,
            name: "Sony WH-1000XM4 Wireless Headphones",
            price: 348.00,
            image: "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=500&q=80",
          },
        ];
        setFavorites(defaultSample);
        localStorage.setItem("ecommerce_wishlist", JSON.stringify(defaultSample));
      }
    } catch (e) {
      console.error("Error reading wishlist:", e);
    }
  };

  const removeFavorite = (id) => {
    const updated = favorites.filter((item) => item.id !== id);
    setFavorites(updated);
    localStorage.setItem("ecommerce_wishlist", JSON.stringify(updated));
    ToastMessages.showSuccess("Item removed from your wishlist.");
  };

  const moveToCart = async (item) => {
    try {
      await apiClient.post(AppURL.addToCart, {
        product_id: item.id,
        quantity: 1,
      });

      if (fetchCart) {
        fetchCart();
      }

      ToastMessages.showSuccess(`Added "${item.name}" to cart.`);
    } catch (err) {
      console.error("Failed to add wishlisted item to cart:", err);
      ToastMessages.showError("Failed to add item to cart. Please try again.");
    }
  };

  return (
    <ResponsiveLayout>
      <Container className="py-4">
        <div className="section-title text-center mb-4">
          <h2 className="fw-bold">My Saved Wishlist</h2>
          <p className="text-muted">Manage your saved products and move them to cart anytime</p>
        </div>

        {favorites.length === 0 ? (
          <Card className="shadow-sm border-0 text-center py-5">
            <Card.Body>
              <i className="fa fa-heart-broken fa-4x text-muted mb-3"></i>
              <h4>Your wishlist is empty</h4>
              <p className="text-muted">You haven't added any favorite products yet.</p>
              <Link to="/" className="btn btn-primary mt-2">
                <i className="fa fa-shopping-bag me-1"></i> Discover Products
              </Link>
            </Card.Body>
          </Card>
        ) : (
          <Row className="g-3">
            {favorites.map((item) => (
              <Col key={item.id} xl={3} lg={4} md={6} sm={12}>
                <Card className="h-100 shadow-sm border-0 product-card">
                  <div style={{ height: 200, overflow: "hidden", display: "flex", alignItems: "center", justifyContent: "center" }} className="bg-light p-2">
                    <Card.Img
                      variant="top"
                      src={item.image || "https://placehold.co/400x300?text=Product"}
                      alt={item.name}
                      style={{ maxHeight: "100%", maxWidth: "100%", objectFit: "contain" }}
                    />
                  </div>
                  <Card.Body className="d-flex flex-column justify-content-between">
                    <div>
                      <h6 className="fw-semibold text-truncate mb-2">{item.name}</h6>
                      <div className="text-primary fw-bold fs-5 mb-3">
                        ${Number(item.price).toFixed(2)}
                      </div>
                    </div>

                    <div className="d-grid gap-2">
                      <Button
                        variant="primary"
                        size="sm"
                        onClick={() => moveToCart(item)}
                      >
                        <i className="fa fa-shopping-cart me-1"></i> Move to Cart
                      </Button>
                      <Button
                        variant="outline-danger"
                        size="sm"
                        onClick={() => removeFavorite(item.id)}
                      >
                        <i className="fa fa-trash-alt me-1"></i> Remove
                      </Button>
                    </div>
                  </Card.Body>
                </Card>
              </Col>
            ))}
          </Row>
        )}
      </Container>
    </ResponsiveLayout>
  );
};

export default Favourite;
