import json
from pathlib import Path

GUARD_LINE = "if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;"

PLUGIN_DIR = Path(__file__).resolve().parents[1]
WIDGET_DIRS = [PLUGIN_DIR / 'widgets', PLUGIN_DIR / 'templates/widgets']
MANIFEST_PATH = PLUGIN_DIR / 'widget-manifest-with-health.json'
REPORT_PATH = PLUGIN_DIR / 'widget-preview-report.md'

changed_files = []
widget_files = []

# Scan PHP widget files and ensure guard after opening tag
for wdir in WIDGET_DIRS:
    for path in sorted(wdir.glob('*.php')):
        rel = path.relative_to(PLUGIN_DIR)
        widget_files.append(rel.as_posix())
        lines = path.read_text().splitlines()
        if not lines:
            continue
        if lines[0].strip() != '<?php':
            continue  # html-first templates ignored
        guarded = len(lines) > 1 and lines[1].strip() == GUARD_LINE
        if not guarded:
            lines.insert(1, GUARD_LINE)
            path.write_text('\n'.join(lines) + '\n')
            changed_files.append(rel.as_posix())

print(f"Scanned {len(widget_files)} widget PHP files")
print(f"Added guard to {len(changed_files)} files")

# Load manifest and update previewGuarded flag
if MANIFEST_PATH.exists():
    data = json.loads(MANIFEST_PATH.read_text())
    file_to_ids = {}
    for wid, info in data.items():
        file_path = info.get('file', '')
        file_to_ids.setdefault(file_path, []).append(wid)
        preview_guarded = True
        if file_path.endswith('.php'):
            abs_path = PLUGIN_DIR / file_path
            if abs_path.exists():
                lines = abs_path.read_text().splitlines()
                if lines and lines[0].strip() == '<?php':
                    preview_guarded = len(lines) > 1 and lines[1].strip() == GUARD_LINE
                else:
                    preview_guarded = False
            else:
                preview_guarded = False
        info['previewGuarded'] = preview_guarded
    # find duplicates and unmapped
    duplicate_ids = {fp: ids for fp, ids in file_to_ids.items() if len(ids) > 1}
    unmapped = sorted(set(widget_files) - set(file_to_ids.keys()))

    # sort by role then id
    sorted_items = sorted(data.items(), key=lambda kv: ((kv[1]['roles'][0] if kv[1]['roles'] else ''), kv[0]))
    ordered = {k: v for k, v in sorted_items}
    MANIFEST_PATH.write_text(json.dumps(ordered, indent=4) + '\n')

    guarded_count = sum(1 for info in ordered.values() if info.get('previewGuarded'))
    unguarded_count = len(ordered) - guarded_count

    with open(REPORT_PATH, 'w') as f:
        f.write('# Widget Preview Guard Report\n\n')
        f.write(f'Total widgets scanned: {len(widget_files)}\n')
        f.write(f'Widgets guarded: {guarded_count}\n')
        f.write(f'Widgets unguarded: {unguarded_count}\n')
        if duplicate_ids:
            f.write('\n## Duplicate IDs\n')
            for fp, ids in duplicate_ids.items():
                f.write(f'- {fp}: {", ".join(ids)}\n')
        if unmapped:
            f.write('\n## Unmapped Files\n')
            for fp in unmapped:
                f.write(f'- {fp}\n')
        if changed_files:
            f.write('\n## Files Updated\n')
            for cf in changed_files:
                f.write(f'- {cf}\n')

    print(f'Updated manifest at {MANIFEST_PATH}')
    print(f'Report generated at {REPORT_PATH}')

