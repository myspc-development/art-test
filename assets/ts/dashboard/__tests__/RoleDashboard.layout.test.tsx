import React from 'react';
import { render, screen, waitFor } from '@testing-library/react';
import RoleDashboard from '../RoleDashboard';

describe('RoleDashboard layout loading', () => {
  beforeEach(() => {
    (window as any).apDashboardData = { restBase: '/', nonce: '', seenDashboardV2: true };
    window.localStorage.clear();
    global.fetch = jest.fn(() =>
      Promise.resolve({ ok: true, json: () => Promise.resolve({ layout: ['sales', 'inbox'] }) })
    ) as any;
  });

  it('loads layout from server when available', async () => {
    render(<RoleDashboard role="member" initialEdit={true} />);
    await waitFor(() => expect(screen.getAllByLabelText(/remove/i)).toHaveLength(2));
    expect(
      JSON.parse(
        window.localStorage.getItem('ap.dashboard.layout.v1:member') as string
      )
    ).toEqual(['sales', 'inbox']);
  });
});
