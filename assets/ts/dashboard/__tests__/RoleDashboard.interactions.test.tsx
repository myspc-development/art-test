import React from 'react';
import { render } from '@testing-library/react';
import RoleDashboard from '../RoleDashboard';

jest.mock('@/dashboard/useFilteredWidgets', () => ({
  __esModule: true,
  useFilteredWidgets: jest.fn(() => ({ widgets: [], error: null, retry: jest.fn() })),
}));
const { useFilteredWidgets: mockUseFilteredWidgets } = require('@/dashboard/useFilteredWidgets');

describe('RoleDashboard interactions', () => {
  beforeEach(() => {
    (window as any).apDashboardData = {
      restBase: '/',
      nonce: '',
      seenDashboardV2: true,
    };
    globalThis.fetch = jest.fn(() =>
      Promise.resolve({ ok: true, json: () => Promise.resolve({ layout: [] }) })
    ) as any;
  });

  it('renders without crashing', () => {
    render(<RoleDashboard role="artist" initialEdit={false} />);
  });
});
