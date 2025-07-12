import React from 'react';
import { createRoot } from 'react-dom/client';
import ReactForm from './components/ReactForm';

const container = document.getElementById('react-form-root');
if (container) {
  const root = createRoot(container);
  root.render(<ReactForm />);
}
