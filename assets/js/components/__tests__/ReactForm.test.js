import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import ReactForm from '../../../../src/components/ReactForm.jsx';

global.fetch = jest.fn();

beforeEach(() => {
  window.apReactForm = { ajaxurl: '/api', nonce: '123' };
  fetch.mockReset();
});

test('submits form and shows status with custom type', async () => {
  fetch.mockResolvedValueOnce({
    ok: true,
    json: async () => ({ message: 'Saved' }),
  });

  render(<ReactForm type="special" />);
  fireEvent.change(screen.getByPlaceholderText('Name'), { target: { value: 'Al' } });
  fireEvent.change(screen.getByPlaceholderText('Email'), { target: { value: 'al@example.com' } });
  fireEvent.click(screen.getByText('Submit'));

  expect(fetch).toHaveBeenCalledTimes(1);
  await waitFor(() => expect(screen.getByText('Saved (special)')).toBeInTheDocument());
});

test('shows error message on failed submission', async () => {
  fetch.mockResolvedValueOnce({ ok: false });

  render(<ReactForm />);
  fireEvent.change(screen.getByPlaceholderText('Name'), { target: { value: 'Al' } });
  fireEvent.change(screen.getByPlaceholderText('Email'), { target: { value: 'al@example.com' } });
  fireEvent.click(screen.getByText('Submit'));

  expect(fetch).toHaveBeenCalledTimes(1);
  await waitFor(() =>
    expect(
      screen.getByText('There was an error submitting the form.')
    ).toBeInTheDocument()
  );
});
