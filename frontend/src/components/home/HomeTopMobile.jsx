import React from "react";
import Container from "react-bootstrap/Container";
import HomeSlider from "./HomeSlider";
import useSliders from "../../hooks/useSliders";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";

const HomeTopMobile = ({ sliders: propSliders }) => {
  const { data: hookSliders, loading: slidersLoading } = useSliders("active");
  const sliders = propSliders || hookSliders || [];

  if (!propSliders && slidersLoading && sliders.length === 0) {
    return (
      <Container className="py-3">
        <Skeleton height={280} className="rounded-4" />
      </Container>
    );
  }

  return (
    <Container className="py-3">
      <HomeSlider data={sliders} />
    </Container>
  );
};

export default React.memo(HomeTopMobile);
