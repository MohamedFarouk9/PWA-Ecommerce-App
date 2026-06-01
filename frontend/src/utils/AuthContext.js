import { createContext, useEffect, useState } from "react";
import AppURL from "./AppURL";
import apiClient from "../services/apiClient";

export const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
    const [token, setToken] = useState(localStorage.getItem("auth_token"));
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(false); // Start with false - only load on token change
    const [hasFetched, setHasFetched] = useState(false);

    const logout = () => {
        localStorage.removeItem("auth_token");
        setToken(null);
        setUser(null);
        setHasFetched(false);
    };

    const login = (newToken) => {
        localStorage.setItem("auth_token", newToken);
        setToken(newToken);
        setHasFetched(false);
    };

    // Only fetch user data if token exists AND we haven't fetched yet
    useEffect(() => {
        if (token && !hasFetched) {
            setLoading(true);
            setHasFetched(true);
            const fetchUserData = async () => {
                try {
                    const response = await apiClient.get(AppURL.UserProfile);
                    setUser(response.data);
                    setLoading(false);
                } catch (error) {
                    console.error("Error fetching user data:", error);
                    // Token is invalid/expired, clear everything
                    localStorage.removeItem("auth_token");
                    setToken(null);
                    setUser(null);
                    setLoading(false);
                    // Don't reset hasFetched - we already tried and failed
                }
            };
            fetchUserData();
        }
    }, [token, hasFetched]);

    return (
        <AuthContext.Provider value={{ token, user, login, logout, loading }}>
            {children}
        </AuthContext.Provider>
    );
};
