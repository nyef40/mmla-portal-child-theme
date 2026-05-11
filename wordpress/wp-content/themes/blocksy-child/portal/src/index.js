import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App';
import './styles/portal.css';

const container = document.getElementById('portal-root');
if (container) {
  const root = createRoot(container);
  root.render(<App />);
}
