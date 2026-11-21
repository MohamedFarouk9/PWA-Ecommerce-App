import React, { useContext, useState } from "react";
import Container from "react-bootstrap/Container";
import { Link, useNavigate } from "react-router-dom";
import Logo from "../../assets/images/logo.jpg";
import MegaMenuMobile from "../home/MegaMenuMobile";
import { CartContext } from "../../utils/CartContext";

const NavMenuMobile = () => {
  const { cart } = useContext(CartContext) || {};
  const [sideNavState, setSideNavState] = useState("sideNavClose");
  const [contentOverState, setContentOverState] = useState("ContentOverlayClose");
  const [searchKey, setSearchKey] = useState("");
  const navigate = useNavigate();

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
      <header className="ecom-navbar-wrapper">
        <Container className="py-2">
          <div className="d-flex align-items-center justify-content-between mb-2">
            <div className="d-flex align-items-center gap-2">
              <button
                type="button"
                onClick={sideNavOpenClose}
                className="ecom-menu-trigger"
                aria-label="Open menu"
              >
                <i className="fa fa-bars"></i>
              </button>
              <Link to="/" className="ecom-brand">
                <img className="ecom-brand-logo" src={Logo} alt="logo" />
              </Link>
            </div>

            <Link to="/cart" className="ecom-cart-pill">
              <i className="fa fa-shopping-bag"></i>
              <span>{cart?.length || 0}</span>
            </Link>
          </div>

          <form onSubmit={handleSearch}>
            <div className="ecom-search-group">
              <input
                type="text"
                className="ecom-search-input"
                placeholder="Search products, brands..."
                value={searchKey}
                onChange={(e) => setSearchKey(e.target.value)}
              />
              <button type="submit" className="ecom-search-btn" aria-label="Search">
                <i className="fa fa-search"></i>
              </button>
            </div>
          </form>
        </Container>
      </header>

      <div className={sideNavState}>
        <div className="d-flex justify-content-between align-items-center px-2 pb-2 mb-2 border-bottom">
          <strong className="text-dark">Categories</strong>
          <button
            type="button"
            className="btn btn-sm btn-light"
            onClick={sideNavOpenClose}
          >
            <i className="fa fa-times"></i>
          </button>
        </div>
        <MegaMenuMobile />
      </div>

      <div onClick={sideNavOpenClose} className={contentOverState}></div>
    </>
  );
};

export default NavMenuMobile;
