import React from "react";
import { Link } from "react-router-dom";
import Apple from "../../assets/images/apple.png";
import Google from "../../assets/images/google.png";
import { useSiteSettings } from "../../hooks/useSiteSettings";

const FooterMobile = () => {
  const { settings } = useSiteSettings();

  return (
    <>
      <footer className="ecom-mobile-footer">
        <h6 className="text-white fw-bold mb-2">Shop On The Go</h6>
        <p className="small mb-3">
          Fast delivery, secure payments, and 30-day returns.
        </p>
        <div className="d-flex justify-content-center gap-2 mb-3">
          <a href={settings.androidAppLink || "#"} target="_blank" rel="noopener noreferrer">
            <img src={Google} alt="Google Play" style={{ height: "34px", borderRadius: "6px" }} />
          </a>
          <a href={settings.iosAppLink || "#"} target="_blank" rel="noopener noreferrer">
            <img src={Apple} alt="App Store" style={{ height: "34px", borderRadius: "6px" }} />
          </a>
        </div>
        <div className="d-flex justify-content-center flex-wrap gap-3 small">
          <Link to="/content/about" className="text-secondary text-decoration-none">About</Link>
          <Link to="/content/privacy" className="text-secondary text-decoration-none">Privacy</Link>
          <Link to="/content/refund" className="text-secondary text-decoration-none">Refunds</Link>
          <Link to="/contact" className="text-secondary text-decoration-none">Contact</Link>
        </div>
      </footer>

      <nav className="ecom-mobile-bottom-nav">
        <Link to="/" className="ecom-mobile-tab">
          <i className="fa fa-home"></i>
          <span>Home</span>
        </Link>
        <Link to="/favourite" className="ecom-mobile-tab">
          <i className="fa fa-heart"></i>
          <span>Wishlist</span>
        </Link>
        <Link to="/cart" className="ecom-mobile-tab">
          <i className="fa fa-shopping-bag"></i>
          <span>Cart</span>
        </Link>
        <Link to="/orders" className="ecom-mobile-tab">
          <i className="fa fa-box"></i>
          <span>Orders</span>
        </Link>
        <Link to="/profile" className="ecom-mobile-tab">
          <i className="fa fa-user"></i>
          <span>Account</span>
        </Link>
      </nav>
    </>
  );
};

export default FooterMobile;
