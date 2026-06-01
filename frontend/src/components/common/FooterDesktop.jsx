import React from "react";
import { Col, Container, Row, Spinner } from "react-bootstrap";
import { Link } from "react-router-dom";
import Apple from "../../assets/images/apple.png";
import Google from "../../assets/images/google.png";
import { useSiteSettings } from "../../hooks/useSiteSettings";
import { safeParse } from "../../utils/htmlParser";

const FooterDesktop = () => {
  const { settings, isLoading } = useSiteSettings();

  return (
    <>
      <div className="footerback m-0 mt-5 pt-3 shadow-sm">
        <Container>
          <Row className="px-0 my-5">
            <Col className="p-2" lg={3} md={3} sm={6} xs={12}>
              {isLoading ? (
                <Spinner
                  animation="border"
                  variant="primary"
                  role="status"
                  className="d-block mx-auto my-3"
                >
                  <span className="sr-only">Loading...</span>
                </Spinner>
              ) : (
                <>
                  <h5 className="footer-menu-title">OFFICE ADDRESS</h5>
                  {safeParse(settings.address)}
                  <h5 className="footer-menu-title">SOCIAL LINK</h5>
                  <a href={settings.facebookLink} target="_blank" rel="noreferrer">
                    <i className="fab m-1 h4 fa-facebook"></i>
                  </a>
                  <a href={settings.instagramLink} target="_blank" rel="noreferrer">
                    <i className="fab m-1 h4 fa-instagram"></i>
                  </a>
                  <a href={settings.twitterLink} target="_blank" rel="noreferrer">
                    <i className="fab m-1 h4 fa-twitter"></i>
                  </a>
                </>
              )}
            </Col>

            <Col className="p-2" lg={3} md={3} sm={6} xs={12}>
              <h5 className="footer-menu-title">THE COMPANY</h5>
              <Link to="/content/about" className="footer-link">
                About Us
              </Link>
              <br />
              <Link to="/" className="footer-link">
                Company Profile
              </Link>
              <br />
              <Link to="/contact" className="footer-link">
                Contact Us
              </Link>
              <br />
            </Col>

            <Col className="p-2" lg={3} md={3} sm={6} xs={12}>
              <h5 className="footer-menu-title">MORE INFO</h5>
              <Link to="/content/purchase_guide" className="footer-link">
                Purchase Guide
              </Link>
              <br />
              <Link to="/content/privacy" className="footer-link">
                Privacy Policy
              </Link>
              <br />
              <Link to="/content/refund" className="footer-link">
                Refund Policy
              </Link>
              <br />
            </Col>

            <Col className="p-2" lg={3} md={3} sm={6} xs={12}>
              <h5 className="footer-menu-title">DOWNLOAD APPS</h5>
              <a
                href={settings.androidAppLink}
                target="_blank"
                rel="noopener noreferrer"
              >
                <img src={Google} alt="Download on Google Play" />
              </a>
              <br />
              <a href={settings.iosAppLink} target="_blank" rel="noopener noreferrer">
                <img
                  className="mt-2"
                  src={Apple}
                  alt="Download on the App Store"
                />
              </a>
              <br />

              <div id="google_translate_element"></div>
            </Col>
          </Row>
        </Container>

        <Container fluid={true} className="text-center m-0 pt-3 pb-1 bg-dark">
          <Container>
            <Row>
              <h6 className="text-white">{safeParse(settings.copyrightText)}</h6>
            </Row>
          </Container>
        </Container>
      </div>
    </>
  );
};

export default FooterDesktop;
