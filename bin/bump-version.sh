#!/usr/bin/env bash
# Bump the plugin version everywhere it is declared.
#
# Usage: bin/bump-version.sh 1.2.3
set -euo pipefail

new="${1:-}"
if [[ ! "$new" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
	echo "Usage: $0 <major.minor.patch>" >&2
	exit 1
fi

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
main="$root/mdotcar-elementor.php"
readme="$root/readme.txt"

current="$(sed -n "s/^define( 'MDOTCAR_ELEMENTOR_VERSION', '\(.*\)' );$/\1/p" "$main")"

sed -i.bak -E "s/^( \* Version: +)[0-9]+\.[0-9]+\.[0-9]+$/\1$new/" "$main"
sed -i.bak -E "s/^define\( 'MDOTCAR_ELEMENTOR_VERSION', '[0-9]+\.[0-9]+\.[0-9]+' \);$/define( 'MDOTCAR_ELEMENTOR_VERSION', '$new' );/" "$main"
sed -i.bak -E "s/^Stable tag: [0-9]+\.[0-9]+\.[0-9]+$/Stable tag: $new/" "$readme"
rm -f "$main.bak" "$readme.bak"

echo "Bumped $current -> $new"
echo "Now add a '## [$new]' section to CHANGELOG.md and a '= $new =' entry to readme.txt."
