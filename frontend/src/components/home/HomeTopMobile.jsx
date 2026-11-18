import React from "react";
import Container from "react-bootstrap/Container";
import Row from "react-bootstrap/Row";
import Col from "react-bootstrap/Col";
import HomeSlider from "./HomeSlider";
import useSliders from "../../hooks/useSliders";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";

const HomeTopMobile = ({ sliders: propSliders }) => {
  const { data: hookSliders, loading: slidersLoading } = useSliders('active');
  const sliders = propSliders || hookSliders || [];

  if (!propSliders && slidersLoading && sliders.length === 0) {
    return <Skeleton count={4} height={50} className="mb-2" />;
  }

  return (
    <Container className="p-0 m-0 overflow-hidden" fluid={true}>
      <Row className="p-0 m-0 overflow-hidden">
        <Col lg={12} md={12} sm={12}>
          <HomeSlider data={sliders} />
        </Col>
      </Row>
    </Container>
  );
};

export default React.memo(HomeTopMobile);
