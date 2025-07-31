describe('Member Dashboard Widgets', () => {
  const login = () => {
    cy.visit('/wp-login.php');
    cy.get('#user_login').type(Cypress.env('MEMBER_USER'));
    cy.get('#user_pass').type(Cypress.env('MEMBER_PASS'), { log: false });
    cy.get('#wp-submit').click();
  };

  const widgets = [
    'membership',
    'upgrade',
    'nearby_events_map',
    'widget_favorites',
    'widget_events',
    'activity_feed',
    'account-tools',
    'notifications',
    'rsvp_button',
    'event_chat',
    'share_this_event',
    'qa_checklist',
  ];

  it('shows default widgets for members', () => {
    login();
    cy.visit('/dashboard');
    cy.contains('Org Dashboard').should('not.exist');
    cy.contains('Org Users').should('not.exist');
    widgets.forEach((id) => {
      cy.get(`[data-widget="${id}"]`, { timeout: 15000 }).should('be.visible');
    });
  });
});
