import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';

export function NearbyEventsMapWidget({ apiRoot, nonce, lat, lng }) {
  const [events, setEvents] = useState([]);

  useEffect(() => {
    if (!lat || !lng) {
      setEvents([]);
      return;
    }
    fetch(`${apiRoot}artpulse/v1/events/nearby?lat=${lat}&lng=${lng}`, {
      headers: { 'X-WP-Nonce': nonce },
    })
      .then(r => (r.ok ? r.json() : Promise.resolve([])))
      .then(data => (Array.isArray(data) ? data : []))
      .then(setEvents)
      .catch(() => setEvents([]));
  }, [lat, lng, apiRoot, nonce]);

  return (
    <div className="ap-nearby-events-widget">
      <ul>
        {events.map(ev => (
          <li key={ev.id}>
            <a href={ev.link}>{ev.title}</a> ({ev.distance} km)
          </li>
        ))}
      </ul>
    </div>
  );
}

export default function initNearbyEventsMapWidget(el) {
  const root = createRoot(el);
  const { lat, lng, apiRoot, nonce } = el.dataset;
  root.render(
    <NearbyEventsMapWidget
      apiRoot={apiRoot}
      nonce={nonce}
      lat={lat}
      lng={lng}
    />
  );
}
