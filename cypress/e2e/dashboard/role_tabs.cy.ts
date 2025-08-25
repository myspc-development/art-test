import 'cypress-axe';

describe('Role Dashboard role tabs', () => {
  beforeEach(() => {
    cy.login('admin');
    cy.visit('/wp-admin/admin.php?page=dashboard-role', {
      onBeforeLoad(win) {
        win.localStorage.removeItem('ap:lastRole');
      }
    });
    cy.injectAxe();
  });

  it('supports ARIA tabs with keyboard navigation and persistence', () => {
    cy.checkA11y('#ap-dashboard-root');

    cy.get('.ap-role-tabs').should('have.attr', 'role', 'tablist');
    cy.get('.ap-role-tab').should('have.attr', 'role', 'tab');

    cy.contains('.ap-role-tab', 'Member').as('member');
    cy.contains('.ap-role-tab', 'Artist').as('artist');
    cy.contains('.ap-role-tab', 'Organization').as('org');

    // Arrow keys cycle through tabs
    cy.get('@member').focus().type('{rightarrow}');
    cy.focused().should('have.attr', 'data-role', 'artist');
    cy.focused().type('{end}');
    cy.focused().should('have.attr', 'data-role', 'organization');
    cy.focused().type('{home}');
    cy.focused().should('have.attr', 'data-role', 'member');

    // Query parameter and localStorage persistence
    cy.get('@artist').click();
    cy.url().should('include', 'role=artist');
    cy.reload();
    cy.contains('.ap-role-tab', 'Artist').should('have.attr', 'aria-selected', 'true');
  });
});
