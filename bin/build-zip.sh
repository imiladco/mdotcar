#!/usr/bin/env bash
# Build an installable plugin ZIP: dist/mdotcar-elementor-<version>.zip
#
# The archive contains a single top-level `mdotcar-elementor/` folder, which is
# what "Plugins > Add New > Upload Plugin" expects.
set -euo pipefail

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
slug="mdotcar-elementor"
version="$(sed -n "s/^define( 'MDOTCAR_ELEMENTOR_VERSION', '\(.*\)' );$/\1/p" "$root/$slug.php")"

dist="$root/dist"
stage="$dist/$slug"
rm -rf "$stage" "$dist/$slug-$version.zip"
mkdir -p "$stage"

# Ship only runtime files: no git metadata, no build/dev tooling, no dist.
for entry in "$root"/* "$root"/.[!.]*; do
	[ -e "$entry" ] || continue
	case "$(basename "$entry")" in
		.git|.github|.gitignore|bin|dist|vendor|node_modules) continue ;;
		README.md|CHANGELOG.md|composer.json|composer.lock|phpcs.xml.dist|*.bak) continue ;;
	esac
	cp -R "$entry" "$stage/"
done

( cd "$dist" && zip -qr "$slug-$version.zip" "$slug" )
rm -rf "$stage"

echo "Built dist/$slug-$version.zip"
