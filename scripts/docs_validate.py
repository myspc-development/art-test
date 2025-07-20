import os
import re
import sys

FAILURES = []

SNIPPET_PHRASE = "Found something outdated?"

for root, dirs, files in os.walk('docs'):
    if any(part in ('archive','site') for part in root.split(os.sep)):
        continue
    for f in files:
        if f.endswith('.md'):
            path = os.path.join(root, f)
            with open(path, 'r', encoding='utf-8') as fh:
                content = fh.read()
            if not re.match(r'^---\n.*?\n---', content, re.S):
                FAILURES.append(f"Missing frontmatter: {path}")
            else:
                fm = re.search(r'^---\n(.*?)\n---', content, re.S)
                if fm:
                    if 'status:' in fm.group(1):
                        status = re.search(r'status:\s*(.*)', fm.group(1)).group(1).strip()
                        if status not in ('draft', 'complete'):
                            FAILURES.append(f"Invalid status in {path}: {status}")
                    else:
                        FAILURES.append(f"Missing status in {path}")
            if re.search(r'\b(TODO|TBD|coming soon)\b', content, re.I):
                FAILURES.append(f"Prohibited phrase in {path}")
            for m in re.findall(r'\[[^\]]+\]\(([^)]+\.md)\)', content):
                if m.startswith('http'):
                    continue
                link_path = os.path.normpath(os.path.join(root, m))
                if not os.path.exists(link_path):
                    FAILURES.append(f"Broken link in {path}: {m}")
            if SNIPPET_PHRASE not in content:
                FAILURES.append(f"Missing feedback snippet in {path}")

if FAILURES:
    for f in FAILURES:
        print(f)
    sys.exit(1)
else:
    print('All docs validated successfully')
