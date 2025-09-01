describe('Dashboard Layout', () => {
  ['member', Cypress.env('ARTIST_USER'), 'organization'].forEach(role => {
    it(`loads widgets for ${role}`, () => {
      cy.login(role);
      cy.visit('/dashboard');
      cy.get('.ap-dashboard-widget').should('exist');
    });
  });

  it('preserves layout after role upgrade', () => {
    cy.login('member');
    cy.visit('/dashboard');
    cy.get('#favorites').should('exist');
    cy.upgradeRole('artist');
    cy.visit('/dashboard');
    cy.get('#favorites').should('exist');
  });

  it('allows drag and drop between tabs', () => {
    cy.login(Cypress.env('ARTIST_USER'));
    cy.visit('/dashboard');
    // Assumes custom command for drag
    cy.drag('#messages', '#favorites');
  });
});
