import '@testing-library/jest-dom';

if (typeof (global as any).TextEncoder === 'undefined') {
  const util = require('util');
  (global as any).TextEncoder = util.TextEncoder;
  (global as any).TextDecoder = util.TextDecoder;
}

// Polyfill localStorage in Jest tests when missing
if (typeof (global as any).localStorage === 'undefined') {
  let store: Record<string, string> = {};
  const localStorageMock = {
    getItem(key: string) {
      return Object.prototype.hasOwnProperty.call(store, key) ? store[key] : null;
    },
    setItem(key: string, value: string) {
      store[key] = String(value);
    },
    removeItem(key: string) {
      delete store[key];
    },
    clear() {
      store = {};
    },
  };
  Object.defineProperty(global, 'localStorage', {
    value: localStorageMock,
    writable: true,
  });
}

if (typeof global.window !== 'undefined' && typeof (global.window as any).localStorage === 'undefined') {
  Object.defineProperty(global.window, 'localStorage', {
    value: (global as any).localStorage,
    writable: true,
  });
}

const originalError = console.error;
const allowed = [/React state update on an unmounted component/, /Warning:.*not wrapped in act/];

beforeEach(() => {
  (global as any).fetch = jest.fn(() =>
    Promise.resolve({
      ok: true,
      json: () => Promise.resolve({ widgets: [], filters: [] }),
    })
  );
  console.error = (...args: any[]) => {
    if (allowed.some((re) => re.test(String(args[0])))) {
      return;
    }
    originalError(...args);
    throw new Error(args.join(' '));
  };
});

afterEach(() => {
  jest.restoreAllMocks();
  console.error = originalError;
});
