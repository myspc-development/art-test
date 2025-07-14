import React from 'react';

export default function AnalyticsCard({ label, value }) {
  return (
    <div className="border rounded p-4 text-center">
      <div className="text-2xl font-bold">{value ?? 0}</div>
      <div className="text-sm text-gray-500">{label}</div>
    </div>
  );
}
