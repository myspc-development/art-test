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

test('returns error when name is missing', () => {
  const result = analyze({ value: 1 });
  expect(result.valid).toBe(false);
  expect(result.errors).toContain('name is required');
});

test('returns error when value is non-numeric', () => {
  const result = analyze({ name: 'demo', value: 'oops' });
  expect(result.valid).toBe(false);
  expect(result.errors).toContain('value must be a number');
});

test.each([
  null,
  [],
])('returns error for non-object input %p', (input) => {
  const result = analyze(input);
  expect(result.valid).toBe(false);
  expect(result.errors).toContain('Input must be an object');
});

