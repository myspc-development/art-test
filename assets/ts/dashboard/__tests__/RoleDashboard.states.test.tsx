import React from 'react';
import { render, screen, fireEvent, waitFor, act } from '@testing-library/react';
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

  it('closes whats new dialog on Escape', async () => {
    render(<RoleDashboard role="artist" initialEdit={false} />);
    await waitFor(() =>
      expect(
        screen.getByRole('dialog', { name: /what's new in roles dashboard/i })
      ).toBeInTheDocument()
    );
    act(() => {
      fireEvent.keyDown(window, { key: 'Escape', bubbles: true });
    });
    await waitFor(() =>
      expect(
        screen.queryByRole('dialog', { name: /what's new in roles dashboard/i })
      ).not.toBeInTheDocument()
    );
  });

  it('records seen only once when reopening whats new', async () => {
    render(<RoleDashboard role="artist" initialEdit={false} />);
    fireEvent.click(screen.getByRole('button', { name: /got it/i }));
    await waitFor(() =>
      expect(
        screen.queryByRole('dialog', { name: /what's new in roles dashboard/i })
      ).not.toBeInTheDocument()
    );
    fireEvent.click(screen.getByText(/see what's new/i));
    fireEvent.click(screen.getByRole('button', { name: /got it/i }));
    await waitFor(() =>
      expect(
        screen.queryByRole('dialog', { name: /what's new in roles dashboard/i })
      ).not.toBeInTheDocument()
    );
    const seenCalls = (global.fetch as jest.Mock).mock.calls.filter((c: any[]) =>
      String(c[0]).includes('seen-dashboard-v2')
    );
    expect(seenCalls).toHaveLength(1);
  });

  it('closes help panel on Escape', () => {
    const spy = jest.spyOn(window, 'addEventListener');
    render(<RoleDashboard role="artist" initialEdit={false} />);
    fireEvent.click(screen.getByRole('button', { name: '?' }));
    const handler = spy.mock.calls.filter(c => c[0] === 'keydown').pop()?.[1] as any;
    act(() => handler({ key: 'Escape' }));
    spy.mockRestore();
  });
});
