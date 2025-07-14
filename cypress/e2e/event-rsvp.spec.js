describe('Event RSVP', () => {
    /**
     * Basic end‑to‑end test covering the RSVP interaction.
     * Assumes a test event exists at /events/sample-event/.
     */
    it('allows a user to RSVP to an event', () => {
        cy.visit('/events/sample-event/');

        // Capture the current RSVP count.
        cy.get('.ap-rsvp-count')
            .invoke('text')
            .then((text) => {
                const initial = parseInt(text, 10) || 0;

                // Click the RSVP button.
                cy.get('.ap-rsvp-btn').click();

                // The page redirects back to the event after submitting.
                cy.location('pathname').should('include', '/events/sample-event/');

                // Button should show rsvp state.
                cy.get('.ap-rsvp-btn').should('have.class', 'ap-rsvped');

                // RSVP count should increment by one.
                cy.get('.ap-rsvp-count').should('have.text', `${initial + 1}`);
            });
    });
});
