import React from 'react';
import { render } from '@testing-library/react';
import RoleDashboard from '../RoleDashboard';

describe('RoleDashboard without restBase', () => {
  beforeEach(() => {
    (window as any).apDashboardData = { nonce: '', seenDashboardV2: true };
    window.localStorage.clear();
    global.fetch = jest.fn();
  });

  it('renders without fetching when restBase missing', () => {
    render(<RoleDashboard role="member" initialEdit={false} />);
    expect(global.fetch).not.toHaveBeenCalled();
  });
});
