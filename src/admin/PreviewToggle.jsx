import React from 'react';

export default function PreviewToggle({ preview, onToggle }) {
  return (
    <button type="button" onClick={() => onToggle(!preview)}>
      {preview ? 'Edit Mode' : 'Preview'}
    </button>
  );
}
