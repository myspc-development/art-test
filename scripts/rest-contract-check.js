import fs from 'fs';
import Ajv from 'ajv';

const schemaPath = new URL('../schema/dashboard-config.schema.json', import.meta.url);
const schema = JSON.parse(fs.readFileSync(schemaPath, 'utf8'));
const ajv = new Ajv({ allErrors: true, strict: false });
const validate = ajv.compile(schema);

const base = process.env.REST_BASE_URL || 'http://localhost:8889';
const url = base.replace(/\/$/, '') + '/wp-json/artpulse/v1/dashboard-config';

fetch(url)
  .then(r => r.json())
  .then(data => {
    const valid = validate(data);
    if (!valid) {
      console.error('Validation errors:\n', JSON.stringify(validate.errors, null, 2));
      process.exit(1);
    }
    console.log('dashboard-config contract OK');
  })
  .catch(err => {
    console.error('Request failed', err);
    process.exit(1);
  });
