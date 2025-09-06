import React from 'react';
import { render, screen, fireEvent, act, waitFor } from '@testing-library/react';
import type { DragEndEvent } from '@dnd-kit/core';

let triggerDrag: (e: DragEndEvent) => void = () => {};
jest.mock('@dnd-kit/core', () => {
  const actual = jest.requireActual('@dnd-kit/core');
  return {
    ...actual,
    DndContext: ({ onDragEnd, children }: any) => {
      triggerDrag = onDragEnd;
      return <div>{children}</div>;
    },
  };
});

import RoleDashboard from '../RoleDashboard';

describe('RoleDashboard interactions', () => {
  beforeEach(() => {
    jest.useFakeTimers();
    (window as any).apDashboardData = {
      restBase: '/',
      nonce: '',
      seenDashboardV2: true,
    };
    window.localStorage.clear();
    global.fetch = jest.fn(() =>
      Promise.resolve({ ok: true, json: () => Promise.resolve({ layout: [] }) })
    ) as any;
  });

  afterEach(() => {
    jest.useRealTimers();
  });

  it('allows removing, adding, resetting widgets and announces drag moves', () => {
    render(<RoleDashboard role="member" initialEdit={true} />);

    // Trigger drag to cover handleDragEnd/announce
    act(() => {
      triggerDrag({ active: { id: 'tasks' }, over: { id: 'inbox' } } as any);
    });
    const liveMsg = screen.getByText(/moved tasks to position 3/i);
    expect(liveMsg).toBeInTheDocument();
    jest.runAllTimers();
    expect(screen.queryByText(/moved tasks to position 3/i)).toBeNull();

    // Initially three widgets
    expect(screen.getAllByLabelText(/remove/i)).toHaveLength(3);

    // Remove first widget
    fireEvent.click(screen.getAllByLabelText(/remove/i)[0]);
    expect(screen.getAllByLabelText(/remove/i)).toHaveLength(2);

    // Add calendar widget via dialog
    fireEvent.click(screen.getByText(/add widget/i));
    fireEvent.click(screen.getByText(/calendar/i));
    fireEvent.click(screen.getByText(/close/i));
    expect(screen.getAllByLabelText(/remove/i)).toHaveLength(3);

    // Reset restores presets
    fireEvent.click(screen.getByText(/reset/i));
    expect(screen.getAllByLabelText(/remove/i)).toHaveLength(3);
    expect(screen.getByText(/no upcoming events/i)).toBeInTheDocument();
  });

  it('reorders widgets and ignores drags without target', async () => {
    render(<RoleDashboard role="member" initialEdit={true} />);
    act(() => {
      triggerDrag({ active: { id: 'tasks' }, over: { id: 'inbox' } } as any);
    });
    await waitFor(() =>
      expect(
        JSON.parse(
          window.localStorage.getItem('ap.dashboard.layout.v1:member') as string
        )
      ).toEqual(['upcoming', 'inbox', 'tasks'])
    );

    act(() => {
      triggerDrag({ active: { id: 'tasks' }, over: undefined } as any);
    });
    expect(
      JSON.parse(
        window.localStorage.getItem('ap.dashboard.layout.v1:member') as string
      )
    ).toEqual(['upcoming', 'inbox', 'tasks']);

    act(() => {
      triggerDrag({ active: { id: 'tasks' }, over: { id: 'tasks' } } as any);
    });
    expect(
      JSON.parse(
        window.localStorage.getItem('ap.dashboard.layout.v1:member') as string
      )
    ).toEqual(['upcoming', 'inbox', 'tasks']);
  });
});
