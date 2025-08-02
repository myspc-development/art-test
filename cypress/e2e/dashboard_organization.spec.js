describe('Organization Dashboard', () => {
  const widgets = ['org_event_overview', 'org_insights', 'webhooks'];

  it('renders all organization widgets', () => {
    cy.login('organization', 'password');
    cy.visit('/dashboard');
    widgets.forEach((id) => {
      cy.get(`[data-widget-id="${id}"]`, { timeout: 15000 }).should('exist');
    });
  });
});
