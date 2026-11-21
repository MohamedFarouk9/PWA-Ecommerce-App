import React, { useContext, useEffect, useState } from "react";
import Profile from "../components/common/Profile";
import { AuthContext } from "../utils/AuthContext";
import { Container, Spinner } from "react-bootstrap";
import { useNavigate } from "react-router-dom";
import { ResponsiveLayout } from "../layouts/ResponsiveLayout";

const ProfilePage = () => {
  const { user, loading, logout } = useContext(AuthContext);
  const navigate = useNavigate();
  const [isChecking, setIsChecking] = useState(true);

  useEffect(() => {
    window.scroll(0, 0);
    const timer = setTimeout(() => {
      setIsChecking(false);
    }, 300);
    return () => clearTimeout(timer);
  }, []);

  useEffect(() => {
    if (!loading && !isChecking && !user) {
      navigate("/login");
    }
  }, [loading, isChecking, user, navigate]);

  if (loading || isChecking) {
    return (
      <ResponsiveLayout>
        <Container className="text-center py-5">
          <Spinner animation="border" variant="primary" />
          <p className="text-muted mt-2">Loading your profile...</p>
        </Container>
      </ResponsiveLayout>
    );
  }

  if (!user) return null;

  return (
    <ResponsiveLayout>
      <Profile userData={user} logout={logout} />
    </ResponsiveLayout>
  );
};

export default ProfilePage;
