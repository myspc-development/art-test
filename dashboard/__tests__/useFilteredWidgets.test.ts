import { renderHook, waitFor, act } from '@testing-library/react';
import useFilteredWidgets from '../useFilteredWidgets';

describe('useFilteredWidgets', () => {
  beforeEach(() => {
    (global.fetch as jest.Mock).mockReset();
  });

  test('filters widgets by roles, capabilities, and excluded roles', async () => {
    (global.fetch as jest.Mock).mockResolvedValueOnce({
      ok: true,
      status: 200,
      json: () =>
        Promise.resolve({
          widget_roles: {
            alpha: ['member'],
            beta: ['member'],
            delta: ['member'],
          },
          capabilities: { beta: 'edit_posts' },
          excluded_roles: { delta: ['member'] },
        }),
    });

    const widgets = [
      { id: 'alpha', roles: ['member'] },
      { id: 'beta', roles: ['member'] },
      { id: 'gamma', roles: ['member'] },
      { id: 'delta', roles: ['member'] },
    ];

    const { result } = renderHook(() =>
      useFilteredWidgets(widgets, { roles: ['member'], capabilities: [] })
    );

    await waitFor(() => expect(result.current.loading).toBe(false));
    expect(result.current.widgets.map(w => w.id)).toEqual(['alpha', 'gamma']);
  });

  test('retry re-fetches configuration after failure', async () => {
    (global.fetch as jest.Mock)
      .mockRejectedValueOnce(new Error('Network error'))
      .mockResolvedValueOnce({
        ok: true,
        status: 200,
        json: () => Promise.resolve({ widget_roles: { beta: ['member'] } }),
      });

    const widgets = [{ id: 'alpha', roles: ['member'] }];
    const { result } = renderHook(() =>
      useFilteredWidgets(widgets, { roles: ['member'] })
    );

    await waitFor(() => expect(result.current.loading).toBe(false));
    expect(result.current.error).toBeTruthy();
    expect(result.current.widgets.map(w => w.id)).toEqual(['alpha']);
    expect((global.fetch as jest.Mock).mock.calls.length).toBe(1);

    await act(async () => {
      result.current.retry();
    });

    await waitFor(() => expect(result.current.loading).toBe(false));
    expect(result.current.error).toBeNull();
    expect((global.fetch as jest.Mock).mock.calls.length).toBe(2);
    expect(result.current.widgets.map(w => w.id)).toEqual(['alpha', 'beta']);
  });
});

