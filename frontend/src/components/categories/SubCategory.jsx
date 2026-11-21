import React, { useEffect, useState } from "react";
import { Container, Row } from "react-bootstrap";
import { Link, useParams } from "react-router-dom";
import Breadcrumb from "react-bootstrap/Breadcrumb";
import axios from "axios";
import AppURL from "../../utils/AppURL";
import ToastMessages from "../../toast-messages/toast";
import { ResponsiveLayout } from "../../layouts/ResponsiveLayout";
import ProductCard from "../Products/ProductCard";
import { ProductSkeleton } from "../ui/LoadingStates";

const SubCategory = () => {
  const { category_slug, subCategory_slug } = useParams();
  const [productData, setProductData] = useState([]);
  const [message, setMessage] = useState("");
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    window.scrollTo(0, 0);

    const fetchProductData = async () => {
      setLoading(true);
      try {
        const response = await axios.get(
          AppURL.ProductBySubCategory(category_slug, subCategory_slug)
        );
        if (response.data?.message) {
          setMessage(response.data.message);
        }
        const items =
          response.data?.data ||
          response.data?.products?.data ||
          response.data?.products ||
          [];
        setProductData(Array.isArray(items) ? items : []);
      } catch (err) {
        setError(
          ToastMessages.showError("Failed to load products Information.")
        );
      } finally {
        setLoading(false);
      }
    };

    fetchProductData();
  }, [category_slug, subCategory_slug]);

  const formatSlug = (s = "") =>
    s.replace(/-/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());

  return (
    <ResponsiveLayout>
      <Container className="py-3">
        <div className="ecom-breadcrumb-bar">
          <Breadcrumb>
            <Breadcrumb.Item linkAs={Link} linkProps={{ to: "/" }}>
              Home
            </Breadcrumb.Item>
            <Breadcrumb.Item
              linkAs={Link}
              linkProps={{ to: `/${category_slug}` }}
            >
              {formatSlug(category_slug)}
            </Breadcrumb.Item>
            <Breadcrumb.Item active>
              {formatSlug(subCategory_slug)}
            </Breadcrumb.Item>
          </Breadcrumb>
        </div>

        <div className="ecom-section-header">
          <div>
            <h2 className="ecom-section-title">
              {formatSlug(subCategory_slug)}
            </h2>
            <p className="ecom-section-subtitle">
              {formatSlug(category_slug)} Department • {productData.length}{" "}
              products
            </p>
          </div>
        </div>

        {loading ? (
          <Row className="g-2">
            <ProductSkeleton count={8} />
          </Row>
        ) : error ? (
          <div className="text-center py-5 text-danger">
            <h5>{error}</h5>
          </div>
        ) : productData.length > 0 ? (
          <Row className="g-2">
            {productData.map((product, index) => (
              <ProductCard key={product.id || index} product={product} />
            ))}
          </Row>
        ) : (
          <div className="text-center py-5">
            <h5>{message || "No products available in this subcategory."}</h5>
            <Link
              to={`/${category_slug}`}
              className="site-btn mt-3 text-decoration-none"
            >
              Back to {formatSlug(category_slug)}
            </Link>
          </div>
        )}
      </Container>
    </ResponsiveLayout>
  );
};

export default SubCategory;
