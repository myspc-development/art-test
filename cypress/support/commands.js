Cypress.Commands.add('login', (username, password = 'password') => {
  cy.request('POST', '/wp-json/jwt-auth/v1/token', {
    username,
    password
  }).then((resp) => {
    window.localStorage.setItem('auth_token', resp.body.token);
  });
});
