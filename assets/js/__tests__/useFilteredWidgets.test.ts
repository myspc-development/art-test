import { renderHook, waitFor } from '@testing-library/react';
import useFilteredWidgets from '../../../dashboard/useFilteredWidgets';

test('returns widgets matching any user role', async () => {
  const widgets = [
    { id: 'alpha', roles: ['member'] },
    { id: 'beta', roles: ['artist'] },
    { id: 'shared', roles: ['member', 'artist'] },
  ];
  const { result } = renderHook(() => useFilteredWidgets(widgets, { roles: ['member', 'artist'] }));
  await waitFor(() => expect(result.current.loading).toBe(false));
  await waitFor(() =>
    expect(result.current.widgets.map(w => w.id)).toEqual(['alpha', 'beta', 'shared'])
  );
});

test('includes widgets with no allowed roles for any user', async () => {
  const widgets = [{ id: 'alpha' }];
  const { result } = renderHook(() => useFilteredWidgets(widgets, { roles: ['member'] }));
  await waitFor(() => expect(result.current.loading).toBe(false));
  await waitFor(() =>
    expect(result.current.widgets.map(w => w.id)).toEqual(['alpha'])
  );
});

test('includes REST-only widgets as stubs', async () => {
  (global.fetch as jest.Mock).mockResolvedValueOnce({
    ok: true,
    status: 200,
    json: () => Promise.resolve({ widget_roles: { gamma: ['member'] } }),
  });
  const { result } = renderHook(() => useFilteredWidgets([{ id: 'alpha', roles: ['member'] }], { roles: ['member'] }));
  await waitFor(() => expect(result.current.widgets.some(w => w.id === 'gamma')).toBe(true));
  await waitFor(() =>
    expect(result.current.widgets.find(w => w.id === 'gamma')?.restOnly).toBe(true)
  );
});

test('includes REST-only widgets with no allowed roles for any user', async () => {
  (global.fetch as jest.Mock).mockResolvedValueOnce({
    ok: true,
    status: 200,
    json: () => Promise.resolve({ widget_roles: { gamma: [] } }),
  });
  const { result } = renderHook(() => useFilteredWidgets([], { roles: ['member'] }));
  await waitFor(() => expect(result.current.widgets.some(w => w.id === 'gamma')).toBe(true));
});

test('allows all widgets when user has no roles', async () => {
  const widgets = [{ id: 'alpha', roles: ['member'] }];
  const { result } = renderHook(() => useFilteredWidgets(widgets, { roles: [] }));
  await waitFor(() => expect(result.current.loading).toBe(false));
  await waitFor(() =>
    expect(result.current.widgets.map(w => w.id)).toEqual(['alpha'])
  );
});

test('fetches dashboard config from REST endpoint', async () => {
  renderHook(() => useFilteredWidgets([], { roles: [] }));
  await waitFor(() =>
    expect((global.fetch as jest.Mock)).toHaveBeenCalledWith(
      '/wp-json/artpulse/v1/dashboard-config',
      expect.anything()
    )
  );
});

test('omits widgets when preview role lacks capability', async () => {
  (global.fetch as jest.Mock).mockResolvedValueOnce({
    ok: true,
    status: 200,
    json: () =>
      Promise.resolve({
        widget_roles: { alpha: ['member'] },
        capabilities: { alpha: 'edit_posts' },
      }),
  });
  const widgets = [{ id: 'alpha', roles: ['member'] }];
  const { result } = renderHook(() =>
    useFilteredWidgets(widgets, { roles: ['member'], capabilities: [] })
  );
  await waitFor(() => expect(result.current.widgets.length).toBe(0));
});

test('omits widgets when preview role is excluded', async () => {
  (global.fetch as jest.Mock).mockResolvedValueOnce({
    ok: true,
    status: 200,
    json: () =>
      Promise.resolve({
        widget_roles: { beta: ['member'] },
        excluded_roles: { beta: ['member'] },
      }),
  });
  const widgets = [{ id: 'beta', roles: ['member'] }];
  const { result } = renderHook(() =>
    useFilteredWidgets(widgets, { roles: ['member'] })
  );
  await waitFor(() => expect(result.current.widgets.length).toBe(0));
});

test('reports loading state', async () => {
  const { result } = renderHook(() => useFilteredWidgets([], { roles: [] }));
  expect(result.current.loading).toBe(true);
  await waitFor(() => expect(result.current.loading).toBe(false));
});

test('sets error when fetch rejects and keeps widgets unchanged', async () => {
  (global.fetch as jest.Mock).mockRejectedValueOnce(new Error('Network error'));
  const widgets = [{ id: 'alpha', roles: ['member'] }];
  const { result } = renderHook(() =>
    useFilteredWidgets(widgets, { roles: ['member'] })
  );
  await waitFor(() => expect(result.current.loading).toBe(false));
  await waitFor(() => expect(result.current.error).not.toBeNull());
  expect(result.current.widgets.map(w => w.id)).toEqual(['alpha']);
});

test('sets error when fetch returns non-ok response', async () => {
  (global.fetch as jest.Mock).mockResolvedValueOnce({ ok: false, status: 500 });
  const widgets = [{ id: 'alpha', roles: ['member'] }];
  const { result } = renderHook(() =>
    useFilteredWidgets(widgets, { roles: ['member'] })
  );
  await waitFor(() => expect(result.current.loading).toBe(false));
  await waitFor(() => expect(result.current.error).toMatch(/500/));
  expect(result.current.widgets.map(w => w.id)).toEqual(['alpha']);
});

test('aborts fetch on unmount', () => {
  let aborted = false;
  (global.fetch as jest.Mock).mockImplementationOnce((url: any, { signal }: any) => {
    signal.addEventListener('abort', () => {
      aborted = true;
    });
    return new Promise(() => {});
  });
  const { unmount } = renderHook(() => useFilteredWidgets([], { roles: [] }));
  unmount();
  expect(aborted).toBe(true);
});
