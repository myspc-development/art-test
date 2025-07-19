import React from 'react';
import GridLayout from 'react-grid-layout';

export default function DashboardCanvas({ layout = [], onLayoutChange, children }) {
  return (
    <div className="ap-dashboard-canvas">
      <GridLayout
        className="layout"
        layout={layout}
        cols={12}
        rowHeight={30}
        width={800}
        onLayoutChange={onLayoutChange}
      >
        {children}
      </GridLayout>
    </div>
  );
}
