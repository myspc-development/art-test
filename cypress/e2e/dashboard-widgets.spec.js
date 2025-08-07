describe('Dashboard Widgets', () => {
    it('shows dashboard container when logged in', () => {
        cy.login('artist');
        cy.visit('/dashboard');
        cy.get('#ap-dashboard-root').should('exist');
    });

    it('displays artist widgets', () => {
        cy.login('artist');
        cy.visit('/dashboard');
        cy.get('#messages').should('exist');
        cy.get('#favorites').should('exist');
    });

    it('hides widgets for users without dashboard access', () => {
        cy.login('public_user');
        cy.visit('/dashboard');
        cy.get('#messages').should('not.exist');
    });
});
