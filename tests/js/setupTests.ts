import '@testing-library/jest-dom';

// Polyfill for some libs in JSDOM
// @ts-ignore
global.TextEncoder = global.TextEncoder || require('util').TextEncoder;
// @ts-ignore
global.TextDecoder = global.TextDecoder || require('util').TextDecoder;
