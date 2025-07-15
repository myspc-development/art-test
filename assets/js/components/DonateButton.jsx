import React from 'react';

export default function DonateButton({ url }) {
  if (!url) return null;
  return (
    <a className="ap-donate-btn" href={url} target="_blank" rel="noopener noreferrer">
      ❤️ Support this Artist
    </a>
  );
}
