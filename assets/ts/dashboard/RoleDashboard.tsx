import React, { useEffect, useRef, useState } from 'react';
import {
  DndContext,
  PointerSensor,
  KeyboardSensor,
  useSensor,
  useSensors,
  closestCenter,
  DragEndEvent
} from '@dnd-kit/core';
import {
  SortableContext,
  arrayMove,
  rectSortingStrategy,
  useSortable
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { GripVertical, X } from 'lucide-react';

import UpcomingEvents from './widgets/UpcomingEvents';
import Sales from './widgets/Sales';
import Tasks from './widgets/Tasks';
import Inbox from './widgets/Inbox';
import CalendarHeatmap from './widgets/CalendarHeatmap';

export type Role = 'member' | 'artist' | 'organization' | 'admin';

export interface WidgetMeta {
  id: string;
  title: string;
  description: string;
  component: React.ComponentType;
}

export const WIDGETS: WidgetMeta[] = [
  { id: 'upcoming', title: 'Upcoming Events', description: '', component: UpcomingEvents },
  { id: 'sales', title: 'Sales', description: '', component: Sales },
  { id: 'tasks', title: 'Tasks', description: '', component: Tasks },
  { id: 'inbox', title: 'Inbox', description: '', component: Inbox },
  { id: 'calendar', title: 'Calendar', description: '', component: CalendarHeatmap }
];

export const PRESETS: Record<Role, string[]> = {
  member: ['upcoming', 'tasks', 'inbox'],
  artist: ['sales', 'tasks', 'inbox', 'calendar'],
  organization: ['upcoming', 'inbox', 'calendar'],
  admin: ['upcoming', 'sales', 'tasks', 'inbox', 'calendar']
};

const storageKey = (role: Role) => `ap.dashboard.layout.v1:${role}`;

/* istanbul ignore next */
function getWidget(id: string): WidgetMeta {
  return WIDGETS.find(w => w.id === id)!;
}

/* istanbul ignore next */
function SortableCard({
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
  const {
    attributes,
    listeners,
    setNodeRef,
    transform,
    transition
  } = useSortable({ id });
  const style = {
    transform: CSS.Transform.toString(transform),
    transition
  } as React.CSSProperties;
  return (
    <div
      ref={setNodeRef}
      style={style}
      className="bg-white rounded shadow p-4 flex flex-col"
    >
      <div className="flex justify-between items-start mb-2">
      <button
          className={`p-1 cursor-grab ${editing ? '' : 'invisible'}`}
          aria-label="Drag handle"
          {...attributes}
          {...listeners}
        >
          <GripVertical size={16} />
        </button>
        {editing && (
          <button
            onClick={onRemove}
            aria-label="Remove"
            className="p-1"
          >
            <X size={16} />
          </button>
        )}
      </div>
      <div className="flex-1">{children}</div>
    </div>
  );
}

export default function RoleDashboard({
  role,
  initialEdit = false
}: {
  role: Role;
  initialEdit?: boolean;
}) {
  const data = (window as any).apDashboardData;
  const [showWhatsNew, setShowWhatsNew] = useState(!data?.seenDashboardV2);
  const [seenRecorded, setSeenRecorded] = useState(!!data?.seenDashboardV2);
  const [showHelp, setShowHelp] = useState(false);
  const whatsNewCloseRef = useRef<HTMLButtonElement>(null);
  const helpCloseRef = useRef<HTMLButtonElement>(null);
  useEffect(() => {
    if (showWhatsNew) whatsNewCloseRef.current?.focus();
  }, [showWhatsNew]);
  useEffect(() => {
    if (showHelp) helpCloseRef.current?.focus();
  }, [showHelp]);
  useEffect(() => {
    const onKey = (e: KeyboardEvent) => {
      if (e.key === 'Escape') {
        if (showWhatsNew) {
          closeWhatsNew();
        } else if (showHelp) {
          setShowHelp(false);
        }
      }
    };
    window.addEventListener('keydown', onKey);
    return () => window.removeEventListener('keydown', onKey);
  }, [showWhatsNew, showHelp]);
  const markSeen = () => {
    if (seenRecorded) return;
    fetch(`${data.restBase}user/seen-dashboard-v2`, {
      method: 'POST',
      headers: { 'X-WP-Nonce': data.nonce || '' }
    }).catch(() => {});
    setSeenRecorded(true);
  };
  const closeWhatsNew = () => {
    setShowWhatsNew(false);
    markSeen();
  };

  const [layout, setLayout] = useState<string[]>(() => {
    const saved = window.localStorage.getItem(storageKey(role));
    return saved ? JSON.parse(saved) : PRESETS[role] || [];
  });
  const [editing, setEditing] = useState(initialEdit);
  const [showAdd, setShowAdd] = useState(false);
  const sensors = useSensors(useSensor(PointerSensor), useSensor(KeyboardSensor));
  const liveRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const data = (window as any).apDashboardData;
    window.localStorage.setItem(storageKey(role), JSON.stringify(layout));
    if (data?.restBase) {
      fetch(`${data.restBase}user/layout`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': data.nonce || ''
        },
        body: JSON.stringify({ role, layout })
      }).catch(() => {});
    }
  }, [layout, role]);

  useEffect(() => {
    const data = (window as any).apDashboardData;
    if (data?.restBase) {
      fetch(`${data.restBase}user/layout?role=${role}`, {
        headers: { 'X-WP-Nonce': data.nonce || '' }
      })
        .then(r => (r.ok ? r.json() : null))
        .then(res => {
          if (res && Array.isArray(res.layout) && res.layout.length) {
            setLayout(res.layout);
          }
        })
        .catch(() => {});
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [role]);

  const announce = (msg: string) => {
    const el = liveRef.current;
    if (el) {
      el.textContent = msg;
      setTimeout(() => {
        if (el.textContent === msg) el.textContent = '';
      }, 1000);
    }
  };

  const handleDragEnd = (evt: DragEndEvent) => {
    const { active, over } = evt;
    if (over && active.id !== over.id) {
      const oldIndex = layout.indexOf(active.id as string);
      const newIndex = layout.indexOf(over.id as string);
      const newLayout = arrayMove(layout, oldIndex, newIndex);
      setLayout(newLayout);
      announce(`Moved ${getWidget(active.id as string).title} to position ${
        newIndex + 1
      }`);
    }
  };

  const available = WIDGETS.filter(w => !layout.includes(w.id));

  const addWidget = (id: string) => {
    setLayout([...layout, id]);
  };

  const removeWidget = (id: string) => {
    setLayout(layout.filter(w => w !== id));
  };

  const reset = () => setLayout(PRESETS[role]);

  return (
    <div>
      <div className="flex items-center justify-between mb-4">
        <h2 className="text-xl font-semibold">Dashboard</h2>
        <div className="space-x-2">
          <button
            className="px-2 py-1 border rounded"
            onClick={() => setShowHelp(true)}
            type="button"
            aria-haspopup="dialog"
          >
            ?
          </button>
          <button
            className="underline text-sm"
            onClick={() => setShowWhatsNew(true)}
            type="button"
          >
            See what's new
          </button>
          <button
            className="px-2 py-1 border rounded"
            onClick={() => setEditing(e => !e)}
            type="button"
          >
            {editing ? 'Done' : 'Customize'}
          </button>
          {editing && (
            <button
              className="px-2 py-1 border rounded"
              onClick={() => setShowAdd(true)}
              type="button"
            >
              Add widget
            </button>
          )}
          <button className="px-2 py-1 border rounded" onClick={reset} type="button">
            Reset
          </button>
        </div>
      </div>
      <DndContext
        sensors={sensors}
        collisionDetection={closestCenter}
        onDragEnd={handleDragEnd}
      >
        <SortableContext items={layout} strategy={rectSortingStrategy}>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 auto-rows-[minmax(10rem,auto)]">
            {layout.map(id => {
              const W = getWidget(id).component;
              return (
                <SortableCard
                  key={id}
                  id={id}
                  editing={editing}
                  onRemove={() => removeWidget(id)}
                >
                  <W />
                </SortableCard>
              );
            })}
          </div>
        </SortableContext>
      </DndContext>
      {showAdd && (
        <AddWidgetDialog
          available={available}
          onAdd={addWidget}
          onClose={() => setShowAdd(false)}
        />
      )}
      {showHelp && (
        <div
          className="fixed inset-0 bg-black/50 flex items-start justify-end"
          role="dialog"
          aria-modal="true"
          aria-labelledby="ap-help-title"
        >
          <div className="bg-white p-4 m-4 rounded w-80 shadow">
            <h2 id="ap-help-title" className="text-lg mb-2">
              Keyboard help
            </h2>
            <ul className="list-disc pl-5 text-sm space-y-1 mb-2">
              <li>
                <strong>Tabs:</strong> Use Left/Right/Home/End to move between
                tabs.
              </li>
              <li>
                <strong>Tab order:</strong> Focus follows the visual order of
                widgets.
              </li>
              <li>
                <strong>Drag handles:</strong> Focus a handle then press Space
                and arrow keys to move a widget.
              </li>
              <li>
                <strong>Section links:</strong> Widget titles link to full
                sections.
              </li>
              <li>
                <strong>Reduced motion:</strong> Enable "Reduce motion" in your
                device settings to limit animations.
              </li>
            </ul>
            <div className="text-right">
              <button
                ref={helpCloseRef}
                className="px-2 py-1 border rounded"
                onClick={() => setShowHelp(false)}
                type="button"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      )}
      {showWhatsNew && (
        <div
          className="fixed inset-0 bg-black/50 flex items-center justify-center"
          role="dialog"
          aria-modal="true"
          aria-labelledby="ap-whats-new-title"
        >
          <div className="bg-white p-4 rounded max-w-sm w-full shadow">
            <h2 id="ap-whats-new-title" className="text-lg mb-2">
              What's new in Roles Dashboard
            </h2>
            <p className="mb-4 text-sm">
              The dashboard includes improved keyboard navigation and a new help
              panel. Use the ? button any time for tips.
            </p>
            <div className="text-right">
              <button
                ref={whatsNewCloseRef}
                className="px-2 py-1 border rounded"
                onClick={closeWhatsNew}
                type="button"
              >
                Got it
              </button>
            </div>
          </div>
        </div>
      )}
      <div ref={liveRef} className="sr-only" aria-live="polite" />
    </div>
  );
}

/* istanbul ignore next */
function AddWidgetDialog({
  available,
  onAdd,
  onClose
}: {
  available: WidgetMeta[];
  onAdd: (id: string) => void;
  onClose: () => void;
}) {
  const [query, setQuery] = useState('');
  const list = available.filter(w =>
    w.title.toLowerCase().includes(query.toLowerCase())
  );
  return (
    <div className="fixed inset-0 bg-black/50 flex items-center justify-center">
      <div className="bg-white p-4 rounded w-80">
        <h2 className="text-lg mb-2">Add widget</h2>
        <input
          className="border w-full mb-2 p-1"
          placeholder="Search"
          value={query}
          onChange={e => setQuery(e.target.value)}
        />
        <ul className="max-h-40 overflow-auto">
          {list.map(w => (
            <li key={w.id}>
              <button
                className="w-full text-left py-1 hover:bg-gray-100"
                onClick={() => onAdd(w.id)}
              >
                {w.title}
              </button>
            </li>
          ))}
          {list.length === 0 && (
            <li className="text-sm text-gray-500">No widgets</li>
          )}
        </ul>
        <div className="text-right mt-2">
          <button className="px-2 py-1 border rounded" onClick={onClose}>
            Close
          </button>
        </div>
      </div>
    </div>
  );
}

