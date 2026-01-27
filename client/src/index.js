import React from 'react';
import ReactDOM from 'react-dom/client'; // For React 18+
import App from './App';
import './styles/main.css'; // Import the global stylesheet

/**
 * This is the entry point of the React application.
 * It renders the main App component into the DOM.
 *
 * Using ReactDOM.createRoot for React 18 concurrent mode features.
 * React.StrictMode helps identify potential problems in an application during development.
 * It activates additional checks and warnings for its descendants.
 */
const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);