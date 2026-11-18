import React from "react";
import { Container } from "react-bootstrap";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";
import { Link } from "react-router-dom";
import useCategories from "../../hooks/useCategories";

const MegaMenuDesktop = ({ categories: propCategories }) => {
  const { data: hookCategories, loading, error } = useCategories('all');
  const categories = propCategories || hookCategories || [];

  const handleAccordionClick = (event) => {
    event.currentTarget.classList.toggle("active");
    const panel = event.currentTarget.nextElementSibling;
    if (panel.style.maxHeight) {
      panel.style.maxHeight = null;
    } else {
      panel.style.maxHeight = panel.scrollHeight + "px";
    }
  };

  if (!propCategories && loading && categories.length === 0) {
    return (
      <Container className="text-center">
        <Skeleton count={8} height={20} />
      </Container>
    );
  }

  return (
    <div className="accordionMenuDivAll">
      {error && categories.length === 0 ? (
        <div className="text-danger">{error}</div>
      ) : categories.length > 0 ? (
        categories.map((category, index) => (
          <div className="accordionMenuDivInsideAll" key={category.id || index}>
            <button className="accordionAll" onClick={handleAccordionClick}>
              <img
                className="accordionMenuIconAll"
                src={category.image || "https://img.icons8.com/?size=50&id=53386&format=png"}
                alt="icon"
                loading="lazy"
              />
              &nbsp; {category.name || category.category_name}
            </button>
            <div className="panelAll">
              <ul>
                {category.children && category.children.length > 0 ? (
                  category.children.map((sub, sIdx) => (
                    <li key={sub.id || sIdx}>
                      <Link to={`/${category.slug || category.name}/${sub.slug || sub.name}`} className="accordionItem">
                        {sub.name || sub.category_name}
                      </Link>
                    </li>
                  ))
                ) : (
                  <li>
                    <Link to={`/${category.slug || category.name}`} className="accordionItem">
                      {category.name || category.category_name}
                    </Link>
                  </li>
                )}
              </ul>
            </div>
          </div>
        ))
      ) : null}
    </div>
  );
};

export default React.memo(MegaMenuDesktop);
