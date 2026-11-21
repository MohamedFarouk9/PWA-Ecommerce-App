import React, { useEffect, useState } from "react";
import { Container, Row } from "react-bootstrap";
import apiClient from "../../services/apiClient";
import AppURL from "../../utils/AppURL";
import ProductCard from "../Products/ProductCard";
import { ProductSkeleton } from "../ui/LoadingStates";

const EnhancedSuggestedProduct = ({ product_id }) => {
  const [relatedProducts, setRelatedProducts] = useState([]);
  const [isLoading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (!product_id) {
      setLoading(false);
      return;
    }

    const fetchRelatedProducts = async () => {
      setLoading(true);
      try {
        const response = await apiClient.get(
          AppURL.relatedProduct(product_id)
        );
        const data =
          response.data?.data?.related_products ??
          response.data?.related_products ??
          response.data;

        setRelatedProducts(data?.products ?? data ?? []);
      } catch (err) {
        setError(
          err?.response?.data?.message ?? "Failed to load related products."
        );
      } finally {
        setLoading(false);
      }
    };

    fetchRelatedProducts();
  }, [product_id]);

  if (error) return null;

  return (
    <Container className="my-5">
      <div className="ecom-section-header">
        <div>
          <h2 className="ecom-section-title">Similar Products You May Like</h2>
          <p className="ecom-section-subtitle">
            Customers who viewed this item also explored these recommendations
          </p>
        </div>
      </div>

      {isLoading ? (
        <Row className="g-2">
          <ProductSkeleton count={4} />
        </Row>
      ) : !relatedProducts || relatedProducts.length === 0 ? (
        <p className="text-muted">No related products found.</p>
      ) : (
        <Row className="g-2">
          {relatedProducts.map((product, index) => (
            <ProductCard key={product.id || index} product={product} />
          ))}
        </Row>
      )}
    </Container>
  );
};

export default EnhancedSuggestedProduct;
