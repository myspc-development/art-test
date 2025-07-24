import React from 'react';
const { __ } = wp.i18n;

export default function DonateButton({ url }) {
  if (!url) return null;
  return (
    <a className="ap-donate-btn" href={url} target="_blank" rel="noopener noreferrer">
      {__('❤️ Support this Artist', 'artpulse')}
    </a>
  );
}
