import React, { useContext, useState } from "react";
import Container from "react-bootstrap/Container";
import { Link, useNavigate } from "react-router-dom"; // Added useNavigate for navigation
import Navbar from "react-bootstrap/Navbar";
import Row from "react-bootstrap/Row";
import Col from "react-bootstrap/Col";
import Button from "react-bootstrap/Button";
import Logo from "../../assets/images/logo.jpg";
import MegaMenuDesktop from "../home/MegaMenuDesktop";
import Bars from "../../assets/images/bars.png";
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


  const handleSearch = async () => {
    if (searchKey.trim().length > 0) {
      navigate(`/search/${encodeURIComponent(searchKey)}`); // Pass the searchKey in the URL
    } else {
      alert("Please enter a search term");
    }
  };

  return (
    <>
      <div className="TopSectionDown">
        <Navbar className="navbar" fixed={"top"}>
          <Container
            fluid={"true"}
            className="fixed-top shadow-sm p-2 mb-0 bg-white"
          >
            <Row>
              <Col lg={4} md={4} sm={12} xs={12}>
                <img
                  onClick={sideNavOpenClose}
                  className="bar-img"
                  alt="bar"
                  src={Bars}
                />
                <Link to="/">
                  <img className="nav-logo" src={Logo} alt="logo" />
                </Link>
              </Col>

              <Col className="p-1 mt-1" lg={4} md={4} sm={12} xs={12}>
                <div className="input-group w-100">
                  <input
                    type="text"
                    className="form-control"
                    placeholder="Search here..."
                    value={searchKey}
                    onChange={(e) => setSearchKey(e.target.value)} // Update search input
                  />
                  <Button
                    type="button"
                    className="btn site-btn"
                    onClick={handleSearch}
                  >
                    <i className="fa fa-search"></i>
                  </Button>
                </div>
              </Col>

              <Col className="p-1 mt-1" lg={4} md={4} sm={12} xs={12}>
                <Link to="/favourite" className="btn">
                  <i className="fa h4 fa-heart"></i>
                  <sup>
                    <span className="badge text-white bg-danger"> {wishlistCount} </span>
                  </sup>
                </Link>

                <Link to="/notification" className="btn">
                  <i className="fa h4 fa-bell"></i>
                  <sup>
                    <span className="badge text-white bg-danger"> 5</span>
                  </sup>
                </Link>

                {/* Show username when logged in, otherwise show Login/Register */}
           
              {token ? (
                <>
                    {user?.role === 'admin' && (
                      <button className="h4 btn text-warning fw-bold" onClick={()=> navigate("/admin/dashboard")}>
                        Dashboard
                      </button>
                    )}
                    <button className="h4 btn" onClick={()=> navigate("/orders")}>
                      Orders
                    </button>
                    <button className="h4 btn" onClick={()=> navigate("/profile")}>
                      Profile
                    </button>
                    <button className="h4 btn" onClick={handleLogout}>
                      Logout
                    </button>
                </>
              ): (
                <>
                  <Link to="/login" className="h4 btn">
                    Login
                  </Link>

                  <Link to="/register" className="h4 btn">
                    Register
                  </Link>
                </>
              )}

                <Link to="/cart">
                  <Button className="cart-btn">
                    <i className="fa fa-shopping-cart"></i> {cart?.length || 0} Items
                  </Button>
                </Link>
              </Col>
            </Row>
          </Container>
        </Navbar>
      </div>

      <div className={sideNavState}>
        <MegaMenuDesktop />
      </div>

      <div onClick={sideNavOpenClose} className={contentOverState}></div>
    </>
  );
};

export default NavMenuDesktop;
