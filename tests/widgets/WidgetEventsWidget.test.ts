import fs from 'fs';
import path from 'path';
import Ajv from 'ajv';
const ajv = new Ajv({ allErrors: true, strict: false });

const name = 'WidgetEventsWidget';
const rel = 'WidgetEventsWidget';
const schemaPath = path.join(process.cwd(), 'widgets', rel + '.schema.json');
const schema = JSON.parse(fs.readFileSync(schemaPath, 'utf8'));

test(`${name} schema validates minimal props`, () => {
  const validate = ajv.compile(schema);
  const ok = validate({});
  expect(ok).toBe(true);
});

const filePath = path.join(process.cwd(), 'widgets', rel + '.php');
test(`${name} PHP widget snapshot`, () => {
  const firstLine = fs.readFileSync(filePath, 'utf8').split('\n')[0];
  expect(firstLine).toMatchSnapshot();
});
