describe('Member Dashboard', () => {
  const widgets = [
    'news_feed',
    'my_favorites',
    'widget_events',
    'rsvp_button',
  ];

  it('renders all member widgets', () => {
    cy.login('member');
    cy.visit('/dashboard');
    widgets.forEach((id) => {
      cy.get(`[data-widget-id="${id}"]`).should('exist');
    });
  });
});

