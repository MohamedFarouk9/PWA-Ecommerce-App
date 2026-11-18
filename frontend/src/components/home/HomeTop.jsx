import React from "react";
import Container from "react-bootstrap/Container";
import Row from "react-bootstrap/Row";
import Col from "react-bootstrap/Col";
import MegaMenu from "./MegaMenu";
import HomeSlider from "./HomeSlider";
import useCategories from "../../hooks/useCategories";
import useSliders from "../../hooks/useSliders";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";

function HomeTop({ categories: propCategories, sliders: propSliders }) {
  const { data: hookCategories, loading: categoriesLoading } = useCategories('all');
  const { data: hookSliders, loading: slidersLoading } = useSliders('active');

  const categories = propCategories || hookCategories || [];
  const sliders = propSliders || hookSliders || [];
  const isLoading = (!propCategories && categoriesLoading) || (!propSliders && slidersLoading);

  return (
    <Container className="p-0 pt-2" fluid={true}>
      <Row className="p-0 m-0">
        <Col lg={3} md={3} sm={12} className="p-0">
          {isLoading && categories.length === 0 ? (
            <Skeleton count={8} height={35} className="mb-2" />
          ) : (
            <MegaMenu data={categories} />
          )}
        </Col>
        <Col lg={9} md={9} sm={12} className="p-0">
          {isLoading && sliders.length === 0 ? (
            <Skeleton height={350} className="w-100" />
          ) : (
            <HomeSlider data={sliders} />
          )}
        </Col>
      </Row>
    </Container>
  );
}

export default React.memo(HomeTop);
