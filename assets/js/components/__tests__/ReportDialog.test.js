import { render, screen, fireEvent } from '@testing-library/react';
import ReportDialog from '../ReportDialog.jsx';

test('submits selected reason', () => {
  const onSubmit = jest.fn();
  const onClose = jest.fn();
  render(<ReportDialog onSubmit={onSubmit} onClose={onClose} />);
  fireEvent.change(screen.getByRole('combobox'), { target: { value: 'Abuse' } });
  fireEvent.click(screen.getByRole('button', { name: 'Submit' }));
  expect(onSubmit).toHaveBeenCalledWith('Abuse', '');
});
