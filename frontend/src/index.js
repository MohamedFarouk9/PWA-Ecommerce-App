import React from 'react';
import ReactDOM from 'react-dom/client';
import 'bootstrap/dist/css/bootstrap.min.css';
import '../src/assets/css/fontawesome.css'
import '../src/assets/css/custom.css'
import App from './App';
import reportWebVitals from './reportWebVitals';
import { AuthProvider } from './utils/AuthContext';
import { CartProvider } from "./utils/CartContext";  // Import CartProvider

import * as serviceWorkerRegistration from './serviceWorkerRegistration';

const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(
  <React.StrictMode>
    <AuthProvider>
      <CartProvider>
        <App />
      </CartProvider>
    </AuthProvider>
  </React.StrictMode>
);

// Register service worker for Progressive Web App (PWA) offline capability
serviceWorkerRegistration.register();


// If you want to start measuring performance in your app, pass a function
// to log results (for example: reportWebVitals(console.log))
// or send to an analytics endpoint. Learn more: https://bit.ly/CRA-vitals
reportWebVitals();
