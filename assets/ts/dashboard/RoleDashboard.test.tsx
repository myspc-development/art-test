/**
 * Minimal smoke test for RoleDashboard.
 * Ensures it renders and toggles customize mode.
 */
import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import RoleDashboard from './RoleDashboard';

describe('RoleDashboard', () => {
  test('renders title and toggles customize', () => {
    render(<RoleDashboard role="artist" initialEdit={false} />);
    expect(screen.getByText(/dashboard/i)).toBeInTheDocument();

    // Find the Customize button and toggle edit mode
    const customizeButton = screen.getByText(/customize/i);
    fireEvent.click(customizeButton);
    expect(screen.getByText(/done/i)).toBeInTheDocument();
  });
});
