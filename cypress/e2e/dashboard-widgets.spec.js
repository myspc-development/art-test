describe('Dashboard Widgets', () => {
    it('shows dashboard container when logged in', () => {
        cy.login(Cypress.env('ARTIST_USER'));
        cy.visit('/dashboard');
        cy.get('#ap-dashboard-root').should('exist');
    });

    it('displays artist widgets', () => {
        cy.login(Cypress.env('ARTIST_USER'));
        cy.visit('/dashboard');
        cy.get('#messages').should('exist');
        cy.get('#favorites').should('exist');
    });

    it('hides widgets for users without dashboard access', () => {
        cy.login(Cypress.env('PUBLIC_USER'));
        cy.visit('/dashboard');
        cy.get('#messages').should('not.exist');
    });
});
