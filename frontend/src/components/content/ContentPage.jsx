import React, { useEffect } from 'react';
import { useSiteContent } from '../../hooks/useSiteContent';
import { Container, Row, Col, Card } from 'react-bootstrap';
import ContentSkeleton from './ContentSkeleton';
import './ContentSkeleton.css';

/**
 * Reusable content page component
 * @param {Object} props
 * @param {string} props.type - Content type (about, privacy, refund, purchase_guide)
 * @param {string} props.title - Page title
 * @param {React.Component} props.extraContent - Optional extra content to display
 */
const ContentPage = ({ type, title, extraContent }) => {
    const { data: content, isLoading: loading, error } = useSiteContent(type);

    // Scroll to top when content loads
    useEffect(() => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }, [type]); // Re-run when type changes (new page)

    if (loading) {
        return <ContentSkeleton />;
    }

    if (error) {
        return (
            <Container className="py-5">
                <Row className="justify-content-center">
                    <Col md={8}>
                        <Card className="border-0 shadow-sm">
                            <Card.Body className="text-center text-danger">
                                {error.message}
                            </Card.Body>
                        </Card>
                    </Col>
                </Row>
            </Container>
        );
    }

    if (!content) {
        return (
            <Container className="py-5">
                <Row className="justify-content-center">
                    <Col md={8}>
                        <Card className="border-0 shadow-sm">
                            <Card.Body className="text-center">
                                No content available
                            </Card.Body>
                        </Card>
                    </Col>
                </Row>
            </Container>
        );
    }

    return (
        <Container className="py-4">
            <div className="ecom-section-header">
                <div>
                    <h2 className="ecom-section-title">{title}</h2>
                    <p className="ecom-section-subtitle">Official store policy and customer guidelines</p>
                </div>
            </div>
            <Row className="justify-content-center">
                <Col md={12}>
                    <Card className="p-3">
                        <Card.Body>
                            <div 
                                className="content-body"
                                style={{ lineHeight: 1.75 }}
                                dangerouslySetInnerHTML={{ __html: content }} 
                            />
                            {extraContent}
                        </Card.Body>
                    </Card>
                </Col>
            </Row>
        </Container>
    );
};

export default ContentPage; 