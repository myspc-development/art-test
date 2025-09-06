import React from 'react';
import { render, screen } from '@testing-library/react';
import RoleDashboard, { PRESETS, Role } from '../RoleDashboard';

describe('RoleDashboard role layouts', () => {
  const baseData = { restBase: '/', nonce: '', seenDashboardV2: true };
  beforeEach(() => {
    (window as any).apDashboardData = baseData;
    window.localStorage.clear();
    global.fetch = jest.fn(() =>
      Promise.resolve({ ok: true, json: () => Promise.resolve({ layout: [] }) })
    ) as any;
  });

  it.each(Object.keys(PRESETS) as Role[])(
    'renders widgets for %s role',
    role => {
      render(<RoleDashboard role={role} initialEdit={true} />);
      expect(screen.getAllByLabelText(/remove/i)).toHaveLength(PRESETS[role].length);
    }
  );

  it.each(['donor', 'sponsor'])(
    'renders no widgets for unknown role %s',
    role => {
      render(<RoleDashboard role={role as any} initialEdit={true} />);
      expect(screen.queryAllByLabelText(/remove/i)).toHaveLength(0);
    }
  );
});
