import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App';

document.addEventListener('DOMContentLoaded', () => {
  const rootEl = document.getElementById('ap-widget-editor-root');
  if (rootEl) {
    const root = createRoot(rootEl);
    root.render(<App />);
  }
});
