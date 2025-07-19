const analyze = require('../tools/analyzer');

test('analyzer returns valid for non-empty input', () => {
  expect(analyze('test')).toEqual({ valid: true });
});

