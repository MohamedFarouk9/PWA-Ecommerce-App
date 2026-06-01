import React, { useEffect, useState } from "react";
import Container from "react-bootstrap/Container";
import Row from "react-bootstrap/Row";
import Col from "react-bootstrap/Col";
import MegaMenu from "./MegaMenu";
import HomeSlider from "./HomeSlider";
import apiClient from "../../services/apiClient";
import AppURL from "../../utils/AppURL";
import ToastMessages from "../../toast-messages/toast";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";

function HomeTop() {
  const [menuData, setMenuData] = useState([]);
  const [sliderData, setSliderData] = useState([]);
  const [menuLoading, setMenuLoading] = useState(true);
  const [sliderLoading, setSliderLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    // Fetch category data when the component mounts
    const fetchData = async () => {
      try {
        const [menuResponse, sliderResponse] = await Promise.allSettled([
          apiClient.get(AppURL.CategoryDetails),
          apiClient.get(AppURL.Sliders),
        ]);

        // Handle menu response
        if (menuResponse.status === "fulfilled") {
          setMenuData(menuResponse.value.data.data.categories);
        } else {
          setError(ToastMessages.showError("Failed to load categories"));
        }
        setMenuLoading(false);
        // Handle slider response
        if (sliderResponse.status === "fulfilled") {
          console.log("Slider API response:", sliderResponse.value.data); // DEBUG
          let sliders = [];
          const responseData = sliderResponse.value.data.data;
          
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
        } else {
          console.error("Failed to load sliders:", sliderResponse.reason);
          setSliderData([]);
          ToastMessages.showError("Failed to load sliders");
        }
        setSliderLoading(false);
      } catch (error) {
        console.error("Unexpected error:", error);
      } finally {
        setSliderLoading(false);
        setMenuLoading(false);
      }
      //Finally Block for Loading State: The finally block ensures that isLoading is set to false regardless of the request's success or failure.
    };

    fetchData();
  }, []); // Empty dependency array to run this effect only once on mount

  if (error) {
    return <p>Error: {error}</p>;
  }

  // if (isloading) {
  //   return  <Skeleton count={5} height={40} />;
  // }

  return (
    <Container className="p-0 m-0 overflow-hidden" fluid={true}>
      <Row>
        <Col lg={3} md={3} sm={12}>
          {menuLoading ? (
            <div>
              <Skeleton count={12} height={20} />
            </div>
          ) : (
            <MegaMenu data={menuData} />
          )}
        </Col>
        <Col lg={9} md={9} sm={12}>
          {sliderLoading ? (
            <div>
              <Skeleton height={300} />
            </div>
          ) : (
            <HomeSlider data={sliderData} />
          )}
        </Col>
      </Row>
    </Container>
  );
}

export default HomeTop;
