import { renderHook, waitFor } from '@testing-library/react';
import useFilteredWidgets from '../../dashboard/useFilteredWidgets';

const mockFetch = jest.fn(() => Promise.resolve({ json: () => Promise.resolve({ widget_roles: {} }) })) as any;
global.fetch = mockFetch;

test('returns widgets matching any user role', async () => {
  const widgets = [
    { id: 'alpha', roles: ['member'] },
    { id: 'beta', roles: ['artist'] },
    { id: 'shared', roles: ['member', 'artist'] },
  ];
  const { result } = renderHook(() => useFilteredWidgets(widgets, { roles: ['member', 'artist'] }));
  expect(result.current.widgets.map(w => w.id)).toEqual(['alpha', 'beta', 'shared']);
});

test('includes REST-only widgets as stubs', async () => {
  mockFetch.mockResolvedValueOnce({ json: () => Promise.resolve({ widget_roles: { gamma: ['member'] } }) });
  const { result } = renderHook(() => useFilteredWidgets([{ id: 'alpha', roles: ['member'] }], { roles: ['member'] }));
  await waitFor(() => expect(result.current.widgets.some(w => w.id === 'gamma')).toBe(true));
  expect(result.current.widgets.find(w => w.id === 'gamma')?.restOnly).toBe(true);
});

test('omits widgets when preview role lacks capability', async () => {
  mockFetch.mockResolvedValueOnce({
    json: () => Promise.resolve({
      widget_roles: { alpha: ['member'] },
      capabilities: { alpha: 'edit_posts' },
    }),
  });
  const widgets = [{ id: 'alpha', roles: ['member'] }];
  const { result } = renderHook(() =>
    useFilteredWidgets(widgets, { roles: ['member'], capabilities: [] })
  );
  await waitFor(() => expect(result.current.widgets).toBeDefined());
  expect(result.current.widgets.length).toBe(0);
});

test('omits widgets when preview role is excluded', async () => {
  mockFetch.mockResolvedValueOnce({
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
  await waitFor(() => expect(result.current.widgets).toBeDefined());
  expect(result.current.widgets.length).toBe(0);
});
