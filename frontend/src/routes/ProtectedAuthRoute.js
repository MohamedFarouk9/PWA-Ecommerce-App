import { useContext, useEffect } from "react";
import { AuthContext } from "../utils/AuthContext";
import { Outlet, useNavigate } from "react-router-dom";

const ProtectedAuthRoute = () => {
  const { user, loading } = useContext(AuthContext);
  const navigate = useNavigate();
  const redirectUrl = sessionStorage.getItem("redirectUrl") || "/profile";

  useEffect(() => {
    if (user) {
      navigate(redirectUrl, { replace: true });
      sessionStorage.removeItem("redirectUrl");
    }
  }, [user, navigate, redirectUrl]);

  if (loading) return null;

  return user ? null : <Outlet />;
};

export default ProtectedAuthRoute;
