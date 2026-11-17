import React, { useEffect, useState } from "react";
import EnhancedProductDetails from "../components/ProductDetails/EnhancedProductDetails";
import { useParams } from "react-router";
import AppURL from "../utils/AppURL";
import ToastMessages from "../toast-messages/toast";
import { ResponsiveLayout } from "../layouts/ResponsiveLayout";
import apiClient from "../services/apiClient";

const ProductDetailsPage = () => {
  const { productId } = useParams();
  const [productData, setProductData] = useState([]);
  const [message, setMessage] = useState("");
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    window.scroll(0, 0);

    const fetchProductDetails = async () => {
      try {
        const response = await apiClient.get(AppURL.ProductDetails(productId));
        let payload = response.data?.data?.product ?? response.data?.product ?? response.data;

        if (response.data?.message) {
          setMessage(response.data.message);
        }

        // Ensure payload has expected structure
        if (!payload || !payload.id) {
          setError("Product not found");
          return;
        }

        // Ensure nested objects are properly initialized
        if (!payload.rating) {
          payload.rating = { average: 0, count: 0, breakdown: {} };
        }
        if (!payload.images) {
          payload.images = [];
        }
        if (!payload.reviews) {
          payload.reviews = [];
        }

        setProductData({ product: payload });
      } catch (error) {
        console.error("Product fetch error:", error);
        setError(
          ToastMessages.showError("Failed to load product details Information.")
        );
      } finally {
        setLoading(false);
      }
    };

    fetchProductDetails();
  }, [productId]);

  return (
    <ResponsiveLayout>
      <EnhancedProductDetails productData={productData} message={message} />
    </ResponsiveLayout>
  );
};

export default ProductDetailsPage;
