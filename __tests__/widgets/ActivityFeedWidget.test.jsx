// Vitest is used instead of Jest because the previous Jest setup failed
// to provide a `test` function in the ESM environment which resulted in
// a runtime `TypeError: Cannot read properties of undefined (reading 'bind')`.
// Importing the functions directly from Vitest avoids relying on the missing
// global and resolves the error.
import { describe, it, expect } from 'vitest';
import { renderToString } from 'react-dom/server';
import { ActivityFeedWidget } from '../../assets/js/widgets/ActivityFeedWidget.jsx';

describe('ActivityFeedWidget', () => {
  it('renders ActivityFeedWidget placeholder', () => {
    const html = renderToString(<ActivityFeedWidget />);
    expect(html).toContain('ActivityFeedWidget Component Placeholder');
  });
});
