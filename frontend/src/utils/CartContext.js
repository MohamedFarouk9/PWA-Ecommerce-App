import { createContext, useCallback, useEffect, useState } from "react";
import AppURL from "./AppURL";
import apiClient from "../services/apiClient";


export const CartContext = createContext();

const normalizeCartItems = (items) => {
    if (!Array.isArray(items)) return [];

    return items.map((item) => ({
        ...item,
        quantity: Number(item.quantity || 1),
        price: Number(item.price ?? item.product?.price ?? item.product?.special_price ?? 0),
    }));
};

export const CartProvider = ({ children }) => {
    const [cart, setCart] = useState([]);
    const [loading, setLoading] = useState(true);

    const fetchCart = useCallback(async () => {
        try {
            const response = await apiClient.get(AppURL.getCart);
            const cartData = response?.data?.data ?? response?.data ?? [];
            setCart(normalizeCartItems(Array.isArray(cartData) ? cartData : []));
        } catch (error) {
            if (error.response?.status === 401) {
                setCart([]);
            } else {
                console.error("Error fetching cart:", error);
                setCart([]);
            }
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchCart();
    }, [fetchCart]);

    const addToCart = async (product, quantity) => {
        try {
            const response = await apiClient.post(AppURL.addToCart, {
                product_id: product.id,
                quantity,
            });

            await fetchCart();

            return {
                success: true,
                data: response?.data?.data ?? response?.data ?? null,
            };
        } catch (error) {
            if (error.response && error.response.status === 401) {
                return { success: false, isUnauthorized: true };
            }
            console.error("Error adding to cart:", error);
            return { success: false, error: error.message };
        }
    };

    const updateCart = async (id, quantity) => {
        try {
            await apiClient.patch(AppURL.updateCart(id), { quantity });
            await fetchCart();
        } catch (error) {
            console.error("Error updating cart:", error);
        }
    };

    const removeFromCart = async (id) => {
        try {
            await apiClient.delete(`${AppURL.removeFromCart(id)}`);
            await fetchCart();
        } catch (error) {
            console.error("Error removing from cart:", error);
            await fetchCart();
        }
    };

    return (
        <CartContext.Provider value={{ cart, addToCart, updateCart, removeFromCart, refreshCart: fetchCart, loading }}>
            {children}
        </CartContext.Provider>
    );
};