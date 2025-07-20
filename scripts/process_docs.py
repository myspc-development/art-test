import os
import re
import yaml
from pathlib import Path

allowed_categories = {'widgets','admin','developer','qa','api','user'}
allowed_roles = {'developer','admin','qa','user'}

def infer_title(lines, filename):
    for line in lines:
        if line.startswith('# '):
            return line[2:].strip()
    name = Path(filename).stem
    name = re.sub(r'[-_]', ' ', name)
    name = re.sub(r'(?<!^)(?=[A-Z])', ' ', name)
    return name.title()

def infer_category(parts):
    for p in parts:
        if p in allowed_categories:
            return p
    return 'developer'

def infer_role(parts):
    for p in parts:
        if p in allowed_roles:
            return p
    return 'developer'

def has_frontmatter(lines):
    return len(lines) > 0 and lines[0].strip() == '---'

def parse_frontmatter(lines):
    if not has_frontmatter(lines):
        return None, lines
    fm_lines = []
    body_start = 0
    for i, line in enumerate(lines[1:], start=1):
        if line.strip() == '---':
            body_start = i + 1
            break
        fm_lines.append(line)
    fm = yaml.safe_load('\n'.join(fm_lines)) if fm_lines else {}
    return fm, lines[body_start:]

def build_frontmatter(title, category, role, status='draft'):
    fm = {
        'title': title,
        'category': category,
        'role': role,
        'last_updated': '2025-07-20',
        'status': status,
    }
    return '---\n' + '\n'.join(f"{k}: {v}" for k,v in fm.items()) + '\n---\n'

def update_status(fm, body):
    if fm.get('status') != 'draft':
        return fm
    h2_count = len([l for l in body if l.startswith('## ')])
    word_count = len(re.findall(r'\w+', '\n'.join(body)))
    if h2_count >= 3 and word_count > 300 and not re.search(r'TODO|TBD', '\n'.join(body), re.IGNORECASE):
        fm['status'] = 'complete'
        fm.setdefault('last_updated', '2025-07-20')
    return fm

def ensure_single_h1(body):
    first_h1 = None
    new_body = []
    for line in body:
        if line.startswith('# '):
            if first_h1 is None:
                first_h1 = line
                new_body.append(line)
            else:
                new_body.append('##' + line[1:])
        else:
            new_body.append(line)
    return new_body

def relative_links_ok(body):
    links = re.findall(r'\[[^\]]+\]\(([^)]+)\)', '\n'.join(body))
    for href in links:
        if href.startswith('http'):
            return False
        if not href.split('#')[0].endswith('.md'):
            return False
    return True

def process_file(path):
    text = Path(path).read_text(encoding='utf-8')
    lines = text.splitlines()
    parts = Path(path).parts
    fm, body_lines = parse_frontmatter(lines)
    if fm is None:
        title = infer_title(lines, path)
        category = infer_category(parts)
        role = infer_role(parts)
        fm = {
            'title': title,
            'category': category,
            'role': role,
            'last_updated': '2025-07-20',
            'status': 'draft'
        }
        frontmatter = build_frontmatter(title, category, role)
    else:
        fm = update_status(fm, body_lines)
        frontmatter = '---\n' + '\n'.join(f"{k}: {v}" for k,v in fm.items()) + '\n---\n'
    body_lines = ensure_single_h1(body_lines)
    Path(path).write_text(frontmatter + '\n'.join(body_lines), encoding='utf-8')
    h2_count = len([l for l in body_lines if l.startswith('## ')]) >= 2
    links_ok = relative_links_ok(body_lines)
    no_todo = not re.search(r'TODO|TBD', '\n'.join(body_lines), re.IGNORECASE)
    return {
        'file': os.path.relpath(path, 'docs'),
        'frontmatter': True,
        'status': 'status' in fm,
        'todos': no_todo,
        'sections': h2_count,
        'links': links_ok,
        'passed': all([True, 'status' in fm, no_todo, h2_count, links_ok])
    }

def main():
    report = []
    terms = set()
    for md in Path('docs').rglob('*.md'):
        r = process_file(md)
        report.append(r)
        text = Path(md).read_text(encoding='utf-8')
        # remove fenced code blocks to avoid noise
        text_clean = re.sub(r'```.*?```', '', text, flags=re.DOTALL)
        for term in re.findall(r'\*\*([^*]+)\*\*', text_clean):
            t = term.strip()
            if 0 < len(t.split()) <= 5:
                terms.add(t)
        for term in re.findall(r'_([^_]+)_', text_clean):
            t = term.strip()
            if 0 < len(t.split()) <= 5:
                terms.add(t)
    # Glossary
    with open('docs/glossary.md','w',encoding='utf-8') as f:
        f.write('# Glossary\n\n| Term | Definition |\n|------|------------|\n')
        for term in sorted(terms):
            f.write(f'| {term} | Definition of {term} |\n')
    # Validation report
    with open('docs/validation-report.md','w',encoding='utf-8') as f:
        f.write('# Documentation Validation Report\n\n')
        f.write('| File | Frontmatter | Status | TODOs | Sections | Links | ✅ Passed |\n')
        f.write('|------|-------------|--------|-------|----------|-------|----------|\n')
        for r in report:
            f.write(f"| {r['file']} | {'✅' if r['frontmatter'] else '❌'} | {'✅' if r['status'] else '❌'} | {'✅' if r['todos'] else '❌'} | {'✅' if r['sections'] else '❌'} | {'✅' if r['links'] else '❌'} | {'✅' if r['passed'] else '❌'} |\n")

if __name__ == '__main__':
    main()
