import React, { useContext, useEffect, useState } from "react";
import { Container, Row, Col, Card, Button } from "react-bootstrap";
import { Link } from "react-router-dom";
import { ResponsiveLayout } from "../../layouts/ResponsiveLayout";
import { CartContext } from "../../utils/CartContext";
import apiClient from "../../services/apiClient";
import AppURL from "../../utils/AppURL";
import ToastMessages from "../../toast-messages/toast";

const FALLBACK_IMG =
  "https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=80";

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
        const defaultSample = [
          {
            id: 1,
            title: "MacBook Pro 16 M3 Max",
            price: 3299.0,
            image:
              "https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80",
          },
          {
            id: 2,
            title: "Sony WH-1000XM5 Wireless Headphones",
            price: 349.99,
            image:
              "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=80",
          },
        ];
        setFavorites(defaultSample);
        localStorage.setItem(
          "ecommerce_wishlist",
          JSON.stringify(defaultSample)
        );
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
    const itemName = item.title || item.name || "Product";
    try {
      await apiClient.post(AppURL.addToCart, {
        product_id: item.id,
        quantity: 1,
      });

      if (fetchCart) {
        fetchCart();
      }

      ToastMessages.showSuccess(`Added "${itemName}" to cart.`);
    } catch (err) {
      ToastMessages.showError("Please sign in to move items to your cart.");
    }
  };

  return (
    <ResponsiveLayout>
      <Container className="py-4">
        <div className="ecom-section-header">
          <div>
            <h2 className="ecom-section-title">Saved Wishlist</h2>
            <p className="ecom-section-subtitle">
              Keep track of your favorite products and move them to cart anytime
            </p>
          </div>
          <Link to="/" className="btn btn-outline-primary btn-sm">
            <i className="fa fa-arrow-left me-1"></i> Continue Shopping
          </Link>
        </div>

        {favorites.length === 0 ? (
          <Card className="text-center py-5">
            <Card.Body>
              <div
                className="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle"
                style={{
                  width: 72,
                  height: 72,
                  background: "#fef2f2",
                  color: "var(--ecom-danger)",
                }}
              >
                <i className="fa fa-heart fa-2x"></i>
              </div>
              <h4 className="fw-bold">Your wishlist is empty</h4>
              <p className="text-muted mb-4">
                Tap the heart icon on any product card to save it here for later.
              </p>
              <Link to="/" className="site-btn text-decoration-none">
                <i className="fa fa-shopping-bag"></i> Discover Products
              </Link>
            </Card.Body>
          </Card>
        ) : (
          <Row className="g-3">
            {favorites.map((item) => {
              const title = item.title || item.name || "Saved Product";
              const img = item.image || FALLBACK_IMG;

              return (
                <Col key={item.id} xl={3} lg={4} md={6} sm={12}>
                  <div className="ecom-product-card">
                    <div className="ecom-product-media">
                      <button
                        type="button"
                        onClick={() => removeFavorite(item.id)}
                        className="ecom-wishlist-trigger active"
                        title="Remove from Wishlist"
                      >
                        <i className="fa fa-trash-alt"></i>
                      </button>
                      <Link
                        to={`/product-details/${item.id}`}
                        className="w-100 h-100"
                      >
                        <img
                          className="ecom-product-img"
                          src={img}
                          alt={title}
                          onError={(e) => {
                            e.target.onerror = null;
                            e.target.src = FALLBACK_IMG;
                          }}
                        />
                      </Link>
                    </div>

                    <div className="ecom-product-body">
                      <Link
                        to={`/product-details/${item.id}`}
                        className="text-link"
                      >
                        <h6 className="ecom-product-title">{title}</h6>
                      </Link>

                      <div className="ecom-product-footer">
                        <span className="ecom-price-current">
                          ${Number(item.price || 0).toFixed(2)}
                        </span>
                        <Button
                          size="sm"
                          className="site-btn py-1 px-3"
                          onClick={() => moveToCart(item)}
                        >
                          <i className="fa fa-cart-plus"></i> Add
                        </Button>
                      </div>
                    </div>
                  </div>
                </Col>
              );
            })}
          </Row>
        )}
      </Container>
    </ResponsiveLayout>
  );
};

export default Favourite;
