import { renderHook, waitFor, act } from '@testing-library/react';
import useFilteredWidgets from '../useFilteredWidgets';

describe('useFilteredWidgets', () => {
  beforeEach(() => {
    (window as any).wpApiSettings = { root: '/api/', nonce: 'n' };
    window.localStorage.clear();
  });

  it('handles fetch errors then succeeds on retry', async () => {
    const widgets = [{ id: 'local' }];
    const fetchMock = jest
      .fn()
      .mockResolvedValueOnce({ ok: false, status: 500 })
      .mockResolvedValueOnce({
        ok: true,
        json: () =>
          Promise.resolve({ widget_roles: { remote: ['member'] }, capabilities: {}, excluded_roles: {} }),
      });
    global.fetch = fetchMock as any;

    const { result } = renderHook(() =>
      useFilteredWidgets(widgets, { role: 'member', capabilities: [] })
    );

    await waitFor(() => expect(result.current.loading).toBe(false));
    expect(result.current.error).toBeTruthy();

    await act(async () => {
      await result.current.retry();
    });

    await waitFor(() => expect(result.current.error).toBeNull());
    expect(result.current.widgets).toEqual([
      { id: 'local' },
      { id: 'remote', restOnly: true },
    ]);
  });
});
