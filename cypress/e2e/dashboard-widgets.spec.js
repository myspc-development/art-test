describe('Dashboard Widgets', () => {
    it('shows dashboard container when logged in', () => {
        cy.login('artist');
        cy.visit('/dashboard');
        cy.get('#ap-user-dashboard').should('exist');
    });
});
