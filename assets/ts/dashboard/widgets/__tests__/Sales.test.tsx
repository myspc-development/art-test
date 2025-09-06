import React from 'react';
import { render } from '@testing-library/react';
import '@testing-library/jest-dom';
import Sales from '../Sales';
import * as Recharts from 'recharts';

describe('Sales', () => {
  it('passes data to LineChart', () => {
    const spy = jest.spyOn(Recharts, 'LineChart');
    render(<Sales />);
    expect(spy).toHaveBeenCalled();
    const props = spy.mock.calls[0][0];
    expect(props.data).toHaveLength(4);
    spy.mockRestore();
  });
});
