import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';
import Tasks from '../Tasks';

describe('Tasks', () => {
  it('toggles task done state and line-through class when checkbox clicked', () => {
    render(<Tasks />);
    const checkbox = screen.getByRole('checkbox', { name: /finish portfolio/i });
    const text = screen.getByText('Finish portfolio');

    expect(checkbox).not.toBeChecked();
    expect(text).not.toHaveClass('line-through');

    fireEvent.click(checkbox);
    expect(checkbox).toBeChecked();
    expect(text).toHaveClass('line-through');

    fireEvent.click(checkbox);
    expect(checkbox).not.toBeChecked();
    expect(text).not.toHaveClass('line-through');
  });
});
