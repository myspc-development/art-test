describe('Tip Flow', () => {
    it('user tips an artist and sees confirmation', () => {
        cy.login('public_user');
        cy.visit('/artists/sample-artist/');
        cy.get('[data-cy=tip-button]').click();
        cy.get('[data-cy=tip-amount]').clear().type('5');
        cy.get('[data-cy=submit-tip]').click();
        cy.contains('Thanks for the tip');
    });
});
