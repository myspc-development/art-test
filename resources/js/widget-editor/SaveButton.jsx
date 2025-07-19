import React from 'react';

export default function SaveButton({ onClick, disabled }) {
  return (
    <button type="button" onClick={onClick} disabled={disabled}>
      💾 Save
    </button>
  );
}
