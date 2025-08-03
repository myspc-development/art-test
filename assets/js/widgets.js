import React from 'react';
import { createRoot } from 'react-dom/client';

function toComponentName(id) {
  return id
    .split('_')
    .map(part => part.charAt(0).toUpperCase() + part.slice(1))
    .join('') + 'Widget';
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-widget]').forEach(async (el) => {
    const id = el.getAttribute('data-widget');
    const props = JSON.parse(el.getAttribute('data-props') || '{}');
    const componentName = toComponentName(id);
    try {
      const module = await import(`./widgets/${componentName}.jsx`);
      const Component = module.default || module[componentName];
      if (!Component) {
        console.warn(`Component ${componentName} missing for widget ${id}`);
        return;
      }
      const root = createRoot(el);
      root.render(<Component {...props} />);
    } catch (e) {
      console.warn(`No component found for widget "${id}"`);
    }
  });
});
