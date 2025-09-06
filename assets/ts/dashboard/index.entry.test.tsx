jest.mock('react-dom/client', () => ({
  createRoot: jest.fn(),
}));
jest.mock('./RoleDashboard', () => () => null);

describe('dashboard entry bootstrap', () => {
  beforeEach(() => {
    jest.resetModules();
    document.body.innerHTML = '';
  });

  it('renders on custom bootstrap event', async () => {
    const { createRoot } = await import('react-dom/client');
    const render = jest.fn();
    (createRoot as jest.Mock).mockReturnValue({ render });
    document.body.innerHTML = '<div id="ap-dashboard-root"></div>';
    await import('./index');
    document.dispatchEvent(new Event('ap-dashboard-bootstrap'));
    expect(createRoot).toHaveBeenCalledTimes(1);
    expect(render).toHaveBeenCalledTimes(1);
  });

  it('renders on DOMContentLoaded and uses data-role attribute', async () => {
    const { createRoot } = await import('react-dom/client');
    const render = jest.fn();
    (createRoot as jest.Mock).mockReturnValue({ render });
    document.body.innerHTML = '<div id="ap-dashboard-root" data-role="artist"></div>';
    await import('./index');
    document.dispatchEvent(new Event('DOMContentLoaded'));
    expect(createRoot).toHaveBeenCalledTimes(1);
    expect(render).toHaveBeenCalledTimes(1);
  });

  it('does nothing if root element missing', async () => {
    const { createRoot } = await import('react-dom/client');
    (createRoot as jest.Mock).mockReturnValue({ render: jest.fn() });
    await import('./index');
    document.dispatchEvent(new Event('DOMContentLoaded'));
    expect(createRoot).not.toHaveBeenCalled();
  });
});
