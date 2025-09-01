test('initializes widget editor on DOMContentLoaded', () => {
  document.body.innerHTML = '<div id="artpulse-widget-editor-root"></div>';
  const root = document.getElementById('artpulse-widget-editor-root');

  const renderMock = jest.fn();
  const createRootMock = jest.fn(() => ({ render: renderMock }));

  const originalReact = global.React;
  const originalReactDOM = global.ReactDOM;

  global.React = { createElement: jest.fn() };
  global.ReactDOM = { createRoot: createRootMock };

  require('../js/widget-editor');
  document.dispatchEvent(new Event('DOMContentLoaded'));

  expect(createRootMock).toHaveBeenCalledWith(root);
  expect(renderMock).toHaveBeenCalled();

  global.React = originalReact;
  global.ReactDOM = originalReactDOM;
});

