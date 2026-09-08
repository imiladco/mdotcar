# Changelog

All notable changes to **MDotCar Mentoring** are documented here.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

The version lives in three places and must always match:

- `MDOTCAR_MENTORING_VERSION` in `mdotcar-mentoring.php`
- the `Version:` plugin header in `mdotcar-mentoring.php`
- `Stable tag:` in `readme.txt`

Use `bin/bump-version.sh <new-version>` to update all three at once.

## [Unreleased]

- Mentoring widget implementation (pending specification).

## [0.1.0] - 2026-09-08

### Added

- Plugin bootstrap with PHP/WordPress requirement checks and an admin notice
  when the environment is unsupported.
- SPL autoloader for `MDotCar_Mentoring_*` classes in `includes/` and `widgets/`.
- Installer with a stored-version option and a `mdotcar_mentoring_upgrade`
  action so per-release migrations run exactly once.
- Widget registration pipeline via the `mdotcar_mentoring_widgets` filter.
- Registered front-end stylesheet and script, versioned by the plugin version
  for cache busting, with automatic RTL stylesheet support.
- Translation loading from `/languages` (text domain `mdotcar-mentoring`).
- `uninstall.php` cleaning up plugin options on deletion.
