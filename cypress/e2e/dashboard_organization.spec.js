describe('Organization Dashboard', () => {
  const widgets = [
    'org_event_overview',
    'org_insights',
    'org_widget_sharing',
    'webhooks',
    'artpulse_analytics_widget',
    'sponsor_display',
  ];

  beforeEach(() => {
    cy.login('organization');
    cy.visit('/dashboard');
  });

  it('renders all organization widgets', () => {
    widgets.forEach((id) => {
      cy.get(`[data-widget-id="${id}"]`).should('exist');
    });
  });

  describe('Org Quick Add Event widget', () => {
    it('renders the Quick Add Event button', () => {
      cy.get('#ap-add-event-btn').should('exist');
    });

    it('opens the modal and mounts the form', () => {
      cy.get('#ap-add-event-btn').click();
      cy.get('#ap-org-modal').should('exist').and(($el) => {
        expect($el.attr('hidden')).to.be.undefined;
      });
      cy.get('#ap-org-event-form').should('exist');
    });
  });
});
