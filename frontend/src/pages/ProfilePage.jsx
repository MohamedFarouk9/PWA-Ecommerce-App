import React, { useContext, useEffect, useState } from "react";
import FooterDesktop from "../components/common/FooterDesktop";
import FooterMobile from "../components/common/FooterMobile";
import NavMenuDesktop from "../components/common/NavMenuDesktop";
import NavMenuMobile from "../components/common/NavMenuMoblie";
import Profile from "../components/common/Profile";
import { AuthContext } from "../utils/AuthContext";
import { Container } from "react-bootstrap";
import { useNavigate } from "react-router-dom";

const ProfilePage = () => {
  const { user, loading, logout } = useContext(AuthContext);
  const navigate = useNavigate();
  const [isChecking, setIsChecking] = useState(true); // Prevents flickering

  useEffect(() => {
    window.scroll(0, 0);

    // Start a timer that sets 'isChecking' to false after 500ms
    const timer = setTimeout(() => {
      setIsChecking(false);
    }, 500);

    // Cleanup function to clear the timeout if component unmounts
    return () => clearTimeout(timer);
  }, []); // Empty dependency array to run only once

  if (loading || isChecking) {
    return (
      <Container className="text-center">
        <h2>Loading...</h2>
      </Container>
    );
  }

  if (!user) {
    return navigate("/login"); // Redirect if not logged in
  }

  return (
    <>
      <div className="Desktop">
        <NavMenuDesktop />
      </div>

      <div className="Mobile">
        <NavMenuMobile />
      </div>

      <Profile userData={user} logout={logout} />

      <div className="Desktop">
        <FooterDesktop />
      </div>

      <div className="Mobile">
        <FooterMobile />
      </div>
    </>
  );
};

export default ProfilePage;
