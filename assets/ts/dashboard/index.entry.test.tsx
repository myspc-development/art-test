import { createRoot } from 'react-dom/client';

jest.mock('react-dom/client', () => ({
  createRoot: jest.fn(),
}));
jest.mock('./RoleDashboard', () => () => null);

describe('dashboard entry bootstrap', () => {
  it('renders on custom bootstrap event', async () => {
    const render = jest.fn();
    (createRoot as jest.Mock).mockReturnValue({ render });
    document.body.innerHTML = '<div id="ap-dashboard-root"></div>';
    await import('./index');
    document.dispatchEvent(new Event('ap-dashboard-bootstrap'));
    expect(createRoot).toHaveBeenCalledTimes(1);
    expect(render).toHaveBeenCalledTimes(1);
  });
});
