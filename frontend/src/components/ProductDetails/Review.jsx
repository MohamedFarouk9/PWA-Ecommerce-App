import React, { useContext, useEffect, useState } from "react";
import { Col, Container } from "react-bootstrap";
import AppURL from "../../utils/AppURL";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";
import ToastMessages from "../../toast-messages/toast";
import { AuthContext } from "../../utils/AuthContext";
import apiClient from "../../services/apiClient";

const Review = ({ product_id }) => {
  const [reviewData, setReviewData] = useState([]);
  const [error, setError] = useState(null);
  const [isLoading, setLoading] = useState(true);
  const { token } = useContext(AuthContext); // Get token and loading state from context

  useEffect(() => {
    const fetchReviews = async () => {
      if (!token) return; // Get token and loading state from context
      try {
        const response = await apiClient.get(AppURL.reviews(product_id));
        // console.log(response);
        setReviewData(response.data.reviews);
      } catch (error) {
        setError(
          ToastMessages.showError(
            error.response?.data?.message || "Failed to fetch reviews."
          )
        );
      } finally {
        setLoading(false);
      }
    };

    fetchReviews();
  }, [product_id , token]);

  const renderSkeletons = Array.from({ length: 2 }).map((_, index) => (
    <Col className="p-1" key={index} md={6} lg={6} sm={12} xs={12}>
      <p className="p-0 m-0">
        <Skeleton height={20} width={150} />
        <Skeleton height={15} width={100} />
        <Skeleton count={2} />
      </p>
    </Col>
  ));

  if (isLoading) {
    return (
      <>
        <div className="p-1 col-lg-6 col-md-6 col-sm-12 col-12" md={12}>
          {renderSkeletons}
        </div>
      </>
    );
  }

  if (error) {
    return (
      <div className="p-1 col-lg-6 col-md-6 col-sm-12 col-12" md={12}>
        <h4 className="text-danger">{error}</h4>
      </div>
    );
  }

  if (reviewData.length === 0) {
    return (
      <Container className="text-center">
        <div className="section-title text-center mb-55">
          <h2>CUSTOMER REVIEWS</h2>
          <p>See what our customers are saying</p>
        </div>
        <h4 className="text-muted">No reviews available.</h4>
      </Container>
    );
  }

  const MyView = reviewData.map((review, index) => (
    <Col className="p-1" key={index} md={6} lg={6} sm={12} xs={12}>
      <p className="p-0 m-0">
        <span className="Review-Title">{review.user.name}</span>{" "}
        <span className="text-success">
          {Array.from({ length: review.rating }).map((_, i) => (
            <i key={i} className="fa fa-star"></i>
          ))}
          {/* <i className="fa fa-star"></i> <i className="fa fa-star"></i> */}
        </span>
      </p>
      <p>{review.comment}</p>
      {/* Additional reviews go here */}
    </Col>
  ));
  return (
    <>
      <div className="p-1 col-lg-6 col-md-6 col-sm-12 col-12" md={12}>
        <h6 className="mt-2">REVIEWS</h6>
        {MyView}
      </div>
    </>
  );
};

export default Review;
