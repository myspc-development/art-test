import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import AddWidgetDialog from '../AddWidgetDialog';

describe('AddWidgetDialog', () => {
  const widgets = [
    { id: 'one', title: 'One', description: '', component: () => null },
    { id: 'two', title: 'Two', description: '', component: () => null }
  ];

  test('filters widgets by query and triggers onAdd', () => {
    const onAdd = jest.fn();
    render(
      <AddWidgetDialog available={widgets} onAdd={onAdd} onClose={() => {}} />
    );
    fireEvent.change(screen.getByPlaceholderText(/search/i), {
      target: { value: 'Two' }
    });
    expect(screen.queryByText('One')).not.toBeInTheDocument();
    fireEvent.click(screen.getByText('Two'));
    expect(onAdd).toHaveBeenCalledWith('two');
  });

  test('close button triggers onClose', () => {
    const onClose = jest.fn();
    render(
      <AddWidgetDialog available={widgets} onAdd={() => {}} onClose={onClose} />
    );
    fireEvent.click(screen.getByText(/close/i));
    expect(onClose).toHaveBeenCalled();
  });
});
