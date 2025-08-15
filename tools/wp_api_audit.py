import os
import re
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OUTPUT_JSON = ROOT / 'out' / 'api_routes.json'
OUTPUT_MD = ROOT / 'docs' / 'api_audit.md'
OUTPUT_OPENAPI = ROOT / 'openapi' / 'openapi.yaml'

REST_PATTERN = re.compile(
    r"register_rest_route\(\s*(?P<ns>['\"][^'\"]+['\"]|[A-Z0-9_\\\\]+)\s*,\s*(['\"])(?P<route>[^'\"]+)\2\s*,\s*(?P<args>\[.*?\])\s*\)",
    re.DOTALL
)

AJAX_PATTERN = re.compile(
    r"add_action\(\s*['\"]wp_ajax(?P<nopriv>_nopriv)?_(?P<action>[^'\"]+)['\"]\s*,\s*([^)]+)\)")

CAP_PATTERN = re.compile(r"current_user_can\(\s*['\"]([^'\"]+)['\"]")
NONCE_PATTERN = re.compile(r"(check_ajax_referer|wp_verify_nonce|check_admin_referer)")
ARG_PATTERN = re.compile(r"'(?P<arg>[a-zA-Z0-9_]+)'\s*=>\s*\[(?P<body>.*?)\]", re.DOTALL)
TYPE_PATTERN = re.compile(r"'type'\s*=>\s*['\"]([^'\"]+)")
REQUIRED_PATTERN = re.compile(r"'required'\s*=>\s*(true|false)")
SANITIZE_PATTERN = re.compile(r"'sanitize_callback'\s*=>\s*['\"]([^'\"]+)")
VALIDATE_PATTERN = re.compile(r"'validate_callback'\s*=>\s*['\"]([^'\"]+)")


def relpath(p: Path) -> str:
    return str(p.relative_to(ROOT))


def parse_rest(file_path: Path, content: str):
    rest = []
    for match in REST_PATTERN.finditer(content):
        ns = match.group('ns')
        route = match.group('route')
        args_str = match.group('args')
        line = content[: match.start()].count('\n') + 1

        methods = []
        m = re.search(r"'methods'\s*=>\s*([^,]+)", args_str)
        if m:
            methods_raw = m.group(1)
            methods = [s.strip("'\" ") for s in re.split(r"[|,]", methods_raw)]

        perm_cb_present = "permission_callback" in args_str
        nonce_present = bool(re.search(NONCE_PATTERN, content))

        caps = CAP_PATTERN.findall(content)

        args = {}
        args_block_match = re.search(r"'args'\s*=>\s*\[(.*)\]", args_str, re.DOTALL)
        if args_block_match:
            block = args_block_match.group(1)
            for arg_match in ARG_PATTERN.finditer(block):
                arg_name = arg_match.group('arg')
                body = arg_match.group('body')
                arg_info = {}
                t = TYPE_PATTERN.search(body)
                if t:
                    arg_info['type'] = t.group(1)
                r = REQUIRED_PATTERN.search(body)
                if r:
                    arg_info['required'] = r.group(1) == 'true'
                s = SANITIZE_PATTERN.search(body)
                if s:
                    arg_info['sanitize'] = s.group(1)
                v = VALIDATE_PATTERN.search(body)
                if v:
                    arg_info['validate'] = v.group(1)
                args[arg_name] = arg_info

        pagination = {
            'page': 'page' in args,
            'per_page': 'per_page' in args,
            'search': 'search' in args,
        }

        rest.append({
            'namespace': ns,
            'route': route,
            'methods': methods,
            'file': relpath(file_path),
            'line': line,
            'permission_callback_present': perm_cb_present,
            'capabilities': list(sorted(set(caps))),
            'nonce_check_present': nonce_present,
            'args': args,
            'errors_standardized': False,
            'pagination': pagination,
            'notes': ''
        })
    return rest


def parse_ajax(file_path: Path, content: str):
    ajax = []
    for match in AJAX_PATTERN.finditer(content):
        action = match.group('action')
        nopriv = bool(match.group('nopriv'))
        line = content[: match.start()].count('\n') + 1
        nonce_present = bool(re.search(NONCE_PATTERN, content))
        caps = CAP_PATTERN.findall(content)
        ajax.append({
            'action': action,
            'nopriv': nopriv,
            'file': relpath(file_path),
            'line': line,
            'nonce_check_present': nonce_present,
            'capability_checks': list(sorted(set(caps))),
            'notes': ''
        })
    return ajax


