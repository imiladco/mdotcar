#!/usr/bin/env python3
"""Compile every languages/*.po into its .mo (stand-in for msgfmt)."""

import array
import pathlib
import re
import struct
import sys

ROOT = pathlib.Path(__file__).resolve().parent.parent
ENTRY = re.compile(
    r'^msgid\s+(?P<id>(?:"(?:\\.|[^"\\])*"\s*)+)^msgstr\s+(?P<str>(?:"(?:\\.|[^"\\])*"\s*)+)',
    re.M | re.S,
)


def unquote(block):
    parts = re.findall(r'"((?:\\.|[^"\\])*)"', block)
    text = "".join(parts)
    return (
        text.replace("\\n", "\n")
        .replace("\\t", "\t")
        .replace('\\"', '"')
        .replace("\\\\", "\\")
    )


def compile_po(po_path):
    catalog = {}

    for match in ENTRY.finditer(po_path.read_text(encoding="utf-8")):
        msgid = unquote(match.group("id"))
        msgstr = unquote(match.group("str"))
        # An empty translation means "untranslated": leave it out of the .mo.
        if msgstr or msgid == "":
            catalog[msgid] = msgstr

    keys = sorted(catalog)
    offsets = []
    ids = strs = b""

    for key in keys:
        encoded_id = key.encode("utf-8")
        encoded_str = catalog[key].encode("utf-8")
        offsets.append((len(ids), len(encoded_id), len(strs), len(encoded_str)))
        ids += encoded_id + b"\x00"
        strs += encoded_str + b"\x00"

    count = len(keys)
    key_start = 7 * 4 + 16 * count
    value_start = key_start + len(ids)
    key_offsets = []
    value_offsets = []

    for id_offset, id_len, str_offset, str_len in offsets:
        key_offsets += [id_len, id_offset + key_start]
        value_offsets += [str_len, str_offset + value_start]

    output = struct.pack(
        "Iiiiiii",
        0x950412DE,  # magic
        0,           # revision
        count,
        7 * 4,
        7 * 4 + count * 8,
        0,
        0,
    )
    output += array.array("i", key_offsets + value_offsets).tobytes()
    output += ids + strs

    mo_path = po_path.with_suffix(".mo")
    mo_path.write_bytes(output)
    print(f"Compiled {po_path.name} -> {mo_path.name} ({count} entries)")


def main():
    po_files = sorted((ROOT / "languages").glob("*.po"))

    if not po_files:
        print("No .po files found", file=sys.stderr)
        return 1

    for po_file in po_files:
        compile_po(po_file)

    return 0


if __name__ == "__main__":
    sys.exit(main())
