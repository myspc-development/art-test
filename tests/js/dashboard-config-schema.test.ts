import Ajv from 'ajv';
import schema from '../../schema/dashboard-config.schema.json';
import sample from './fixtures/dashboard-config.json';

describe('dashboard-config schema', () => {
  it('validates sample response', () => {
    const ajv = new Ajv({ allErrors: true, strict: false });
    const validate = ajv.compile(schema as any);
    const valid = validate(sample);
    expect(valid).toBe(true);
  });
});
