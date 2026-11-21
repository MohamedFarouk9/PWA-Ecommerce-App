import React, { useState } from "react";
import { Link } from "react-router-dom";

const MegaMenu = ({ data = [] }) => {
  const [openIndex, setOpenIndex] = useState(null);

  const toggleIndex = (idx) => {
    setOpenIndex((prev) => (prev === idx ? null : idx));
  };

  return (
    <div className="ecom-megamenu-card">
      <div className="ecom-megamenu-header">
        <i className="fa fa-th-large"></i>
        <span>Browse Categories</span>
      </div>
      <div className="ecom-megamenu-list">
        {data.map((item, index) => {
          const hasChildren = Array.isArray(item.children) && item.children.length > 0;
          const isOpen = openIndex === index;

          return (
            <div key={item.id || index} className="ecom-megamenu-item">
              <button
                type="button"
                className={`ecom-megamenu-btn ${isOpen ? "active" : ""}`}
                onClick={() => toggleIndex(index)}
              >
                <span className="d-flex align-items-center gap-2">
                  <i className="fa fa-tag text-primary" style={{ fontSize: "12px" }}></i>
                  <span>{item.name}</span>
                </span>
                {hasChildren && (
                  <i
                    className={`fa fa-chevron-${isOpen ? "down" : "right"}`}
                    style={{ fontSize: "11px", opacity: 0.65 }}
                  ></i>
                )}
              </button>

              {hasChildren && isOpen && (
                <ul className="ecom-megamenu-sub">
                  <li>
                    <Link to={`/${item.slug}`} className="fw-semibold text-primary">
                      All {item.name}
                    </Link>
                  </li>
                  {item.children.map((subcategory, subIndex) => (
                    <li key={subcategory.id || subIndex}>
                      <Link to={`/${item.slug}/${subcategory.slug}`}>
                        {subcategory.name}
                      </Link>
                    </li>
                  ))}
                </ul>
              )}
            </div>
          );
        })}
      </div>
    </div>
  );
};

export default MegaMenu;
