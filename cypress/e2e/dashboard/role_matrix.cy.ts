import 'cypress-axe';

describe('Role matrix', () => {
  beforeEach(() => {
    cy.login('admin');
    cy.intercept('POST', '**/roles/batch**', { statusCode: 200, body: {} }).as('saveMatrix');
    cy.visit('/wp-admin/admin.php?page=role-matrix');
    cy.injectAxe();
  });

  it('handles filtering, bulk toggles, dirty state, and navigation guard', () => {
    cy.checkA11y('#ap-dashboard-root');

    cy.get('.ap-role-matrix thead th').first().should('have.css', 'position', 'sticky');
    cy.get('.ap-role-matrix tbody th[scope="row"]').first().should('have.css', 'position', 'sticky');

    cy.get('.ap-role-matrix tbody tr').should('have.length.greaterThan', 1);
    cy.get('.ap-role-matrix tbody tr').eq(1).find('.ap-role-toggle').first().invoke('prop', 'checked').as('hiddenBefore');

    cy.get('.ap-role-matrix tbody tr').first().find('th').invoke('text').then((txt) => {
      cy.get('#ap-role-filter').type(txt.trim().substring(0, 2));
    });
    cy.get('.ap-role-matrix tbody tr:visible').should('have.length', 1);

    cy.get('.ap-col-toggle').first().click();
    cy.get('.ap-role-matrix tbody tr:visible .ap-role-toggle').each(($cb) => {
      cy.wrap($cb).should('have.prop', 'checked');
    });
    cy.get('.ap-role-matrix tbody tr').eq(1).find('.ap-role-toggle').first().invoke('prop', 'checked').then(function (val) {
      expect(val).to.eq(this.hiddenBefore);
    });

    cy.get('#ap-role-save').should('not.be.disabled');
    cy.get('#ap-unsaved-chip').should('not.be.hidden');
    cy.get('#ap-role-save').click();
    cy.wait('@saveMatrix');
    cy.get('#ap-role-toast').should('contain', 'Saved');
    cy.get('#ap-role-save').should('be.disabled');
    cy.get('#ap-unsaved-chip').should('be.hidden');

    cy.get('.ap-role-matrix .ap-role-toggle').first().click();
    cy.window().then((win) => {
      const e = new win.Event('beforeunload', { cancelable: true });
      win.dispatchEvent(e);
      expect(e.defaultPrevented).to.be.true;
    });
  });
});
