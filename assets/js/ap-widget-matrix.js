import React from 'react';
import { createRoot } from 'react-dom/client';
import AdminWidgetMatrix from './components/AdminWidgetMatrix.tsx';

document.addEventListener('DOMContentLoaded', () => {
  const rootEl = document.getElementById('ap-widget-matrix-root');
  if (rootEl) {
    const root = createRoot(rootEl);
    root.render(<AdminWidgetMatrix />);
  }
});

