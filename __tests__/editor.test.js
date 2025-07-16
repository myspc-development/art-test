test('renders widget', () => {
  const { renderWidget } = require('../js/widget-editor');
  const el = renderWidget({ id: 1 });
  expect(el.textContent).toContain('Widget 1');
});
