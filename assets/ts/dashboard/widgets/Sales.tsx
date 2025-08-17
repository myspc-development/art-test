import React from 'react';
import { LineChart, Line, ResponsiveContainer, XAxis, YAxis } from 'recharts';

const data = [
  { week: '1', sales: 4 },
  { week: '2', sales: 6 },
  { week: '3', sales: 3 },
  { week: '4', sales: 8 }
];

export default function Sales() {
  return (
    <div className="w-full h-40">
      <ResponsiveContainer width="100%" height="100%">
        <LineChart data={data}>
          <XAxis dataKey="week" hide />
          <YAxis hide />
          <Line type="monotone" dataKey="sales" stroke="#4b5563" strokeWidth={2} dot={false} />
        </LineChart>
      </ResponsiveContainer>
    </div>
  );
}

