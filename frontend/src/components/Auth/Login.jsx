import React, { useContext, useState } from "react";
import Container from "react-bootstrap/Container";
import Row from "react-bootstrap/Row";
import Col from "react-bootstrap/Col";
import Button from "react-bootstrap/Button";
import { Form } from "react-bootstrap";
import Login from "../../assets/images/login.png";
import axios from "axios";
import AppURL from "../../utils/AppURL";
import ToastMessages from "../../toast-messages/toast";
import { useNavigate } from "react-router-dom";
import { AuthContext } from "../../utils/AuthContext";
import { ResponsiveLayout } from "../../layouts/ResponsiveLayout";

const UserLogin = () => {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const {login} = useContext(AuthContext);
  const [message, setMessage] = useState("");
  const navigate = useNavigate();

  const formSubmit = async (e) => {
    e.preventDefault();
    setMessage("");

    try {
      const response = await axios.post(AppURL.UserLogin, { email, password });
      console.log(response);
      if (response.status === 200) {
        login(response.data.token)         
        navigate("/profile");

        setMessage(ToastMessages.showSuccess("Login successful !"));
      }
    } catch (error) {
        setMessage(ToastMessages.showWarning("Login failed. Please check your credentials."));
    }
  };

  return (
    <>
    <ResponsiveLayout>  
      <Container>
        <Row className="p-2">
          <Col
            className="shadow-sm bg-white mt-2"
            md={12}
            lg={12}
            sm={12}
            xs={12}
          >
            <Row className="text-center">
              <Col
                className="d-flex justify-content-center"
                md={6}
                lg={6}
                sm={12}
                xs={12}
              >
                <Form className="onboardForm" onSubmit={formSubmit}>
                  <h4 className="section-title-login"> Sign In</h4>
                  {/* <h6 className="section-sub-title">Please Enter Your Mobile Number</h6> */}
                  {/* <input className="form-control m-2" type="text" placeholder="Enter Mobile Number" /> */}
                  <input
                    className="form-control m-2"
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder="Enter Your Email"
                  />
                  <input
                    className="form-control m-2"
                    type="password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    placeholder="Enter Your Password"
                  />

                  <Button
                    type="submit"
                    className="btn btn-block m-2 site-btn-login"
                  >
                    Login
                  </Button>

                  {message && (
                    <p className="text-center text-danger">{message}</p>
                  )}
                </Form>
              </Col>
              <Col className="p-0 m-0 Desktop" md={6} lg={6} sm={12} xs={12}>
                <img className="onboardBanner" src={Login} alt="no_image" />
              </Col>
            </Row>
          </Col>
        </Row>
      </Container>
    </ResponsiveLayout>
    </>
  );
};

export default UserLogin;
