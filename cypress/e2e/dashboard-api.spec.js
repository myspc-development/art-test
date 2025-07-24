describe('Dashboard API', () => {
  it('fetches widgets for member role', () => {
    cy.request('/wp-json/artpulse/v1/dashboard-widgets?role=member').its('status').should('eq',200);
  });

  it('RSVP endpoint returns success', () => {
    cy.request('POST', '/wp-json/artpulse/v1/rsvp', {event_id:1}).its('status').should('eq',200);
  });

  it('event chat endpoint', () => {
    cy.request('/wp-json/artpulse/v1/event/1/chat').its('status').should('eq',200);
  });
});
