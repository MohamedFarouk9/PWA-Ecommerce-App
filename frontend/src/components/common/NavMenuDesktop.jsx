import React, { useContext, useState } from "react";
import Container from "react-bootstrap/Container";
import { Link, useNavigate } from "react-router-dom";
import Logo from "../../assets/images/logo.jpg";
import MegaMenuDesktop from "../home/MegaMenuDesktop";
import { AuthContext } from "../../utils/AuthContext";
import { CartContext } from "../../utils/CartContext";


const NavMenuDesktop = () => {
  const [sideNavState, setSideNavState] = useState("sideNavClose");
  const [contentOverState, setContentOverState] = useState(
    "ContentOverlayClose"
  );
  const [searchKey, setSearchKey] = useState(""); // State for search input
  const navigate = useNavigate(); // Hook for navigation
  const { token, user, logout } = useContext(AuthContext);
  const { cart } = useContext(CartContext) || {};

  const wishlistCount = (() => {
    try {
      return JSON.parse(localStorage.getItem("ecommerce_wishlist") || "[]").length;
    } catch {
      return 0;
    }
  })();

  const handleLogout = () => {
    logout();
    navigate('/');
  }

  const sideNavOpenClose = () => {
    if (sideNavState === "sideNavOpen") {
      setSideNavState("sideNavClose");
      setContentOverState("ContentOverlayClose");
    } else {
      setSideNavState("sideNavOpen");
      setContentOverState("ContentOverlayOpen");
    }
  };


  const handleSearch = (e) => {
    if (e) e.preventDefault();
    if (searchKey.trim().length > 0) {
      navigate(`/search/${encodeURIComponent(searchKey.trim())}`);
    }
  };

  return (
    <>
      {/* Top Promotional Announcement Strip */}
      <div className="ecom-topbar d-none d-md-block">
        <Container className="d-flex justify-content-between align-items-center">
          <div>
            <i className="fa fa-bolt text-warning me-2"></i>
            <span>Free Express Shipping on orders over <strong>$99</strong> — Use code <strong>SAVE20</strong></span>
          </div>
          <div className="d-flex gap-4">
            <Link to="/orders"><i className="fa fa-truck me-1"></i> Track Order</Link>
            <Link to="/contact"><i className="fa fa-headset me-1"></i> 24/7 Support</Link>
          </div>
        </Container>
      </div>

      {/* Main Sticky Glass Navbar */}
      <header className="ecom-navbar-wrapper">
        <Container>
          <div className="ecom-navbar-inner">
            {/* Brand + Drawer Trigger */}
            <div className="d-flex align-items-center gap-3">
              <button
                type="button"
                onClick={sideNavOpenClose}
                className="ecom-menu-trigger"
                aria-label="Toggle Categories"
              >
                <i className="fa fa-bars"></i>
              </button>
              <Link to="/" className="ecom-brand">
                <img className="ecom-brand-logo" src={Logo} alt="logo" />
              </Link>
            </div>

            {/* Pill Search Bar */}
            <form className="ecom-search-form" onSubmit={handleSearch}>
              <div className="ecom-search-group">
                <input
                  type="text"
                  className="ecom-search-input"
                  placeholder="Search for products, brands, and categories..."
                  value={searchKey}
                  onChange={(e) => setSearchKey(e.target.value)}
                />
                <button
                  type="submit"
                  className="ecom-search-btn"
                  aria-label="Search"
                >
                  <i className="fa fa-search"></i>
                </button>
              </div>
            </form>

            {/* Right Action Icons & Account Links */}
            <div className="ecom-nav-actions">
              <Link to="/favourite" className="ecom-icon-btn" title="Wishlist">
                <i className="fa fa-heart"></i>
                {wishlistCount > 0 && (
                  <span className="ecom-badge-count">{wishlistCount}</span>
                )}
              </Link>

              <Link to="/notification" className="ecom-icon-btn" title="Notifications">
                <i className="fa fa-bell"></i>
                <span className="ecom-badge-count">5</span>
              </Link>

              {token ? (
                <>
                  {user?.role === 'admin' && (
                    <button
                      type="button"
                      className="ecom-nav-link text-warning"
                      onClick={() => navigate("/admin/dashboard")}
                    >
                      <i className="fa fa-chart-line"></i> Admin
                    </button>
                  )}
                  <button
                    type="button"
                    className="ecom-nav-link"
                    onClick={() => navigate("/orders")}
                  >
                    <i className="fa fa-box"></i> Orders
                  </button>
                  <button
                    type="button"
                    className="ecom-nav-link"
                    onClick={() => navigate("/profile")}
                  >
                    <i className="fa fa-user-circle"></i> Profile
                  </button>
                  <button
                    type="button"
                    className="ecom-nav-link text-danger"
                    onClick={handleLogout}
                    title="Logout"
                  >
                    <i className="fa fa-sign-out-alt"></i>
                  </button>
                </>
              ) : (
                <>
                  <Link to="/login" className="ecom-nav-link">
                    <i className="fa fa-user"></i> Sign In
                  </Link>
                  <Link to="/register" className="ecom-nav-link d-none d-xl-inline-flex">
                    Register
                  </Link>
                </>
              )}

              <Link to="/cart" className="ecom-cart-pill">
                <i className="fa fa-shopping-bag"></i>
                <span>{cart?.length || 0} Items</span>
              </Link>
            </div>
          </div>
        </Container>
      </header>

      <div className={sideNavState}>
        <MegaMenuDesktop />
      </div>

      <div onClick={sideNavOpenClose} className={contentOverState}></div>
    </>
  );
};

export default NavMenuDesktop;
