describe('Artist Dashboard', () => {
  const widgets = [
    'artist_artwork_manager',
    'artist_feed_publisher',
    'artist_inbox_preview',
    'revenue_summary',
    'embed_tool',
    'collab_requests',
  ];

  it('renders all artist widgets', () => {
    cy.login(Cypress.env('ARTIST_USER'));
    cy.visit('/dashboard');
    widgets.forEach((id) => {
      cy.get(`[data-widget-id="${id}"]`).should('exist');
    });
  });
});

