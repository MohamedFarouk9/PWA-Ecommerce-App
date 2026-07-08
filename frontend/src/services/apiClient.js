import axios from 'axios';

const BASE_URL = 'http://127.0.0.1:8000/api';

const getGuestSessionId = () => {
  let sessionId = localStorage.getItem('guest_session_id');

  if (!sessionId) {
    sessionId = `${Date.now()}-${Math.random().toString(36).slice(2, 10)}`;
    localStorage.setItem('guest_session_id', sessionId);
  }

  return sessionId;
};

const apiClient = axios.create({
  baseURL: BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Add request interceptor for auth token
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    if (!config.headers.Authorization) {
      config.headers['X-Session-ID'] = getGuestSessionId();
    }

    return config;
  },
  (error) => Promise.reject(error)
);

// Add response interceptor for error handling
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    // Don't auto-redirect here - let the app handle 401 errors
    // Just reject the error and let components handle it
    return Promise.reject(error);
  }
);

export default apiClient;
