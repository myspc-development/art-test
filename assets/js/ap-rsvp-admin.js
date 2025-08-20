import { __ } from './ap-core.js';

export default async function render(container) {
  const p = document.createElement('p');
  p.textContent = __('RSVP admin coming soon');
  container.appendChild(p);
}
