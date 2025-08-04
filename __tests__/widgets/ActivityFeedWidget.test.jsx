import { renderToString } from 'react-dom/server';
import { ActivityFeedWidget } from '../../assets/js/widgets/ActivityFeedWidget.jsx';

test('renders ActivityFeedWidget placeholder', () => {
  const html = renderToString(<ActivityFeedWidget />);
  expect(html).toContain('ActivityFeedWidget Component Placeholder');
});
