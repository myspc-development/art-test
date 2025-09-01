describe('Dashboard Quick Add Event button', () => {
  const roles = [Cypress.env('ARTIST_USER'), 'organization'];

  roles.forEach((role) => {
    describe(`${role} flow`, () => {
      beforeEach(() => {
        cy.login(role);
        cy.visit('/dashboard');
      });

      it('shows Add New Event control', () => {
        cy.get('#ap-add-event-btn').should('exist').click();

        if (role === Cypress.env('ARTIST_USER')) {
          cy.get('#ap-add-event-form').should('be.visible');

          // mock network request to ensure submit triggers
          cy.intercept('POST', '**/artpulse/v1/submissions', {
            statusCode: 200,
            body: {}
          }).as('submitEvent');

          cy.get('#ap-add-event-form').find('input[name="title"]').type('Cypress Test Event');
          cy.get('#ap-add-event-form').find('button[type="submit"]').click();
          cy.wait('@submitEvent');
        } else {
          cy.get('#ap-org-modal').should('exist').and(($el) => {
            expect($el.attr('hidden')).to.be.undefined;
          });
          cy.get('#ap-org-event-form').should('exist');
        }
      });
    });
  });
});
