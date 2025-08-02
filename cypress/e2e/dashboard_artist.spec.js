describe('Artist Dashboard', () => {
  const widgets = ['artist_feed_publisher', 'artist_artwork_manager'];

  it('renders all artist widgets', () => {
    cy.login('artist', 'password');
    cy.visit('/dashboard');
    widgets.forEach((id) => {
      cy.get(`[data-widget-id="${id}"]`, { timeout: 15000 }).should('exist');
    });
  });
});
