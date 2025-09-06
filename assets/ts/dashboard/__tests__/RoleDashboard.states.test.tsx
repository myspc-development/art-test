import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import RoleDashboard from '../RoleDashboard';

    fireEvent.click(screen.getByRole('button', { name: /got it/i }));
    expect(
      screen.queryByRole('dialog', { name: /what's new in roles dashboard/i })
    ).not.toBeInTheDocument();

  });
});

