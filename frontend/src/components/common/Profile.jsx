import React from 'react';
import { Container, Row, Col, Card, ListGroup, Button } from 'react-bootstrap';
import { useNavigate } from 'react-router-dom';

const Profile = ({userData , logout }) => {

    const navigate = useNavigate();

    const handleLogout = () => {
        logout();
        navigate('/');
    };


    return (
        <Container className="mt-4">
            <Row className="justify-content-center">
                <Col md={6}>
                    <Card className="shadow-sm">
                        <Card.Body>
                            <Card.Title className="text-center">User Profile</Card.Title>
                            <ListGroup variant="flush">
                                <ListGroup.Item><strong>Name:</strong> {userData.user.name}</ListGroup.Item>
                                <ListGroup.Item><strong>Email:</strong> {userData.user.email}</ListGroup.Item>
                                <ListGroup.Item><strong>Phone:</strong> {userData.user.phone}</ListGroup.Item>
                                <ListGroup.Item><strong>Address:</strong> {userData.user.address}</ListGroup.Item>
                                <ListGroup.Item><strong>Bio:</strong> {userData.user.bio}</ListGroup.Item>
                            </ListGroup>
                            <div className="text-center mt-3">
                                <Button variant="outline-primary" className="m-1" onClick={() => navigate('/orders')}>
                                    <i className="fa fa-shopping-bag me-1"></i> My Orders
                                </Button>
                                <Button variant="primary" className="m-1">Edit Profile</Button>
                                <Button variant="danger" className="m-1" onClick={handleLogout}>Logout</Button>
                            </div>
                        </Card.Body>
                    </Card>
                </Col>
            </Row>
        </Container>
    );
};

export default Profile;
