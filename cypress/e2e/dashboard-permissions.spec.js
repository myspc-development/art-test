describe('Dashboard Permissions', () => {
    it('redirects unauthenticated users to login', () => {
        cy.visit('/dashboard');
        cy.url().should('include', '/wp-login.php');
    });
});
