import argparse
import json
from pathlib import Path

GUARD_LINE = "if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;"

PLUGIN_DIR = Path(__file__).resolve().parents[1]
WIDGET_DIRS = [PLUGIN_DIR / 'widgets', PLUGIN_DIR / 'templates/widgets']
MANIFEST_PATH = PLUGIN_DIR / 'widget-manifest-with-health.json'
REPORT_PATH = PLUGIN_DIR / 'widget-preview-report.md'


def main() -> int:
    parser = argparse.ArgumentParser(description="Ensure widget preview guard is present")
    parser.add_argument(
        "--check",
        action="store_true",
        help="Check mode. Exit 1 if any widget or manifest changes would occur",
    )
    parser.add_argument(
        "--fix",
        action="store_true",
        help="Fix mode (default). Writes missing guards and updates manifest/report",
    )
    args = parser.parse_args()
    fix_mode = args.fix or not args.check

    changed_files: list[str] = []
    widget_files: list[str] = []

    # Scan PHP widget files and ensure guard after opening tag
    for wdir in WIDGET_DIRS:
        for path in sorted(wdir.rglob("*.php")):
            rel = path.relative_to(PLUGIN_DIR)
            widget_files.append(rel.as_posix())
            lines = path.read_text().splitlines()
            if not lines:
                continue
            if lines[0].strip() != "<?php":
                continue  # html-first templates ignored
            guarded = len(lines) > 1 and lines[1].strip() == GUARD_LINE
            if not guarded:
                changed_files.append(rel.as_posix())
                if fix_mode:
                    lines.insert(1, GUARD_LINE)
                    path.write_text("\n".join(lines) + "\n")

    print(f"Scanned {len(widget_files)} widget PHP files")
    if fix_mode:
        print(f"Added guard to {len(changed_files)} files")

    duplicate_ids = {}
    unmapped = []

    if MANIFEST_PATH.exists():
        data = json.loads(MANIFEST_PATH.read_text())
        file_to_ids: dict[str, list[str]] = {}
        for wid, info in data.items():
            file_path = info.get("file", "")
            file_to_ids.setdefault(file_path, []).append(wid)
            preview_guarded = True
            if file_path.endswith(".php"):
                abs_path = PLUGIN_DIR / file_path
                if abs_path.exists():
                    lines = abs_path.read_text().splitlines()
                    if lines and lines[0].strip() == "<?php":
                        preview_guarded = len(lines) > 1 and lines[1].strip() == GUARD_LINE
                    else:
                        preview_guarded = False
                else:
                    preview_guarded = False
            if fix_mode:
                info["previewGuarded"] = preview_guarded
        # find duplicates and unmapped
        duplicate_ids = {fp: ids for fp, ids in file_to_ids.items() if len(ids) > 1}
        unmapped = sorted(set(widget_files) - set(file_to_ids.keys()))

        if fix_mode:
            # sort by role then id
            sorted_items = sorted(
                data.items(), key=lambda kv: ((kv[1]["roles"][0] if kv[1]["roles"] else ""), kv[0])
            )
            ordered = {k: v for k, v in sorted_items}
            MANIFEST_PATH.write_text(json.dumps(ordered, indent=4) + "\n")

            guarded_count = sum(1 for info in ordered.values() if info.get("previewGuarded"))
            unguarded_count = len(ordered) - guarded_count

            with open(REPORT_PATH, "w") as f:
                f.write("# Widget Preview Guard Report\n\n")
                f.write(f"Total widgets scanned: {len(widget_files)}\n")
                f.write(f"Widgets guarded: {guarded_count}\n")
                f.write(f"Widgets unguarded: {unguarded_count}\n")
                if duplicate_ids:
                    f.write("\n## Duplicate IDs\n")
                    for fp, ids in duplicate_ids.items():
                        f.write(f"- {fp}: {', '.join(ids)}\n")
                if changed_files:
                    f.write("\n## Files Updated\n")
                    for cf in changed_files:
                        f.write(f"- {cf}\n")

            if unmapped:
                print("[WARN] Unmapped widget files:")
                for fp in unmapped:
                    print(f" - {fp}")
            print(f"Updated manifest at {MANIFEST_PATH}")
            print(f"Report generated at {REPORT_PATH}")

    if args.check:
        if changed_files:
            print("[FAIL] Unguarded widgets detected:")
            for cf in changed_files:
                print(f" - {cf}")
            if duplicate_ids:
                print("[WARN] Duplicate widget IDs:")
                for fp, ids in duplicate_ids.items():
                    print(f" - {fp}: {', '.join(ids)}")
            if unmapped:
                print("[WARN] Unmapped widget files:")
                for fp in unmapped:
                    print(f" - {fp}")
            return 1
        return 0

    return 0


if __name__ == "__main__":
    raise SystemExit(main())

