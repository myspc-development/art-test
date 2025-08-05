// jest.setup.js
// If localStorage is still unavailable, provide a simple in-memory mock
if (typeof localStorage === 'undefined') {
  class LocalStorageMock {
    constructor() { this.store = {}; }
    clear() { this.store = {}; }
    getItem(key) { return this.store[key] || null; }
    setItem(key, value) { this.store[key] = String(value); }
    removeItem(key) { delete this.store[key]; }
  }
  global.localStorage = new LocalStorageMock();
}
