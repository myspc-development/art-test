require('@testing-library/jest-dom');

if (typeof global.TextEncoder === "undefined") {
  const util = require("util");
  global.TextEncoder = util.TextEncoder;
  global.TextDecoder = util.TextDecoder;
}

// Polyfill localStorage in Jest tests when missing
if (typeof global.localStorage === 'undefined') {
  let store = {};
  const localStorageMock = {
    getItem(key) {
      return Object.prototype.hasOwnProperty.call(store, key) ? store[key] : null;
    },
    setItem(key, value) {
      store[key] = String(value);
    },
    removeItem(key) {
      delete store[key];
    },
    clear() {
      store = {};
    }
  };

  Object.defineProperty(global, 'localStorage', {
    value: localStorageMock,
    writable: true,
  });
}

if (typeof global.window !== 'undefined' && typeof global.window.localStorage === 'undefined') {
  Object.defineProperty(global.window, 'localStorage', {
    value: global.localStorage,
    writable: true,
  });
}

beforeEach(() => {
  global.fetch = jest.fn(() =>
    Promise.resolve({
      ok: true,
      json: () => Promise.resolve({ widgets: [], filters: [] }),
    })
  );
});

afterEach(() => {
  jest.restoreAllMocks();
});
