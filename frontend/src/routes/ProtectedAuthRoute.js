import { useContext, useState , useEffect} from "react"
import { AuthContext } from "../utils/AuthContext"
import { Navigate, Outlet, useNavigate } from "react-router-dom";

const ProtectedAuthRoute = ()=> {
    const {user , loading } = useContext(AuthContext);
    const navigate = useNavigate();
    const [redirectUrl, setRedirectUrl] = useState(
        sessionStorage.getItem("redirectUrl") || "/profile"
    );

    useEffect(() => {
        if (user) {
            navigate(redirectUrl, { replace: true });
            sessionStorage.removeItem("redirectUrl"); // Remove it after navigation
        }
    }, [user, navigate, redirectUrl]);

    if(loading) return null ; // Wait until authentication status is loaded
    
    return user ? null : <Outlet />;}

export default ProtectedAuthRoute;
