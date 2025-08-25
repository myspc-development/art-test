import 'cypress-axe';

describe('Dashboard local navigation', () => {
  beforeEach(() => {
    cy.login('admin');
    cy.visit('/wp-admin/admin.php?page=dashboard-role&role=member');
    cy.injectAxe();
  });

  it('sticks to top, highlights active section, and preserves query/hash', () => {
    cy.checkA11y('#ap-dashboard-root');

    cy.get('.ap-local-nav').should('have.css', 'position', 'sticky');

    cy.get('#activity').scrollIntoView();
    cy.get('.ap-local-nav a[href="#activity"]').should('have.class', 'is-active').and('have.attr', 'aria-current', 'true');
    cy.url().should('include', '?role=member');
    cy.url().should('include', '#activity');
  });
});
