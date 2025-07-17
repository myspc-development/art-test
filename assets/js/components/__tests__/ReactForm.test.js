import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import ReactForm from '../../../src/components/ReactForm.jsx';

global.fetch = jest.fn(() => Promise.resolve({
  json: () => Promise.resolve({ message: 'Saved' })
}));

beforeEach(() => {
  window.apReactForm = { ajaxurl: '/api', nonce: '123' };
  fetch.mockClear();
});

test('submits form and shows status', async () => {
  render(<ReactForm />);
  fireEvent.change(screen.getByPlaceholderText('Name'), { target: { value: 'Al' } });
  fireEvent.change(screen.getByPlaceholderText('Email'), { target: { value: 'al@example.com' } });
  fireEvent.click(screen.getByText('Submit'));
  expect(fetch).toHaveBeenCalledTimes(1);
  await waitFor(() => expect(screen.getByText('Saved')).toBeInTheDocument());
});
