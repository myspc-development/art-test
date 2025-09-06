import React from 'react';
import { render } from '@testing-library/react';
import '@testing-library/jest-dom';
import CalendarHeatmap from '../CalendarHeatmap';

describe('CalendarHeatmap', () => {
  it('renders 35 cells with consistent color', () => {
    const randomSpy = jest.spyOn(Math, 'random').mockReturnValue(0.1);
    const { container } = render(<CalendarHeatmap />);
    const cells = container.querySelectorAll('.grid > div');
    expect(cells).toHaveLength(35);
    cells.forEach(cell => expect(cell).toHaveClass('bg-gray-200'));
    randomSpy.mockRestore();
  });
});
