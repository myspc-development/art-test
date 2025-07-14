import React, { useEffect, useRef } from 'react';
import Chart from 'chart.js/auto';

export default function ActivityGraph({ data = [] }) {
  const canvasRef = useRef(null);

  useEffect(() => {
    if (!canvasRef.current || !data.length) return;
    const chart = new Chart(canvasRef.current.getContext('2d'), {
      type: 'line',
      data: {
        labels: data.map(d => d.day),
        datasets: [{
          label: 'Count',
          data: data.map(d => d.c),
          borderColor: '#2563eb',
          backgroundColor: 'rgba(37,99,235,0.2)',
          fill: false
        }]
      },
      options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
    return () => chart.destroy();
  }, [data]);

  return <canvas ref={canvasRef}></canvas>;
}
