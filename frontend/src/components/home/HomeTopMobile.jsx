import React, { useEffect, useState } from "react";
import Container from "react-bootstrap/Container";
import Row from "react-bootstrap/Row";
import Col from "react-bootstrap/Col";
import HomeSlider from "./HomeSlider";
import apiClient from "../../services/apiClient";
import AppURL from "../../utils/AppURL";
import ToastMessages from "../../toast-messages/toast";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";

const HomeTopMobile = () => {
  const [sliderData, setSliderData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchSliderData = async () => {
      try {
        const response = await apiClient.get(AppURL.Sliders);
        console.log("Slider API response:", response.data); // DEBUG
        
        let sliders = [];
        const responseData = response.data.data;
        
        // Handle paginated response (has .data property for actual items)
        if (responseData?.sliders?.data) {
          sliders = responseData.sliders.data;
        }
        // Handle non-paginated response (direct array)
        else if (responseData?.sliders && Array.isArray(responseData.sliders)) {
          sliders = responseData.sliders;
        }
        // Handle direct array response
        else if (Array.isArray(responseData?.sliders)) {
          sliders = responseData.sliders;
        }
        
        console.log("Extracted sliders:", sliders); // DEBUG
        setSliderData(Array.isArray(sliders) ? sliders : []);
      } catch (error) {
        console.error("Error fetching sliders:", error);
        setError(
          ToastMessages.showError(
            "Failed to load information. Please try again later."
          )
        );
        setSliderData([]); // Fallback to empty array
      } finally {
        setLoading(false); // Ensure loading stops in both success and error cases
      }
    };

    fetchSliderData();
  }, []);

  if (error) {
    return <p>Error: {error}</p>;
  }

  if (loading) {
    return <Skeleton count={5} height={40} />;
  }

  return (
    <>
      <Container className="p-0 m-0 overflow-hidden" fluid={true}>
        <Row className="p-0 m-0 overflow-hidden">
          <Col lg={12} md={12} sm={12}>
            <HomeSlider data={sliderData} />
          </Col>
        </Row>
      </Container>
    </>
  );
};

export default HomeTopMobile;
