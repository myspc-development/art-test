const analyze = require('../tools/analyzer');

test('returns valid for object with name and value', () => {
  const input = { name: 'demo', value: 1 };
  expect(analyze(input)).toEqual({ valid: true, errors: [], data: input });
});

test('returns errors for invalid JSON', () => {
  const result = analyze('bad');
  expect(result.valid).toBe(false);
  expect(result.errors.length).toBeGreaterThan(0);
});

