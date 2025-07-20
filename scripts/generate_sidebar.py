import os

def collect_sidebar():
    docs_dir = 'docs'
    groups = {}
    for entry in sorted(os.listdir(docs_dir)):
        path = os.path.join(docs_dir, entry)
        if os.path.isdir(path) and entry not in ('site', 'archive', '.site-config'):
            files = [f for f in sorted(os.listdir(path)) if f.endswith('.md')]
            groups[entry] = [f for f in files if f != 'README.md']
    # root level files
    root_files = [f for f in sorted(os.listdir(docs_dir)) if f.endswith('.md')]
    groups['.'] = [f for f in root_files if f not in ('README.md', 'index.md')]
    return groups

def generate_sidebar(groups):
    lines = ['* [Home](../index.md)']
    for group, files in groups.items():
        if group == '.':
            for f in files:
                lines.append(f"* [{f}](../{f})")
            continue
        lines.append(f"* **{group.capitalize()}**")
        readme_path = os.path.join('docs', group, 'README.md')
        if os.path.exists(readme_path):
            lines.append(f"  * [Overview]({group}/README.md)")
        for f in files:
            lines.append(f"  * [{f}]({group}/{f})")
    return '\n'.join(lines)

if __name__ == '__main__':
    groups = collect_sidebar()
    sidebar = generate_sidebar(groups)
    with open('docs/site/sidebar.md', 'w') as f:
        f.write(sidebar)
