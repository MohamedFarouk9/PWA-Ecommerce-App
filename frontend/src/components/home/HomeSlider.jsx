import React from "react";
import Slider from "react-slick";
import "slick-carousel/slick/slick.css";
import "slick-carousel/slick/slick-theme.css";

const HomeSlider = ({ data = [] }) => {
  // Ensure data is always an array
  const sliderData = Array.isArray(data) ? data : [];

  var settings = {
    dots: true,
    infinite: true,
    speed: 500,
    autoplay: true,
    autoplaySpeed: 3000,
    slidesToShow: 1,
    slidesToScroll: 1,
    initialSlide: 0,
    arrows: false,
    responsive: [
      {
        breakpoint: 1024,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 3,
          infinite: true,
          dots: true,
        },
      },
      {
        breakpoint: 600,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1,
          initialSlide: 2,
        },
      },
      {
        breakpoint: 480,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1,
        },
      },
    ],
  };

  const renderSlides = () => {
    if (!sliderData || sliderData.length === 0) {
      return (
        <div>
          <img
            className="slider-img"
            src="/images/default-slider.jpg"
            alt="default-slider"
          />
        </div>
      );
    }

    return sliderData.map((slider, index) => (
      <div key={slider.id || index}>
        <img
          className="slider-img"
          src={slider.image}
          alt={`slider-${index + 1}`}
          onError={(e) => {
            e.target.onerror = null;
            e.target.src = "/images/default-slider.jpg";
          }}
        />
      </div>
    ));
  };

  return (
    <div className="slider-container">
      <Slider {...settings}>{renderSlides()}</Slider>
    </div>
  );
};

export default HomeSlider;
