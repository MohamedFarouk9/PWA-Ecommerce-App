import React, { useEffect, useState } from "react";
import { Card, Col, Container, Row } from "react-bootstrap";
import { Link, useParams } from "react-router-dom";
import Breadcrumb from "react-bootstrap/Breadcrumb";
import axios from "axios";
import AppURL from "../../utils/AppURL";
import ToastMessages from "../../toast-messages/toast";
import { ResponsiveLayout } from "../../layouts/ResponsiveLayout";

const Category = () => {
  const { slug } = useParams();
  const [productData, setProductData] = useState([]);
  const [message, setMessage] = useState('');
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    window.scroll(0, 0);

    const fetchProductData = async () => {
      try {
        const response = await axios.get(AppURL.ProductByCategory(slug));
        if(response.data.message){
          setMessage(response.data.message);
        }
        const items = response.data?.data || response.data?.products?.data || response.data?.products || [];
        setProductData(Array.isArray(items) ? items : []);
      } catch (error) {
        setError(
          ToastMessages.showError("Failed to load products Information.")
        );
      } finally {
        setLoading(false);
      }
    };

    fetchProductData();
  }, [slug]);

  const renderProducts = productData.length > 0 ? (
    productData.map((product, index) => (
      <Col className="p-0" key={index} xl={3} lg={3} md={3} sm={6} xs={6}>
        <Link className="text-link" to={`/product-details/${product.id}`}>
          <Card className="image-box w-100">
            <Card.Body>
              <Card.Img className="center w-75" src={product.image} alt={product.title} />
              <h5 className="product-name-on-card">{product.title}</h5>
              {product.special_price === "na" ? (
                <p className="product-price-on-card">
                  Price : ${product.price}
                </p>
              ) : (
                <p className="product-price-on-card">
                  Price : <del className="text-secondary">${product.price}</del> ${product.special_price}
                </p>
              )}
            </Card.Body>
          </Card>
        </Link>
      </Col>
    ))
  ) : (
    <Col>
      <h5>{message || 'No products available in this category.'}</h5>
    </Col>
  );

  const renderContent = () => {
    if (error) {
      return (
        <Container className="text-center">
          <h4>{error}</h4>
        </Container>
      );
    }

    if (loading) {
      return (
        <Container className="text-center">
          <h4>Loading Categories ...</h4>
        </Container>
      );
    }

    return (
      <Container className="text-center" fluid={true}>
        <div className="breadbody">
          <Breadcrumb>
            <Breadcrumb.Item><Link className="text-link" to="/">Home</Link></Breadcrumb.Item>
            <Breadcrumb.Item>{slug}</Breadcrumb.Item>
          </Breadcrumb>
        </div>
        <div className="section-title text-center mb-55">
          <h2>{slug}</h2>
        </div>
        <Row>{renderProducts}</Row>
      </Container>
    );
  };

  return (
    <ResponsiveLayout>
      {renderContent()}
    </ResponsiveLayout>
  );
};

export default Category;
