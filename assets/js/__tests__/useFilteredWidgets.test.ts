import { renderHook, waitFor } from '@testing-library/react';
import useFilteredWidgets from '../../../dashboard/useFilteredWidgets';

const mockFetch = jest.fn(() =>
  Promise.resolve({
    ok: true,
    status: 200,
    json: () => Promise.resolve({ widget_roles: {} }),
  })
) as any;
(globalThis as any).fetch = mockFetch;

beforeEach(() => {
  mockFetch.mockReset();
  mockFetch.mockImplementation(() =>
    Promise.resolve({
      ok: true,
      status: 200,
      json: () => Promise.resolve({ widget_roles: {} }),
    })
  );
});

test('returns widgets matching any user role', async () => {
  const widgets = [
    { id: 'alpha', roles: ['member'] },
    { id: 'beta', roles: ['artist'] },
    { id: 'shared', roles: ['member', 'artist'] },
  ];
  const { result } = renderHook(() => useFilteredWidgets(widgets, { roles: ['member', 'artist'] }));
  await waitFor(() =>
    expect(result.current.widgets.map(w => w.id)).toEqual(['alpha', 'beta', 'shared'])
  );
});

test('includes widgets with no allowed roles for any user', async () => {
  const widgets = [{ id: 'alpha' }];
  const { result } = renderHook(() => useFilteredWidgets(widgets, { roles: ['member'] }));
  await waitFor(() =>
    expect(result.current.widgets.map(w => w.id)).toEqual(['alpha'])
  );
});

test('includes REST-only widgets as stubs', async () => {
  mockFetch.mockResolvedValueOnce({
    ok: true,
    status: 200,
    json: () => Promise.resolve({ widget_roles: { gamma: ['member'] } }),
  });
  const { result } = renderHook(() => useFilteredWidgets([{ id: 'alpha', roles: ['member'] }], { roles: ['member'] }));
  await waitFor(() => expect(result.current.widgets.some(w => w.id === 'gamma')).toBe(true));
  expect(result.current.widgets.find(w => w.id === 'gamma')?.restOnly).toBe(true);
});

test('includes REST-only widgets with no allowed roles for any user', async () => {
  mockFetch.mockResolvedValueOnce({
    ok: true,
    status: 200,
    json: () => Promise.resolve({ widget_roles: { gamma: [] } }),
  });
  const { result } = renderHook(() => useFilteredWidgets([], { roles: ['member'] }));
  await waitFor(() => expect(result.current.widgets.some(w => w.id === 'gamma')).toBe(true));
});

test('omits widgets when preview role lacks capability', async () => {
  mockFetch.mockResolvedValueOnce({
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
  mockFetch.mockResolvedValueOnce({
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

test('aborts fetch on unmount', () => {
  let aborted = false;
  mockFetch.mockImplementationOnce((url: any, { signal }: any) => {
    signal.addEventListener('abort', () => {
      aborted = true;
    });
    return new Promise(() => {});
  });
  const { unmount } = renderHook(() => useFilteredWidgets([], { roles: [] }));
  unmount();
  expect(aborted).toBe(true);
});
