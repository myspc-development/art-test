import os
import re
from pathlib import Path
from collections import Counter
import yaml

FOOTER_TEXT = "\n\n💬 Found something outdated? Submit Feedback\n"


def parse_frontmatter(lines):
    if not lines or lines[0].strip() != '---':
        return None, 0
    fm_lines = []
    for i, line in enumerate(lines[1:], start=1):
        if line.strip() == '---':
            try:
                data = yaml.safe_load('\n'.join(fm_lines) or '{}')
            except Exception:
                return None, i + 1
            return data or {}, i + 1
        fm_lines.append(line)
    return None, 0


def validate_file(path):
    text = Path(path).read_text(encoding='utf-8')
    lines = text.splitlines()
    fm, body_start = parse_frontmatter(lines)
    frontmatter = fm is not None
    status_ok = fm and fm.get('status') in {'draft', 'complete'}
    body = '\n'.join(lines[body_start:])
    todo_free = not re.search(r'(TODO|TBD|coming soon)', body, re.IGNORECASE)
    h2_ok = sum(1 for l in lines[body_start:] if l.startswith('## ')) >= 2
    links_ok = True
    for href in re.findall(r'\[[^\]]+\]\(([^)]+)\)', body):
        if href.startswith('http'):
            links_ok = False
            break
        target = href.split('#')[0]
        if not target.endswith('.md'):
            links_ok = False
            break
        if not os.path.exists(os.path.join(os.path.dirname(path), target)):
            links_ok = False
            break
    return {
        'file': os.path.relpath(path, 'docs'),
        'frontmatter': frontmatter,
        'status': status_ok,
        'todos': todo_free,
        'sections': h2_ok,
        'links': links_ok,
    }


def build_validation_report(results, out_path):
    lines = [
        '# Documentation Validation Report',
        '',
        '| File | Frontmatter | Status | TODOs | H2 Sections | Links OK | ✅ Passed |',
        '|------|-------------|--------|-------|-------------|----------|-----------|',
    ]
    for r in results:
        passed = all([r['frontmatter'], r['status'], r['todos'], r['sections'], r['links']])
        lines.append(
            f"| {r['file']} | {'✅' if r['frontmatter'] else '❌'} | {'✅' if r['status'] else '❌'} | {'✅' if r['todos'] else '❌'} | {'✅' if r['sections'] else '❌'} | {'✅' if r['links'] else '❌'} | {'✅' if passed else '❌'} |"
        )
    Path(out_path).write_text('\n'.join(lines) + FOOTER_TEXT, encoding='utf-8')


def build_glossary(terms, out_path):
    lines = [
        '---',
        'title: Glossary',
        'category: docs',
        'role: developer',
        'last_updated: 2025-07-20',
        'status: draft',
        '---',
        '',
        '# Glossary',
        '',
        '| Term | Definition |',
        '|------|------------|',
    ]
    for term in terms:
        lines.append(f'| {term} | Definition of {term} |')
    Path(out_path).write_text('\n'.join(lines) + FOOTER_TEXT, encoding='utf-8')


def main():
    results = []
    counts = Counter()
    for md in Path('docs').rglob('*.md'):
        results.append(validate_file(md))
        text = Path(md).read_text(encoding='utf-8')
        clean = re.sub(r'```.*?```', '', text, flags=re.DOTALL)
        for t in re.findall(r'\*\*([^*]+?)\*\*', clean):
            t = t.strip()
            if 0 < len(t.split()) <= 4:
                counts[t] += 1
        if FOOTER_TEXT.strip() not in text:
            Path(md).write_text(text.rstrip() + FOOTER_TEXT, encoding='utf-8')
    # choose top 20 terms
    terms = [t for t, _ in counts.most_common(20)]
    build_validation_report(sorted(results, key=lambda x: x['file']), 'docs/validation-report.md')
    build_glossary(terms, 'docs/glossary.md')

if __name__ == '__main__':
    main()
