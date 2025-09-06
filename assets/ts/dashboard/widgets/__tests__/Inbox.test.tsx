import React from 'react';
import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';
import Inbox from '../Inbox';

describe('Inbox', () => {
  it('renders messages', () => {
    render(<Inbox />);
    expect(screen.getByText(/alex/i)).toBeInTheDocument();
    expect(screen.getByText(/thanks for sharing/i)).toBeInTheDocument();
    expect(screen.getByText(/jordan/i)).toBeInTheDocument();
    expect(screen.getByText(/can we meet tomorrow/i)).toBeInTheDocument();
  });
});
