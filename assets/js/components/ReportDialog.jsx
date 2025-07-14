import React, { useState, useRef, useEffect } from 'react';

const reasons = ['Spam', 'Abuse', 'Off-topic', 'Other'];

export default function ReportDialog({ onClose, onSubmit }) {
  const [reason, setReason] = useState(reasons[0]);
  const [details, setDetails] = useState('');
  const dialogRef = useRef(null);

  useEffect(() => {
    const node = dialogRef.current;
    if (!node) return;
    const focusable = node.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    first?.focus();
    const handleKeyDown = e => {
      if (e.key === 'Escape') {
        onClose();
      }
      if (e.key === 'Tab') {
        if (e.shiftKey) {
          if (document.activeElement === first) {
            e.preventDefault();
            last.focus();
          }
        } else {
          if (document.activeElement === last) {
            e.preventDefault();
            first.focus();
          }
        }
      }
    };
    document.addEventListener('keydown', handleKeyDown);
    return () => document.removeEventListener('keydown', handleKeyDown);
  }, [onClose]);

  const handleSubmit = e => {
    e.preventDefault();
    if (onSubmit) onSubmit(reason, details);
    onClose();
  };
  return (
    <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div
        ref={dialogRef}
        className="bg-white p-4 rounded w-80" role="dialog" aria-modal="true"
      >
        <h3 className="text-lg font-semibold mb-2">Report</h3>
        <form onSubmit={handleSubmit} className="space-y-3">
          <select
            className="w-full border p-2 rounded"
            value={reason}
            onChange={e => setReason(e.target.value)}
          >
            {reasons.map(r => (
              <option key={r}>{r}</option>
            ))}
          </select>
          <textarea
            className="w-full border rounded p-2"
            placeholder="Details (optional)"
            value={details}
            onChange={e => setDetails(e.target.value)}
            rows={3}
          />
          <div className="flex justify-end gap-2">
            <button type="button" className="px-3 py-1" onClick={onClose}>Cancel</button>
            <button type="submit" className="bg-red-600 text-white px-3 py-1 rounded">Submit</button>
          </div>
        </form>
      </div>
    </div>
  );
}
