import axios from "axios";
import React, { useState, useEffect } from "react";
import { Container, Row, Col, Card, Button, Modal, Badge } from "react-bootstrap";
import AppURL from "../../utils/AppURL";
import ToastMessages from "../../toast-messages/toast";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";
import { ResponsiveLayout } from "../../layouts/ResponsiveLayout";

const Notification = () => {
  const [show, setShow] = useState(false);
  const [notifications, setNotifications] = useState([]);
  const [selectedNotification, setSelectedNotification] = useState(null);
  const [loading, setLoading] = useState(true);

  const handleShow = (item) => {
    setSelectedNotification(item);
    setShow(true);
  };

  const handleClose = () => {
    setSelectedNotification(null);
    setShow(false);
  };

  useEffect(() => {
    const fetchNotifications = async () => {
      try {
        const response = await axios.get(AppURL.Notifications);
        if (response.status === 200) {
          const list =
            response.data?.notifications ||
            response.data?.data?.notifications ||
            response.data?.data ||
            [];
          setNotifications(Array.isArray(list) ? list : []);
        }
      } catch (e) {
        ToastMessages.showError("Failed to load notifications.");
      } finally {
        setLoading(false);
      }
    };

    fetchNotifications();
  }, []);

  const renderSkeletons = Array.from({ length: 4 }).map((_, index) => (
    <Col key={index} md={6} sm={12}>
      <Card className="p-2">
        <Card.Body>
          <Skeleton width="60%" height={20} className="mb-2" />
          <Skeleton width="40%" height={14} />
        </Card.Body>
      </Card>
    </Col>
  ));

  return (
    <>
      <ResponsiveLayout>
        <Container className="py-4">
          <div className="ecom-section-header">
            <div>
              <h2 className="ecom-section-title">Notifications & Alerts</h2>
              <p className="ecom-section-subtitle">
                Stay updated on flash sales, order updates, and announcements
              </p>
            </div>
          </div>

          <Row className="g-3">
            {loading ? (
              renderSkeletons
            ) : notifications.length === 0 ? (
              <Col xs={12}>
                <Card className="text-center py-5">
                  <Card.Body>
                    <i className="fa fa-bell-slash fa-3x text-muted mb-3"></i>
                    <h5 className="fw-bold">You're all caught up!</h5>
                    <p className="text-muted mb-0">
                      No new notifications at the moment.
                    </p>
                  </Card.Body>
                </Card>
              </Col>
            ) : (
              notifications.map((item) => (
                <Col key={item.id} md={6} sm={12}>
                  <Card
                    onClick={() => handleShow(item)}
                    className="notification-card h-100"
                  >
                    <Card.Body className="d-flex flex-column justify-content-between">
                      <div>
                        <div className="d-flex justify-content-between align-items-start mb-2">
                          <h6 className="fw-bold mb-0">{item.title}</h6>
                          <Badge
                            bg={item.is_read ? "secondary" : "primary"}
                            pill
                          >
                            {item.is_read ? "Read" : "New"}
                          </Badge>
                        </div>
                        <p className="text-muted small mb-3">
                          {item.message
                            ? item.message.slice(0, 110) +
                              (item.message.length > 110 ? "..." : "")
                            : "Click to view full details."}
                        </p>
                      </div>

                      <div className="d-flex justify-content-between align-items-center pt-2 border-top small text-muted">
                        <span>
                          <i className="fa fa-calendar-alt me-1 text-primary"></i>
                          {item.created_at
                            ? new Date(item.created_at).toLocaleDateString()
                            : "Recent"}
                        </span>
                        <span className="text-primary fw-semibold">
                          Read More <i className="fa fa-chevron-right ms-1"></i>
                        </span>
                      </div>
                    </Card.Body>
                  </Card>
                </Col>
              ))
            )}
          </Row>
        </Container>
      </ResponsiveLayout>

      <Modal show={show} onHide={handleClose} centered>
        <Modal.Header closeButton>
          <Modal.Title className="fs-6 fw-bold">
            <i className="fa fa-bell text-primary me-2"></i>
            {selectedNotification?.title}
          </Modal.Title>
        </Modal.Header>
        <Modal.Body>
          <div className="small text-muted mb-2">
            {selectedNotification?.created_at &&
              new Date(selectedNotification.created_at).toLocaleString()}
          </div>
          <p className="mb-0" style={{ lineHeight: 1.6 }}>
            {selectedNotification?.message}
          </p>
        </Modal.Body>
        <Modal.Footer>
          <Button variant="secondary" size="sm" onClick={handleClose}>
            Close
          </Button>
        </Modal.Footer>
      </Modal>
    </>
  );
};

export default Notification;
