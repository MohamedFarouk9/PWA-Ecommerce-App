import React from "react";
import Slider from "react-slick";
import { Link } from "react-router-dom";
import "slick-carousel/slick/slick.css";
import "slick-carousel/slick/slick-theme.css";

const HERO_PROMOS = [
  {
    tag: "New Season Arrival",
    title: "Next-Gen Tech & Flagship Electronics",
    subtitle: "Discover MacBook Pro M3, Galaxy S24 Ultra, and noise-canceling audio with up to 30% off.",
    ctaText: "Shop Electronics",
    ctaLink: "/electronics",
    image: "https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?auto=format&fit=crop&w=1600&q=80",
  },
  {
    tag: "Limited Time Deal",
    title: "Elevate Your Everyday Wardrobe",
    subtitle: "Curated footwear, performance sportswear, and premium essentials delivered to your door.",
    ctaText: "Explore Fashion",
    ctaLink: "/clothing",
    image: "https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=1600&q=80",
  },
  {
    tag: "Smart Living",
    title: "Modern Home & Workspace Upgrades",
    subtitle: "Transform your space with smart appliances, ergonomic furniture, and designer lighting.",
    ctaText: "Browse Home",
    ctaLink: "/home-kitchen",
    image: "https://images.unsplash.com/photo-1555529669-e69e7aa0ba9a?auto=format&fit=crop&w=1600&q=80",
  },
];

const HomeSlider = ({ data = [] }) => {
  const sliderData = Array.isArray(data) && data.length > 0 ? data : HERO_PROMOS;

  const settings = {
    dots: true,
    infinite: true,
    speed: 600,
    autoplay: true,
    autoplaySpeed: 4500,
    slidesToShow: 1,
    slidesToScroll: 1,
    arrows: false,
  };

  return (
    <div className="ecom-slider-wrapper">
      <Slider {...settings}>
        {sliderData.map((slider, index) => {
          const promo = HERO_PROMOS[index % HERO_PROMOS.length];
          const imgSrc = slider.image || promo.image;

          return (
            <div key={slider.id || index} className="ecom-slide-item">
              <img
                className="ecom-slide-img"
                src={imgSrc}
                alt={promo.title}
                onError={(e) => {
                  e.target.onerror = null;
                  e.target.src = promo.image;
                }}
              />
              <div className="ecom-slide-overlay">
                <span className="ecom-slide-tag">
                  <i className="fa fa-fire"></i> {promo.tag}
                </span>
                <h1 className="ecom-slide-title">{promo.title}</h1>
                <p className="ecom-slide-subtitle">{promo.subtitle}</p>
                <Link to={promo.ctaLink} className="ecom-slide-cta">
                  <span>{promo.ctaText}</span>
                  <i className="fa fa-arrow-right"></i>
                </Link>
              </div>
            </div>
          );
        })}
      </Slider>
    </div>
  );
};

export default HomeSlider;
