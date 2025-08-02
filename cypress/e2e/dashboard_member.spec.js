describe('Member Dashboard', () => {
  const widgets = [
    'activity_feed',
    'news_feed',
    'my_favorites',
    'qa_checklist',
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

