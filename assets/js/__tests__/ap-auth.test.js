import { beforeEach, expect, test, afterEach, describe } from '@jest/globals';

const flushPromises = () => new Promise((resolve) => setTimeout(resolve, 0));

describe('ap-auth login', () => {
  let originalLocation;
  beforeEach(async () => {
    jest.resetModules();
    document.body.innerHTML = `
      <div id="ap-login-message"></div>
      <form id="ap-login-form">
        <input id="ap_login_username" name="username" required />
        <input id="ap_login_password" name="password" required />
        <input type="checkbox" id="ap_login_remember" name="remember" />
        <button type="submit">Login</button>
      </form>
    `;
    global.APLogin = { ajaxUrl: '/login', nonce: '123', dashboardUrl: '/dash' };
    global.fetch = jest.fn();
    await import('../ap-auth.js');
    document.dispatchEvent(new Event('DOMContentLoaded'));
    originalLocation = window.location;
    delete window.location;
    window.location = { href: 'http://example.com' };
  });

  test('includes remember in payload when checked', async () => {
    document.getElementById('ap_login_remember').checked = true;
    fetch.mockResolvedValue({ ok: true, json: async () => ({ success: false }) });
    document.getElementById('ap-login-form').dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    await flushPromises();
    const body = fetch.mock.calls[0][1].body;
    const entries = Array.from(body.entries());
    expect(entries).toEqual(expect.arrayContaining([
      ['remember', '1'],
      ['action', 'ap_do_login'],
      ['nonce', '123'],
    ]));
  });

  test('renders server error and focuses first invalid field', async () => {
    fetch.mockResolvedValue({
      ok: true,
      json: async () => ({ success: false, data: { message: 'Invalid', invalid: ['username'] } }),
    });
    document.getElementById('ap-login-form').dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    await flushPromises();
    expect(document.getElementById('ap-login-message').textContent).toBe('Invalid');
    expect(document.activeElement).toBe(document.getElementById('ap_login_username'));
  });

  test('focuses first invalid field when server provides no invalid list', async () => {
    fetch.mockResolvedValue({
      ok: true,
      json: async () => ({ success: false, data: { message: 'Invalid' } }),
    });
    document.getElementById('ap-login-form').dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    await flushPromises();
    expect(document.activeElement).toBe(document.getElementById('ap_login_username'));
  });

  test('redirects to dashboard url on success', async () => {
    fetch.mockResolvedValue({
      ok: true,
      json: async () => ({ success: true, data: { dashboardUrl: '/next' } }),
    });
    document.getElementById('ap-login-form').dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    await flushPromises();
    expect(window.location.href).toBe('/next');
  });

  afterEach(() => {
    window.location = originalLocation;
  });
});

describe('ap-auth register', () => {
  let originalLocation;
  beforeEach(async () => {
    jest.resetModules();
    document.body.innerHTML = `
      <div id="ap-register-message"></div>
      <div id="ap-register-success"></div>
      <form id="ap-register-form">
        <input id="ap_reg_display_name" name="display_name" required />
        <input id="ap_reg_bio" name="description" required />
        <input id="ap_reg_pass" name="password" required />
        <input id="ap_reg_confirm" name="password_confirm" required />
        <button type="submit">Register</button>
      </form>
    `;
    global.APLogin = { ajaxUrl: '/register', nonce: '123', dashboardUrl: '/dash' };
    global.fetch = jest.fn();
    await import('../ap-auth.js');
    document.dispatchEvent(new Event('DOMContentLoaded'));
    originalLocation = window.location;
    delete window.location;
    window.location = { href: 'http://example.com' };
  });

  test('renders server error and focuses first invalid field', async () => {
    fetch.mockResolvedValue({
      ok: true,
      json: async () => ({ success: false, data: { message: 'Invalid', invalid: ['display_name'] } }),
    });
    document.getElementById('ap-register-form').dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    await flushPromises();
    expect(document.getElementById('ap-register-message').textContent).toBe('Invalid');
    expect(document.activeElement).toBe(document.getElementById('ap_reg_display_name'));
    expect(document.getElementById('ap-register-success').textContent).toBe('');
  });

  test('focuses first invalid field when server provides no invalid list', async () => {
    fetch.mockResolvedValue({
      ok: true,
      json: async () => ({ success: false, data: { message: 'Invalid' } }),
    });
    document.getElementById('ap-register-form').dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    await flushPromises();
    expect(document.activeElement).toBe(document.getElementById('ap_reg_display_name'));
  });

  test('redirects to dashboard url on success', async () => {
    fetch.mockResolvedValue({
      ok: true,
      json: async () => ({ success: true, data: { message: 'Registered' } }),
    });
    document.getElementById('ap-register-form').dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    await flushPromises();
    expect(document.getElementById('ap-register-success').textContent).toBe('Registered');
    expect(window.location.href).toBe('/dash');
  });

  afterEach(() => {
    window.location = originalLocation;
  });
});
