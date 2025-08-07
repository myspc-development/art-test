Cypress.Commands.add('login', (username, password = 'password') => {
  cy.request('POST', '/wp-json/jwt-auth/v1/token', {
    username,
    password
  }).then((resp) => {
    const token = resp.body.token;
    expect(token).to.exist;

    cy.window().then((win) => {
      win.localStorage.setItem('auth_token', token);
    });
  });
});
