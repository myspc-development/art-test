import React from 'react';
import { render } from '@testing-library/react';
import '@testing-library/jest-dom';
import CalendarHeatmap from '../CalendarHeatmap';

describe('CalendarHeatmap', () => {
  it('renders provided cells with correct colors', () => {
    const { container } = render(<CalendarHeatmap cells={[0, 1, 2, 3]} />);
    const cells = container.querySelectorAll('.grid > div');
    expect(cells).toHaveLength(4);
    expect(cells[0]).toHaveClass('bg-gray-200');
    expect(cells[1]).toHaveClass('bg-green-200');
    expect(cells[2]).toHaveClass('bg-green-400');
    expect(cells[3]).toHaveClass('bg-green-600');
  });

  it('generates deterministic cells from a seed', () => {
    const { container: c1 } = render(<CalendarHeatmap seed={123} />);
    const { container: c2 } = render(<CalendarHeatmap seed={123} />);
    expect(c1.innerHTML).toBe(c2.innerHTML);
  });
});
