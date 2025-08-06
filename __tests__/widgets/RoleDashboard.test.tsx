import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import RoleDashboard from '../../js/components/RoleDashboard';
import useFilteredWidgets from '../../dashboard/useFilteredWidgets';

jest.mock('../../dashboard/useFilteredWidgets', () => ({
  __esModule: true,
  default: jest.fn(),
}));

describe('RoleDashboard widget filtering', () => {
  const mockUse = useFilteredWidgets as unknown as jest.Mock;

  beforeEach(() => {
    mockUse.mockImplementation((widgets, { roles }) => {
      if (roles.includes('artist')) {
        return {
          widgets: widgets.filter((w: any) => ['news', 'events'].includes(w.id)),
          error: null,
          retry: jest.fn(),
        };
      }
      if (roles.includes('member')) {
        return {
          widgets: widgets.filter((w: any) => w.id === 'alerts'),
          error: null,
          retry: jest.fn(),
        };
      }
      return { widgets: [], error: null, retry: jest.fn() };
    });

    (window as any).RoleDashboardData = {
      widgets: [
        { id: 'news', html: '<h2>news-heading</h2>' },
        { id: 'events', html: '<h2>events-heading</h2>' },
        { id: 'alerts', html: '<h2>alerts-heading</h2>' },
      ],
      currentUser: { roles: ['artist'] },
    };

    (window as any).wpApiSettings = { root: '/', nonce: 'nonce' };
    localStorage.clear();
    (global as any).fetch = jest.fn(() =>
      Promise.resolve({
        json: () =>
          Promise.resolve({ widget_roles: {}, role_widgets: { artist: [], member: [] } }),
      })
    );
  });

  it('shows widgets for current role and updates on preview role change', async () => {
    render(<RoleDashboard />);

    await screen.findByRole('option', { name: 'member' });

    expect(await screen.findByRole('region', { name: /news-heading/i })).toBeInTheDocument();
    expect(await screen.findByRole('region', { name: /events-heading/i })).toBeInTheDocument();
    expect(screen.queryByRole('region', { name: /alerts-heading/i })).toBeNull();

    mockUse.mockClear();

    const select = screen.getByRole('combobox');
    fireEvent.change(select, { target: { value: 'member' } });

    expect(mockUse.mock.calls[0][1]).toEqual({ roles: ['member'] });

    expect(await screen.findByRole('region', { name: /alerts-heading/i })).toBeInTheDocument();
    expect(screen.queryByRole('region', { name: /news-heading/i })).toBeNull();
    expect(screen.queryByRole('region', { name: /events-heading/i })).toBeNull();
  });
});

