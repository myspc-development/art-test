// See ActivityFeedWidget.test.jsx for details on why the tests now import
// the Vitest APIs directly. Using Jest in this ESM project left the `test`
// global undefined which triggered a `.bind` call on `undefined` within the
// Jest runtime. Vitest provides the necessary globals reliably.
import { describe, it, expect } from 'vitest';
import { renderToString } from 'react-dom/server';
import { WelcomeBoxWidget } from '../../assets/js/widgets/WelcomeBoxWidget.jsx';

describe('WelcomeBoxWidget', () => {
  it('renders WelcomeBoxWidget placeholder', () => {
    const html = renderToString(<WelcomeBoxWidget />);
    expect(html).toContain('WelcomeBoxWidget Component Placeholder');
  });
});
