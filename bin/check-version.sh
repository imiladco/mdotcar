#!/usr/bin/env bash
# Verify the version is identical everywhere it is declared, and that the
# changelogs carry an entry for it. Run by CI and before every release.
set -euo pipefail

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
main="$root/mdotcar-elementor.php"
status=0

constant="$(sed -n "s/^define( 'MDOTCAR_ELEMENTOR_VERSION', '\(.*\)' );$/\1/p" "$main")"
header="$(sed -n "s/^ \* Version: *\(.*\)$/\1/p" "$main" | tr -d '[:space:]')"
stable="$(sed -n "s/^Stable tag: *\(.*\)$/\1/p" "$root/readme.txt" | tr -d '[:space:]')"

echo "constant:   ${constant:-<missing>}"
echo "header:     ${header:-<missing>}"
echo "stable tag: ${stable:-<missing>}"

if [[ ! "$constant" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
	echo "error: MDOTCAR_ELEMENTOR_VERSION is not a semver version" >&2
	status=1
fi

if [[ "$constant" != "$header" || "$constant" != "$stable" ]]; then
	echo "error: version mismatch; run bin/bump-version.sh <version>" >&2
	status=1
fi

if ! grep -q "^## \[$constant\]" "$root/CHANGELOG.md"; then
	echo "error: CHANGELOG.md has no '## [$constant]' section" >&2
	status=1
fi

if ! grep -q "^= $constant =$" "$root/readme.txt"; then
	echo "error: readme.txt has no '= $constant =' changelog entry" >&2
	status=1
fi

if [[ $status -eq 0 ]]; then
	echo "Version $constant is consistent."
fi

exit $status
