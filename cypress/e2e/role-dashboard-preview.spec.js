describe('Role Dashboard Preview', () => {
  const roles = {
    member: ['membership', 'my_favorites', 'event_chat'],
    artist: ['artist_spotlight', 'artist_inbox_preview'],
    organization: ['org_team_roster', 'event_chat'],
  };

  beforeEach(() => {
    // login as administrator to access dashboard preview
    cy.login('admin');
  });

  Object.entries(roles).forEach(([role, expectedWidgets]) => {
    it(`shows correct widgets for role: ${role}`, () => {
      cy.visit(`/wp-admin/admin.php?page=dashboard-role&ap_preview_role=${role}`);
      expectedWidgets.forEach((widgetId) => {
        cy.get(`.postbox[data-widget-id="${widgetId}"]`).should('exist');
      });
    });
  });
});
