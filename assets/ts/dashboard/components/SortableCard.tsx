import React from 'react';
import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { GripVertical, X } from 'lucide-react';

export default function SortableCard({
  id,
  editing,
  onRemove,
  children
}: {
  id: string;
  editing: boolean;
  onRemove: () => void;
  children: React.ReactNode;
}) {
  const { attributes, listeners, setNodeRef, transform, transition } = useSortable({ id });
  const style = {
    transform: CSS.Transform.toString(transform),
    transition
  } as React.CSSProperties;
  let handleClass = 'p-1 cursor-grab';
  if (!editing) {
    handleClass += ' invisible';
  }

  let removeButton: React.ReactNode = null;
  if (editing) {
    removeButton = (
      <button onClick={onRemove} aria-label="Remove" className="p-1">
        <X size={16} />
      </button>
    );
  }

  return (
    <div
      ref={setNodeRef}
      style={style}
      className="bg-white rounded shadow p-4 flex flex-col"
    >
      <div className="flex justify-between items-start mb-2">
        <button
          className={handleClass}
          aria-label="Drag handle"
          {...attributes}
          {...listeners}
        >
          <GripVertical size={16} />
        </button>
        {removeButton}
      </div>
      <div className="flex-1">{children}</div>
    </div>
  );
}
