import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import RoleDashboard from '../RoleDashboard';

describe('RoleDashboard states', () => {
  beforeEach(() => {
    (window as any).apDashboardData = {
      restBase: '/',
      nonce: '',
      seenDashboardV2: false,
    };
    window.localStorage.clear();
    global.fetch = jest.fn(() =>
      Promise.resolve({ ok: true, json: () => Promise.resolve({ layout: [] }) })
    ) as any;
  });

  it("shows what's new dialog initially and closes on Got it", async () => {
    render(<RoleDashboard role="artist" initialEdit={false} />);
    expect(
      screen.getByRole('dialog', { name: /what's new in roles dashboard/i })
    ).toBeInTheDocument();

    fireEvent.click(screen.getByRole('button', { name: /got it/i }));
    await waitFor(() =>
      expect(
        screen.queryByRole('dialog', { name: /what's new in roles dashboard/i })
      ).not.toBeInTheDocument()
    );

    // markSeen triggered
    expect(
      (global.fetch as jest.Mock).mock.calls.some((c: any[]) =>
        String(c[0]).includes('seen-dashboard-v2')
      )
    ).toBe(true);
  });
});
