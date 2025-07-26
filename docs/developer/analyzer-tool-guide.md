---
title: Analyzer Tool Guide
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---

# Analyzer Tool Guide

The `tools/analyzer/index.js` script validates simple JSON input. The object must include a `name` string and a numeric `value`.

## Usage

Run the analyzer from the project root:

```bash
node -e "const analyze=require('./tools/analyzer');console.log(analyze({name:'demo',value:1}))"
```

Invalid data returns an array of errors so the results can integrate with other checks.
