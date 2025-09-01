import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import ReactForm from '../ReactForm.jsx';

describe('ReactForm smoke test', () => {
  beforeEach(() => {
    window.apReactForm = { ajaxurl: '/fake', nonce: '123' };
    global.fetch = jest.fn().mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({ message: 'Thanks!' }),
    });
  });

  afterEach(() => {
    delete window.apReactForm;
    global.fetch.mockClear();
  });

  it('posts form data and renders server response', async () => {
    render(<ReactForm />);

    fireEvent.change(screen.getByLabelText('Your name'), { target: { value: 'Alice' } });
    fireEvent.change(screen.getByLabelText('Email address'), { target: { value: 'alice@example.com' } });
    fireEvent.submit(screen.getByRole('button', { name: /submit/i }).closest('form'));

    expect(global.fetch).toHaveBeenCalledTimes(1);
    expect(global.fetch).toHaveBeenCalledWith('/fake', expect.objectContaining({
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    }));

    await waitFor(() => expect(screen.getByText('Thanks!')).toBeInTheDocument());
  });
});
