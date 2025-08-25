import 'cypress-axe';

describe('Dashboard skeleton and empty states', () => {
  beforeEach(() => {
    cy.login('admin');
    cy.visit('/wp-admin/admin.php?page=dashboard-role', {
      onBeforeLoad(win) {
        let call = 0;
        cy.stub(win, 'fetch').callsFake((url) => {
          call += 1;
          const body = call === 1 ? '<div class="loaded">Loaded</div>' : '';
          return new win.Promise((resolve) => {
            setTimeout(() => resolve(new win.Response(body, { status: 200, headers: { 'Content-Type': 'text/html' } })), 50);
          });
        }).as('fetch');
      }
    });
    cy.injectAxe();
  });

  it('shows skeletons until content loads and announces empty states', () => {
    cy.checkA11y('#ap-dashboard-root');

    cy.get('.ap-card[data-endpoint]').should('have.length.at.least', 2);
    cy.get('.ap-card[data-endpoint]').eq(0).as('first');
    cy.get('.ap-card[data-endpoint]').eq(1).as('second');

    cy.get('@first').should('have.class', 'is-loading');
    cy.get('@first').find('.ap-skeleton').should('be.visible');
    cy.get('@first').should('not.have.class', 'is-loading');
    cy.get('@first').find('.loaded').should('exist');

    cy.get('@second').should('not.have.class', 'is-loading');
    cy.get('@second').find('.ap-empty-state').should('have.attr', 'role', 'status').and('have.attr', 'aria-live', 'polite');
  });
});
