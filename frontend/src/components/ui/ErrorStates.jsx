import React from 'react';
import { Container, Alert, Button } from 'react-bootstrap';

/**
 * Generic error display component
 * @param {string} message - Error message to display
 * @param {function} onRetry - Optional callback to retry action
 * @param {string} details - Optional detailed error info for debugging
 * @returns {JSX.Element}
 */
export const ErrorAlert = ({ message, onRetry, details }) => (
  <Container className="text-center my-5">
    <Alert variant="danger" className="mx-auto" style={{ maxWidth: '500px' }}>
      <Alert.Heading>Oops! Something went wrong</Alert.Heading>
      <p>{message || 'Failed to load content. Please try again later.'}</p>
      {details && (
        <small className="text-muted d-block mb-3">
          <code>{details}</code>
        </small>
      )}
      {onRetry && (
        <Button variant="primary" size="sm" onClick={onRetry}>
          Try Again
        </Button>
      )}
    </Alert>
  </Container>
);

/**
 * Empty state component
 * @param {string} message - Message to display
 * @param {string} title - Title for empty state
 * @returns {JSX.Element}
 */
export const EmptyState = ({
  message = 'No items found',
  title = 'Empty',
  children,
}) => (
  <Container className="text-center my-5">
    <h4>{title}</h4>
    <p className="text-muted">{message}</p>
    {children}
  </Container>
);
