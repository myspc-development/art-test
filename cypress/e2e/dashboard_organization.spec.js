describe('Organization Dashboard', () => {
  const widgets = [
    'org_event_overview',
    'org_insights',
    'org_widget_sharing',
    'webhooks',
    'artpulse_analytics_widget',
    'sponsor_display',
  ];

  it('renders all organization widgets', () => {
    cy.login('organization');
    cy.visit('/dashboard');
    widgets.forEach((id) => {
      cy.get(`[data-widget-id="${id}"]`).should('exist');
    });
  });

  it('shows Quick Add Event widget', () => {
    cy.login('organization');
    cy.visit('/dashboard');
    cy.get('#ap-add-event-btn').should('exist');
  });
});

