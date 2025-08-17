import React from 'react';

export default function Inbox() {
  return (
    <div className="space-y-2">
      <div className="border-b pb-2">
        <p className="font-medium">Alex</p>
        <p className="text-sm text-gray-600">Thanks for sharing!</p>
      </div>
      <div>
        <p className="font-medium">Jordan</p>
        <p className="text-sm text-gray-600">Can we meet tomorrow?</p>
      </div>
    </div>
  );
}

