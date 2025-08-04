import { renderToString } from 'react-dom/server';
import { WelcomeBoxWidget } from '../../assets/js/widgets/WelcomeBoxWidget.jsx';

test('renders WelcomeBoxWidget placeholder', () => {
  const html = renderToString(<WelcomeBoxWidget />);
  expect(html).toContain('WelcomeBoxWidget Component Placeholder');
});