def scan():
    rest_endpoints = []
    ajax_endpoints = []
    constants = {}
    # collect simple define('CONST','value') in root files
    for path in ROOT.glob('*.php'):
        text = path.read_text(encoding='utf-8', errors='ignore')
        for m in re.finditer(r"define\(\s*['\"]([A-Z0-9_]+)['\"]\s*,\s*['\"]([^'\"]+)['\"]", text):
            constants[m.group(1)] = m.group(2)

    excluded = {'vendor', 'node_modules', 'tests', 'docs', 'openapi', 'out', 'patches'}
    for path in ROOT.rglob('*.php'):
        if any(part in excluded for part in path.parts):
            continue
        try:
            text = path.read_text(encoding='utf-8', errors='ignore')
        except Exception:
            continue
        if 'register_rest_route' in text:
            rest_endpoints.extend(parse_rest(path, text))
        if 'wp_ajax_' in text:
            ajax_endpoints.extend(parse_ajax(path, text))

    # replace namespace constants with values
    for r in rest_endpoints:
        ns = r['namespace']
        if ns in constants:
            r['namespace'] = constants[ns]
    summary = {
        'rest_count': len(rest_endpoints),
        'ajax_count': len(ajax_endpoints)
    }
    data = {
        'rest': rest_endpoints,
        'ajax': ajax_endpoints,
        'summary': summary
    }
    OUTPUT_JSON.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT_MD.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT_OPENAPI.parent.mkdir(parents=True, exist_ok=True)
    with OUTPUT_JSON.open('w', encoding='utf-8') as f:
        json.dump(data, f, indent=2)
    generate_markdown(data)
    generate_openapi(data)


def generate_markdown(data):
    lines = ["# API Audit Report", "", f"Total REST endpoints: {data['summary']['rest_count']}", f"Total AJAX actions: {data['summary']['ajax_count']}", "", "## REST Routes", "", "| Namespace | Methods | Path | File:Line | permission_callback | Nonce |", "|-----------|---------|------|-----------|-------------------|-------|"]
    for r in data['rest']:
        path = f"/{r['namespace']}{r['route']}"
        methods = ','.join(r['methods'])
        fileline = f"{r['file']}:{r['line']}"
        lines.append(f"| {r['namespace']} | {methods} | {r['route']} | {fileline} | {'yes' if r['permission_callback_present'] else 'no'} | {'yes' if r['nonce_check_present'] else 'no'} |")
    lines.extend(["", "## AJAX Actions", "", "| Action | nopriv | File:Line | Nonce | Capabilities |", "|--------|--------|-----------|-------|--------------|"])
    for a in data['ajax']:
        fileline = f"{a['file']}:{a['line']}"
        caps = ','.join(a['capability_checks'])
        lines.append(f"| {a['action']} | {str(a['nopriv'])} | {fileline} | {'yes' if a['nonce_check_present'] else 'no'} | {caps} |")
    OUTPUT_MD.write_text('\n'.join(lines), encoding='utf-8')


def generate_openapi(data):
    lines = ["openapi: 3.0.0", "info:", "  title: ArtPulse API (Stub)", "  version: '1.0.0'", "paths:"]
    for r in data['rest']:
        full_path = f"/{r['namespace']}{r['route']}"
        lines.append(f"  {full_path}:")
        for method in r['methods']:
            m = method.lower()
            lines.append(f"    {m}:")
            lines.append(f"      summary: Auto-generated stub")
            if r['args']:
                lines.append("      parameters:")
                for arg, info in r['args'].items():
                    typ = info.get('type', 'string')
                    lines.append(f"        - in: query")
                    lines.append(f"          name: {arg}")
                    lines.append(f"          schema:")
                    lines.append(f"            type: {typ}")
                    if info.get('required'):
                        lines.append(f"          required: true")
            lines.append("      responses:")
            lines.append("        '200':")
            lines.append("          description: OK")
    # security schemes
    lines.append("components:")
    lines.append("  securitySchemes:")
    lines.append("    cookieAuth:")
    lines.append("      type: apiKey")
    lines.append("      in: cookie")
    lines.append("      name: wordpress_logged_in_")
    OUTPUT_OPENAPI.write_text('\n'.join(lines), encoding='utf-8')


if __name__ == '__main__':
    scan()
