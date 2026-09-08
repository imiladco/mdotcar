#!/usr/bin/env python3
"""Extract translatable strings into languages/mdotcar-elementor.pot.

A small stand-in for `wp i18n make-pot`, covering the call shapes this plugin
uses: __( 'text', 'mdotcar-elementor' ) and esc_html__/esc_attr__ variants,
plus the translator comments directly above them.
"""

import pathlib
import re
import sys

ROOT = pathlib.Path(__file__).resolve().parent.parent
DOMAIN = "mdotcar-elementor"
CALL = re.compile(
    r"\b(?:__|esc_html__|esc_attr__)\(\s*"
    r"(?P<quote>['\"])(?P<text>(?:\\.|(?!(?P=quote)).)*)(?P=quote)\s*,\s*"
    r"['\"]" + re.escape(DOMAIN) + r"['\"]\s*\)"
)
COMMENT = re.compile(r"/\*\s*translators:\s*(?P<text>.*?)\s*\*/", re.S)


def php_unescape(value, quote):
    if quote == "'":
        return value.replace("\\'", "'").replace("\\\\", "\\")
    return value.replace('\\"', '"').replace("\\\\", "\\")


def po_escape(value):
    return value.replace("\\", "\\\\").replace('"', '\\"').replace("\n", "\\n")


def collect():
    entries = {}

    for path in sorted(ROOT.rglob("*.php")):
        if any(part in {".git", "dist", "vendor", "node_modules"} for part in path.parts):
            continue

        source = path.read_text(encoding="utf-8")
        lines = source.splitlines()

        for match in CALL.finditer(source):
            text = php_unescape(match.group("text"), match.group("quote"))
            line = source.count("\n", 0, match.start()) + 1
            entry = entries.setdefault(text, {"refs": [], "comment": None})
            entry["refs"].append(f"{path.relative_to(ROOT)}:{line}")

            # A translator comment sits on the lines just above the call.
            window = "\n".join(lines[max(0, line - 4):line - 1])
            comment = COMMENT.search(window)
            if comment and not entry["comment"]:
                entry["comment"] = " ".join(comment.group("text").split())

    return entries


def main():
    entries = collect()

    out = [
        '# Copyright (C) mdotcar.com',
        '# This file is distributed under the GPL-2.0-or-later license.',
        'msgid ""',
        'msgstr ""',
        '"Project-Id-Version: MDotCar Elementor Widgets\\n"',
        '"Report-Msgid-Bugs-To: https://mdotcar.com/\\n"',
        '"MIME-Version: 1.0\\n"',
        '"Content-Type: text/plain; charset=UTF-8\\n"',
        '"Content-Transfer-Encoding: 8bit\\n"',
        '"Plural-Forms: nplurals=2; plural=(n > 1);\\n"',
        f'"X-Domain: {DOMAIN}\\n"',
        "",
    ]

    for text, meta in sorted(entries.items()):
        if meta["comment"]:
            out.append(f'#. translators: {meta["comment"]}')
        for ref in meta["refs"]:
            out.append(f"#: {ref}")
        out.append(f'msgid "{po_escape(text)}"')
        out.append('msgstr ""')
        out.append("")

    target = ROOT / "languages" / f"{DOMAIN}.pot"
    target.write_text("\n".join(out), encoding="utf-8")
    print(f"Wrote {target.relative_to(ROOT)} with {len(entries)} strings")
    return 0


if __name__ == "__main__":
    sys.exit(main())
