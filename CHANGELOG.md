# Changelog

All notable changes to **MDotCar Elementor Widgets** are documented here.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

The version lives in three places and must always match:

- `MDOTCAR_ELEMENTOR_VERSION` in `mdotcar-elementor.php`
- the `Version:` plugin header in `mdotcar-elementor.php`
- `Stable tag:` in `readme.txt`

Use `bin/bump-version.sh <new-version>` to update all three at once.

## [Unreleased]

- Further Title widget presets (Style 2 and beyond).

## [0.2.0] - 2026-09-08

### Added

- **MDotCar Title widget (Style 1)** — a title with an icon in a flex box:
  - Layout: `display` (flex / inline-flex), `flex-direction`, `flex-wrap`,
    `align-content`, `justify-content`, `align-items` and `gap`, each responsive.
  - Justify/align controls are registered as horizontal/vertical pairs and
    swapped by the chosen direction, so their icons always match the axis they
    act on; Elementor skips CSS for the hidden half of each pair.
  - `align-content` only appears once wrapping is enabled, where it has effect.
  - Style: typography and color for the title, size and color for the icon,
    padding, border, radius and box shadow.
  - Hover state: background, border color, shadow, title and icon color, with a
    configurable transition duration.
  - Width and height each either fit the content or take a custom responsive
    value; a custom width is capped at `100%` so it stays responsive.
  - Default background gradient
    `linear-gradient(103deg, rgba(239,239,239,.6) 17.45%, rgba(255,255,255,.2) 79.67%)`,
    overridden by the Background control when set.
- "MDotCar" category in the Elementor panel.
- Elementor 3.5+ requirement check with an admin notice, and an Elementor CSS
  cache flush whenever the plugin version changes.

### Changed

- Plugin renamed from *MDotCar Mentoring* to **MDotCar Elementor Widgets**
  (slug, text domain, constants and class prefix now `mdotcar-elementor` /
  `MDotCar_Elementor_`). The 0.1.x scaffold registered classic WordPress
  widgets under a name that came from a misread request; nothing had been
  released, so the rename carries no migration.
- The front-end stylesheet is attached through `get_style_depends()`, so it
  loads only on pages that actually use a widget, and is also enqueued in the
  Elementor editor.

### Removed

- The empty front-end script; the widget needs no JavaScript.

## [0.1.1] - 2026-09-08

### Changed

- Moved the plugin to the repository root so a downloaded archive installs
  directly through *Plugins > Add New > Upload Plugin*; previously everything
  sat one level down in `mdotcar-mentoring/` and WordPress rejected the ZIP.

### Added

- `bin/build-zip.sh`, producing `dist/mdotcar-mentoring-<version>.zip` with a
  single correctly named top-level folder and without repository/dev files.

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
