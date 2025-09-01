describe('Messaging Flow', () => {
    it('user sends a message and sees it in inbox', () => {
        // Implementation assumes helper commands for login and seed data exist
        cy.login(Cypress.env('PUBLIC_USER'));
        cy.visit('/messages');
        cy.get('[data-cy=new-message]').click();
        cy.get('[data-cy=recipient-input]').type('verified_artist');
        cy.get('[data-cy=message-input]').type('Hello there');
        cy.get('[data-cy=send-message]').click();
        cy.contains('Hello there');
    });
});
