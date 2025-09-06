import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import RoleDashboard from '../RoleDashboard';

describe('RoleDashboard accessibility', () => {
  beforeEach(() => {
    (window as any).apDashboardData = {
      restBase: '/',
      nonce: '',
      seenDashboardV2: true,
    };
    window.localStorage.clear();
    global.fetch = jest.fn(() =>
      Promise.resolve({ ok: true, json: () => Promise.resolve({ layout: [] }) })
    ) as any;
  });

  it('closes help dialog via Escape and close button', async () => {
    render(<RoleDashboard role="artist" initialEdit={false} />);

    fireEvent.click(screen.getByText('?'));
    expect(
      screen.getByRole('dialog', { name: /keyboard help/i })
    ).toBeInTheDocument();

    fireEvent.keyDown(document, { key: 'Escape', bubbles: true });
    await waitFor(() =>
      expect(
        screen.queryByRole('dialog', { name: /keyboard help/i })
      ).not.toBeInTheDocument()
    );

    // open again and close via button
    fireEvent.click(screen.getByText('?'));
    fireEvent.click(screen.getByRole('button', { name: /close/i }));
    await waitFor(() =>
      expect(
        screen.queryByRole('dialog', { name: /keyboard help/i })
      ).not.toBeInTheDocument()
    );
  });

  it('renders without global data', () => {
    delete (window as any).apDashboardData;
    render(<RoleDashboard role="member" initialEdit={false} />);
    expect(screen.getAllByText(/dashboard/i)[0]).toBeInTheDocument();
  });

  it('skips remote calls when restBase missing', () => {
    (window as any).apDashboardData = { nonce: '', seenDashboardV2: true };
    global.fetch = jest.fn();
    render(<RoleDashboard role="member" initialEdit={false} />);
    expect(global.fetch).not.toHaveBeenCalled();
  });
});
