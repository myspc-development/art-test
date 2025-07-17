import React, { useEffect, useState } from 'react';

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
    }).then(() => alert('Purchased!'));
  };

  if (loading) return <p>Loading tickets...</p>;

  return (
    <div className="ap-tickets" data-event-id={eventId}>
      <ul className="ap-ticket-list">
        {tiers.map(tier => (
          <li key={tier.id} className="ap-ticket-tier">
            <span>{tier.name} - {tier.price}</span>
            <button onClick={() => buy(tier)} aria-label={`Buy ${tier.name}`}>Buy</button>
          </li>
        ))}
      </ul>
    </div>
  );
}
