import React from 'react';

interface CalendarHeatmapProps {
  /**
   * Pre-generated cell values. If not provided a pseudo-random set
   * of 35 cells will be generated.
   */
  cells?: number[];
  /**
   * Optional seed for the pseudo-random generator used when `cells`
   * are not supplied. Using the same seed produces the same output.
   */
  seed?: number;
}

function seededRandom(seed: number) {
  return () => {
    seed = (seed * 1664525 + 1013904223) % 4294967296;
    return seed / 4294967296;
  };
}

function generateCells(length = 35, seed = Date.now()) {
  const rand = seededRandom(seed);
  return Array.from({ length }).map(() => Math.floor(rand() * 4));
}

export default function CalendarHeatmap({ cells, seed }: CalendarHeatmapProps) {
  const cellData = React.useMemo(() => cells ?? generateCells(35, seed), [cells, seed]);
  const colors = ['bg-gray-200', 'bg-green-200', 'bg-green-400', 'bg-green-600'];
  return (
    <div className="grid grid-cols-7 gap-1">
      {cellData.map((lvl, i) => (
        <div key={i} className={`h-4 w-4 rounded ${colors[lvl]}`}></div>
      ))}
    </div>
  );
}

