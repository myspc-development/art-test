import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import RoleDashboard from '../RoleDashboard';

describe('RoleDashboard states', () => {
  beforeEach(() => {
    (window as any).apDashboardData = { restBase: '/', nonce: '', seenDashboardV2: true };
    global.fetch = jest.fn(() =>
      Promise.resolve({ ok: true, json: () => Promise.resolve({ layout: [] }) })
    ) as any;
  });

  test("shows what's new modal when unseen", () => {
    (window as any).apDashboardData.seenDashboardV2 = false;
    render(<RoleDashboard role="artist" initialEdit={false} />);
    const modal = screen.getByRole('dialog', { name: /what's new in roles dashboard/i });
    expect(modal).toBeInTheDocument();
    fireEvent.click(screen.getByRole('button', { name: /got it/i }));
    expect(
      screen.queryByRole('dialog', { name: /what's new in roles dashboard/i })
    ).not.toBeInTheDocument();
  });

  test('help button opens help dialog', () => {
    render(<RoleDashboard role="artist" initialEdit={false} />);
    fireEvent.click(screen.getByText('?'));
    expect(
      screen.getByRole('dialog', { name: /keyboard help/i })
    ).toBeInTheDocument();
  });
});
