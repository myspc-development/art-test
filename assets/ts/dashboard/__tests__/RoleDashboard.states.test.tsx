import React from 'react';
import { render, screen } from '@testing-library/react';
import RoleDashboard from '@/js/components/RoleDashboard';

jest.mock('@/dashboard/useFilteredWidgets', () => ({ __esModule: true, default: jest.fn() }));
const useFilteredWidgets = require('@/dashboard/useFilteredWidgets').default as jest.Mock;

const baseWidgets = [
  { id: 'overview', html: '<h2>Overview</h2>' },
  { id: 'gamma', title: 'Gamma', restOnly: true },
];

const renderDash = (preview = { roles: ['editor'], capabilities: [] }) => {
  (window as any).RoleDashboardData = { widgets: baseWidgets, currentUser: preview };
  (window as any).wpApiSettings = { root: '/', nonce: '' };
  global.fetch = jest.fn((url: string) => {
    if (url.includes('dashboard-widget/gamma')) {
      return Promise.resolve({ ok: true, text: () => Promise.resolve('<h2>Gamma</h2>') });
    }
    return Promise.resolve({ ok: true, json: () => Promise.resolve({ layout: [] }) });
  }) as any;
  return render(<RoleDashboard />);
};

describe('RoleDashboard states', () => {
  afterEach(() => {
    useFilteredWidgets.mockReset();
  });

  test('shows loading state', () => {
    useFilteredWidgets.mockReturnValue({ widgets: [], loading: true, error: null, retry: jest.fn() });
    renderDash();
    const loading = screen.getByTestId('dashboard-loading');
    expect(loading).toBeInTheDocument();
  });

  test('shows error state', () => {
    useFilteredWidgets.mockReturnValue({ widgets: [], loading: false, error: 'Boom', retry: jest.fn() });
    renderDash();
    expect(screen.getByText(/boom/i)).toBeInTheDocument();
  });

  test('renders normal + REST-only widgets', async () => {
    useFilteredWidgets.mockReturnValue({ widgets: baseWidgets, loading: false, error: null, retry: jest.fn() });
    renderDash({ roles: ['subscriber'], capabilities: [] });
    expect(await screen.findByText(/overview/i)).toBeInTheDocument();
    expect(await screen.findByText(/gamma/i)).toBeInTheDocument();
  });

  test('omits capability-gated widget when capability missing', () => {
    useFilteredWidgets.mockReturnValue({
      widgets: [{ id: 'needs-cap', html: '<h2>Secured</h2>', capability: 'manage_stuff' }],
      loading: false,
      error: null,
      retry: jest.fn(),
    });
    renderDash({ roles: ['editor'], capabilities: [] });
    expect(screen.queryByText(/secured/i)).not.toBeInTheDocument();
  });

  test('handles empty widgets gracefully', () => {
    useFilteredWidgets.mockReturnValue({ widgets: [], loading: false, error: null, retry: jest.fn() });
    renderDash();
    const empty = screen.getByText(/no widgets available/i);
    expect(empty).toBeInTheDocument();
  });
});
