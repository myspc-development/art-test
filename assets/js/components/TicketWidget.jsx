import React, { useEffect, useState } from 'react';
const { __ } = wp.i18n;

export default function TicketWidget({ eventId }) {
  const [tiers, setTiers] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch(`${APTickets.apiRoot}artpulse/v1/event/${eventId}/tickets`)
      .then(r => r.json())
      .then(d => { setTiers(d.tiers || []); setLoading(false); });
  }, [eventId]);

  const buy = (tier) => {
    fetch(`${APTickets.apiRoot}artpulse/v1/event/${eventId}/buy-ticket`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': APTickets.nonce
      },
      body: JSON.stringify({ ticket_id: tier.id })
    }).then(() => alert(__('Purchased!', 'artpulse')));
  };

  if (loading) return <p>{__('Loading tickets...', 'artpulse')}</p>;

  return (
    <div className="ap-tickets" data-event-id={eventId}>
      <ul className="ap-ticket-list">
        {tiers.map(tier => (
          <li key={tier.id} className="ap-ticket-tier">
            <span>{tier.name} - {tier.price}</span>
            <button
              onClick={() => buy(tier)}
              aria-label={`${__('Buy', 'artpulse')} ${tier.name}`}
            >
              {__('Buy', 'artpulse')}
            </button>
          </li>
        ))}
      </ul>
    </div>
  );
}
