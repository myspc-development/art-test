import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import { DndContext } from '@dnd-kit/core';
import { SortableContext, rectSortingStrategy } from '@dnd-kit/sortable';
import SortableCard from '../SortableCard';

describe('SortableCard', () => {
  function setup(editing: boolean, onRemove = jest.fn()) {
    return render(
      <DndContext>
        <SortableContext items={['a']} strategy={rectSortingStrategy}>
          <SortableCard id="a" editing={editing} onRemove={onRemove}>
            <div>Widget</div>
          </SortableCard>
        </SortableContext>
      </DndContext>
    );
  }

  test('calls onRemove when remove button clicked', () => {
    const onRemove = jest.fn();
    setup(true, onRemove);
    fireEvent.click(screen.getByLabelText(/remove/i));
    expect(onRemove).toHaveBeenCalled();
  });

  test('hides drag handle when not editing', () => {
    setup(false);
    expect(screen.getByLabelText(/drag handle/i)).toHaveClass('invisible');
    expect(screen.queryByLabelText(/remove/i)).toBeNull();
  });

  test('shows drag handle when editing', () => {
    setup(true);
    expect(screen.getByLabelText(/drag handle/i)).not.toHaveClass('invisible');
  });
});
