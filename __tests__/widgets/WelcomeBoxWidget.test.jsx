// See ActivityFeedWidget.test.jsx for details on why the tests now import
// Jest's testing helpers from `@jest/globals`. In this ESM project, relying
// on automatically injected Jest globals left the `test` function undefined,
// which triggered a `.bind` call on `undefined` within the runtime. Importing
// the helpers explicitly ensures they are available.
import { describe, it, expect } from '@jest/globals';
import { renderToString } from 'react-dom/server';
import { WelcomeBoxWidget } from '../../assets/js/widgets/WelcomeBoxWidget.jsx';

describe('WelcomeBoxWidget', () => {
  it('renders WelcomeBoxWidget placeholder', () => {
    const html = renderToString(<WelcomeBoxWidget />);
    expect(html).toContain('WelcomeBoxWidget Component Placeholder');
  });
});
