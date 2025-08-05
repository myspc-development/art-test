// Jest's ESM-friendly helpers are imported directly from `@jest/globals`.
// Relying on Jest's automatically injected globals in this ESM project left
// `test` undefined which triggered a runtime `TypeError: Cannot read properties
// of undefined (reading 'bind')`. Importing the functions explicitly ensures the
// helpers are available without relying on missing globals.
import { describe, it, expect } from '@jest/globals';
import { renderToString } from 'react-dom/server';
import { ActivityFeedWidget } from '../../assets/js/widgets/ActivityFeedWidget.jsx';

describe('ActivityFeedWidget', () => {
  it('renders ActivityFeedWidget placeholder', () => {
    const html = renderToString(<ActivityFeedWidget />);
    expect(html).toContain('ActivityFeedWidget Component Placeholder');
  });
});
