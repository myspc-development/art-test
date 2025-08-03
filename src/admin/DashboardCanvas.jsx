import React from 'react';
import GridLayout, { WidthProvider } from 'react-grid-layout';

const ResponsiveGridLayout = WidthProvider(GridLayout);

export default function DashboardCanvas({ layout = [], onLayoutChange, children }) {
  return (
    <div className="ap-dashboard-canvas">
      <ResponsiveGridLayout
        className="layout"
        layout={layout}
        cols={12}
        rowHeight={30}
        onLayoutChange={onLayoutChange}
      >
        {children}
      </ResponsiveGridLayout>
    </div>
  );
}
