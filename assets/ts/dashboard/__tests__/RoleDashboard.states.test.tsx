import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import RoleDashboard from '../RoleDashboard';

const setup = (data = { restBase: '/', nonce: '', seenDashboardV2: false }) => {
  (window as any).apDashboardData = data;
  global.fetch = jest.fn(() =>
    Promise.resolve({ ok: true, json: () => Promise.resolve({ layout: [] }) })
  ) as any;
  render(<RoleDashboard role="artist" initialEdit={false} />);
};

describe('RoleDashboard states', () => {
  test("shows what's new modal when unseen", () => {
    setup();
    expect(
      screen.getByRole('dialog', { name: /what's new in roles dashboard/i })
    ).toBeInTheDocument();
  });

  test("closes what's new modal on dismiss", () => {
    setup();
    fireEvent.click(screen.getByRole('button', { name: /got it/i }));
    expect(
      screen.queryByRole('dialog', { name: /what's new in roles dashboard/i })
    ).not.toBeInTheDocument();
  });
});

