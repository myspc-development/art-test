import { render, screen, fireEvent } from '@testing-library/react';
import WidgetSettingsForm from '../WidgetSettingsForm.jsx';

test('calls onChange when field updates', () => {
  const handle = jest.fn();
  const schema = [ { key: 'title', label: 'Title' } ];
  render(<WidgetSettingsForm schema={schema} values={{}} onChange={handle} />);
  fireEvent.change(screen.getByLabelText('Title'), { target: { value: 'My Widget' } });
  expect(handle).toHaveBeenCalledWith('title', 'My Widget');
});
