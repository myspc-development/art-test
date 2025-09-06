import { renderHook, waitFor, act } from '@testing-library/react';
import useFilteredWidgets from '../useFilteredWidgets';

describe('useFilteredWidgets', () => {
  test('includes/excludes widgets based on roles, capabilities and exclusions', async () => {
    (global.fetch as jest.Mock).mockImplementation(() =>
      Promise.resolve({
        ok: true,
        json: () =>
          Promise.resolve({
            widget_roles: { alpha: [], beta: [] },
            capabilities: { beta: 'edit_posts' },
            excluded_roles: { beta: ['banned'] },
          }),
      })
    );

    const widgets = [
      { id: 'alpha' },
      { id: 'beta' },
    ];

    const { result } = renderHook(() =>
      useFilteredWidgets(widgets, {
        roles: ['member'],
        capabilities: ['edit_posts'],
      })
    );
    await waitFor(() => expect(result.current.loading).toBe(false));
    const ids = result.current.widgets.map(w => w.id);
    expect(ids).toContain('alpha');
    expect(ids).toContain('beta');

    const { result: banned } = renderHook(() =>
      useFilteredWidgets(widgets, {
        roles: ['banned'],
        capabilities: ['edit_posts'],
      })
    );
    await waitFor(() => expect(banned.current.loading).toBe(false));
    const bannedIds = banned.current.widgets.map(w => w.id);
    expect(bannedIds).toContain('alpha');
    expect(bannedIds).not.toContain('beta');
  });

  test('sets error on failed fetch', async () => {
    (global.fetch as jest.Mock).mockResolvedValueOnce({ ok: false, status: 500 });
    const widgets = [{ id: 'alpha' }];
    const { result } = renderHook(() =>
      useFilteredWidgets(widgets, { roles: ['member'] })
    );
    await waitFor(() => expect(result.current.loading).toBe(false));
    expect(result.current.error).toMatch(/500/);
    expect(result.current.widgets.map(w => w.id)).toEqual(['alpha']);
  });

  test('retry re-fetches configuration and updates widgets', async () => {
    (global.fetch as jest.Mock)
      .mockResolvedValueOnce({
        ok: true,
        json: () =>
          Promise.resolve({ widget_roles: { alpha: ['member'], beta: ['admin'] } }),
      })
      .mockResolvedValueOnce({
        ok: true,
        json: () =>
          Promise.resolve({ widget_roles: { alpha: ['member'], beta: ['member'] } }),
      });

    const widgets = [{ id: 'alpha' }, { id: 'beta' }];
    const { result } = renderHook(() =>
      useFilteredWidgets(widgets, { roles: ['member'] })
    );
    await waitFor(() => expect(result.current.loading).toBe(false));
    expect(result.current.widgets.map(w => w.id)).toEqual(['alpha']);

    await act(async () => {
      result.current.retry();
    });
    await waitFor(() => expect(result.current.loading).toBe(false));
    expect(result.current.widgets.map(w => w.id)).toEqual(['alpha', 'beta']);
  });
});
