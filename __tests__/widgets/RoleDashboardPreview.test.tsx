import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import RoleDashboard from '../../js/components/RoleDashboard';
import useFilteredWidgets from '../../dashboard/useFilteredWidgets';

jest.mock('../../dashboard/useFilteredWidgets', () => ({
  __esModule: true,
  default: jest.fn(() => ({ widgets: [], error: null, retry: jest.fn() })),
}));

describe('RoleDashboard preview role', () => {
  beforeEach(() => {
    (window as any).RoleDashboardData = { widgets: [], currentUser: { roles: ['artist'] } };
    (window as any).wpApiSettings = { root: '/', nonce: 'nonce' };
    localStorage.clear();
    (globalThis as any).fetch = jest.fn(() =>
      Promise.resolve({
        json: () =>
          Promise.resolve({ widget_roles: {}, role_widgets: { artist: [], member: [], organization: [] } }),
      })
    );
  });

  it('passes preview role to useFilteredWidgets', async () => {
    const mockUse = useFilteredWidgets as unknown as jest.Mock;

    render(<RoleDashboard />);

    await screen.findByRole('option', { name: 'member' });

    mockUse.mockClear();

    const select = screen.getByRole('combobox');
    fireEvent.change(select, { target: { value: 'member' } });

    expect(localStorage.getItem('ap_preview_role')).toBe('member');
    expect(mockUse.mock.calls[0][1]).toEqual({ roles: ['member'] });
  });
});
