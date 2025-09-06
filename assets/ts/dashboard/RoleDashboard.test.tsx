/**
 * Minimal smoke test for RoleDashboard.
 * Ensures it renders and toggles customize mode.
 */
import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import RoleDashboard from './RoleDashboard';

describe('RoleDashboard', () => {
  beforeEach(() => {
    (window as any).apDashboardData = {
      restBase: '/',
      nonce: '',
      seenDashboardV2: true
    };
    globalThis.fetch = jest.fn(() =>
      Promise.resolve({ ok: true, json: () => Promise.resolve({ layout: [] }) })
    ) as any;
  });

  test('renders title and toggles customize', () => {
    render(<RoleDashboard role="artist" initialEdit={false} />);
    expect(screen.getByText(/dashboard/i)).toBeInTheDocument();

    const customizeButton = screen.getByText(/customize/i);
    fireEvent.click(customizeButton);
    expect(screen.getByText(/done/i)).toBeInTheDocument();
  });

  test("See what's new link opens modal", () => {
    (window as any).apDashboardData.seenDashboardV2 = false;
    render(<RoleDashboard role="artist" initialEdit={false} />);
    const link = screen.getByText(/see what's new/i);
    fireEvent.click(link);
    const dialog = screen.getByRole('dialog', { name: /what's new in roles dashboard/i });
    expect(dialog).toBeInTheDocument();
    fireEvent.keyDown(window, { key: 'Escape' });
    expect(
      screen.queryByRole('dialog', { name: /what's new in roles dashboard/i })
    ).toBeNull();
  });

  test('Help button opens sheet', async () => {
    render(<RoleDashboard role="artist" initialEdit={false} />);
    const btn = screen.getByText('?');
    fireEvent.click(btn);
    const help = await screen.findByRole('dialog', { name: /keyboard help/i });
    expect(help).toBeInTheDocument();
    fireEvent.keyDown(window, { key: 'Escape' });
    expect(
      screen.queryByRole('dialog', { name: /keyboard help/i })
    ).toBeNull();
  });
});
