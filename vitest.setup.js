import { vi } from 'vitest';
import '@testing-library/jest-dom';

Object.defineProperty(window, 'localStorage', {
  value: {
    getItem: vi.fn(),
    setItem: vi.fn(),
    removeItem: vi.fn(),
  },
  writable: true,
});
