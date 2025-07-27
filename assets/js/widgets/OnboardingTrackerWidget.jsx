import React from 'react';
import { createRoot } from 'react-dom/client';
const { __ } = wp.i18n;

export function OnboardingTrackerWidget() {
  const items = [
    __('Complete Profile', 'artpulse'),
    __('Upload First Artwork', 'artpulse'),
    __('Host an Event', 'artpulse'),
    __('Get 10 Followers', 'artpulse')
  ];
  return (
    <ul className="ap-onboarding-tracker">
      {items.map((i, idx) => (
        <li key={idx}>{i}</li>
      ))}
    </ul>
  );
}

export default function initOnboardingTrackerWidget(el) {
  const root = createRoot(el);
  root.render(<OnboardingTrackerWidget />);
}
