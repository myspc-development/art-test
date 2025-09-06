import '@testing-library/jest-dom';
import { TextEncoder, TextDecoder } from 'util';

if (typeof (global as any).TextEncoder === 'undefined') {
  (global as any).TextEncoder = TextEncoder;

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

beforeEach(() => {
  (global as any).fetch = jest.fn(() =>
    Promise.resolve({
      ok: true,
      json: () => Promise.resolve({ widgets: [], filters: [] }),
    })
  );
});

afterEach(() => {
  jest.restoreAllMocks();
});

const ALLOW = [
  /React act\(.+\) warning/i,
  /React state update on an unmounted component/i,
  /Warning:.*not wrapped in act/i,
  /Failed to load dashboard configuration/i,
];

const originalError = console.error;
console.error = (...args: unknown[]) => {
  const asText = args.map(String).join(' ');
  if (ALLOW.some((rx) => rx.test(asText))) {
    return originalError(...(args as any));
  }
  throw new Error(`Unexpected console.error in test:\n${asText}`);
};

afterAll(() => {
  console.error = originalError;
});
