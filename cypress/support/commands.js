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

// Basic drag and drop helper. Triggers the HTML5 drag events on the
// source and target elements to simulate a drag operation.
Cypress.Commands.add('drag', (sourceSelector, targetSelector) => {
  const dataTransfer = new DataTransfer();

  cy.get(sourceSelector)
    .trigger('dragstart', { dataTransfer });

  cy.get(targetSelector)
    .trigger('dragover', { dataTransfer })
    .trigger('drop', { dataTransfer });

  cy.get(sourceSelector).trigger('dragend', { dataTransfer });
});
