describe('Member Dashboard', () => {
  const widgets = ['activity_feed', 'news_feed', 'my_favorites'];

  it('renders all member widgets', () => {
    cy.login('member', 'password');
    cy.visit('/dashboard');
    widgets.forEach((id) => {
      cy.get(`[data-widget-id="${id}"]`, { timeout: 15000 }).should('exist');
    });
  });
});
