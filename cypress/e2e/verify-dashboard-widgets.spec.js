describe('Role Dashboard Widget Rendering', () => {
  const roles = ['member', 'artist', 'organization'];

  beforeEach(() => {
    // Assume loginAsAdmin logs in and stores session
    cy.loginAsAdmin(); 
  });

  roles.forEach((role) => {
    it(`renders widgets for ${role}`, () => {
      const url = `/wp-admin/admin.php?page=dashboard-role&ap_preview_role=${role}`;
      cy.visit(url);

      cy.get('.ap-dashboard-grid', { timeout: 10000 }).should('exist');

      cy.get('.ap-widget-card').then((widgets) => {
        const widgetCount = widgets.length;
        cy.log(`Found ${widgetCount} widgets for role: ${role}`);
        expect(widgetCount).to.be.greaterThan(0);
      });
    });
  });
});
