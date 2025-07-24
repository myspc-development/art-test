import { renderHook } from '@testing-library/react';
import useFilteredWidgets from '../../dashboard/useFilteredWidgets';

global.fetch = jest.fn(() => Promise.resolve({ json: () => Promise.resolve({ widget_roles: {} }) })) as any;

test('returns widgets matching any user role', async () => {
  const widgets = [
    { id: 'alpha', roles: ['member'] },
    { id: 'beta', roles: ['artist'] },
    { id: 'shared', roles: ['member', 'artist'] },
  ];
  const { result } = renderHook(() => useFilteredWidgets(widgets, { roles: ['member', 'artist'] }));
  expect(result.current.map(w => w.id)).toEqual(['alpha', 'beta', 'shared']);
});
