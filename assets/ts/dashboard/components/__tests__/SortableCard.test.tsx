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

  describe('when editing', () => {
    test('shows controls and triggers onRemove', () => {
      const onRemove = jest.fn();
      setup(true, onRemove);
      expect(screen.getByLabelText(/drag handle/i)).not.toHaveClass('invisible');
      fireEvent.click(screen.getByLabelText(/remove/i));
      expect(onRemove).toHaveBeenCalledTimes(1);
    });
  });

  describe('when not editing', () => {
    test('hides controls and does not call onRemove', () => {
      const onRemove = jest.fn();
      setup(false, onRemove);
      expect(screen.getByLabelText(/drag handle/i)).toHaveClass('invisible');
      expect(screen.queryByLabelText(/remove/i)).toBeNull();
      expect(onRemove).not.toHaveBeenCalled();
    });
  });
});
