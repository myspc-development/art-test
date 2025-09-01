describe('Dashboard Permissions', () => {
    it('redirects unauthenticated users to login', () => {
        cy.visit('/dashboard');
        cy.url().should('include', '/wp-login.php');
    });

    it('shows message when user lacks dashboard capability', () => {
        cy.login(Cypress.env('PUBLIC_USER'));
        cy.visit('/dashboard');
        cy.contains('Please log in to view your dashboard.');
    });
});
