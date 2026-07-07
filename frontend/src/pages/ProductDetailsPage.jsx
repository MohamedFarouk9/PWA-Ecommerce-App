import React, { useEffect, useState } from "react";
import ProductDetails from "../components/ProductDetails/ProductDetails";
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
        const payload = response.data?.data?.product ?? response.data?.product ?? response.data;

        if (response.data?.message) {
          setMessage(response.data.message);
        }

        // Normalize payload so components expecting { product: {...} } keep working
        if (payload && payload.id) {
          setProductData({ product: payload });
        } else {
          setProductData(payload);
        }
      } catch (error) {
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
      <ProductDetails productData={productData} message={message} />
    </ResponsiveLayout>
  );
};

export default ProductDetailsPage;
