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

## [0.7.4] - 2026-09-16

### Fixed

- **Action icon was on the wrong side.** The icon rendered before the text in
  the DOM, which a `flex-direction: row` row places at the main-start edge —
  the right, in RTL — putting the chevron to the right of "ثبت درخواست"
  instead of to its left as the design shows. Swapped the render order so the
  text comes first (right) and the icon second (left), confirmed by measuring
  both elements' on-page position before and after.

### Changed

- **Action button margin and padding are now full, independent controls.**
  The two single-direction sliders (Space Above Action, Space Below Action)
  are replaced by a **Margin** and a **Padding** control, each a responsive
  4-side dimensions field (top/right/bottom/left, independently unlinkable) on
  the action button itself, matching the pattern Elementor's own Button widget
  uses. Padding defaults to 12/0/8/0, reproducing the previous spacing;
  Margin defaults to 0 and is new — room to pull the button off full width
  once it gets its own background or border.

## [0.7.3] - 2026-09-16

### Added

- Items-per-row (Style → Layout → Columns) and the Gap between cards were
  already responsive controls — Desktop/Tablet/Mobile default to 3/2/1
  columns, switchable per device via the small icon next to each control's
  label. Gap now also defaults per device (24/20/16px) instead of carrying the
  desktop value to every breakpoint unless the admin overrode it.

## [0.7.2] - 2026-09-16

### Fixed

- A real uploaded vehicle photo could render far taller than the configured
  Image Height (a photo would balloon well past its card, matching the small
  WordPress placeholder image while a real photo did not). Cause: themes and
  even Elementor itself commonly ship a global image reset such as
  `.elementor-widget-container img { max-width: 100%; height: auto; }`, whose
  specificity (0,1,1) beats a plain class selector like
  `.mdotcar-vehicle__image { height: 120px }` (0,1,0). Losing `height` falls
  back to the image's own aspect ratio — invisible on the built-in placeholder,
  which is small on its own, but very visible on a real high-resolution photo.
  Reproduced with that exact reset rule and confirmed fixed: computed height
  went from 517px back to the intended 120px.
  `.mdotcar-vehicle__image` (width, max-width, height) and
  `.mdotcar-vehicle__badge-image` (width, height) — both the stylesheet
  defaults and the Image Height / Image Max Width / Badge Icon Size panel
  controls — now carry `!important` on those dimensions, the ones such resets
  touch.

## [0.7.1] - 2026-09-16

### Fixed

- **The plugin stylesheet never loaded on an RTL site — which mdotcar.com is.**
  `register_assets()` called `wp_style_add_data( HANDLE, 'rtl', 'replace' )`.
  WordPress's `'replace'` value means: on an RTL site, load the `-rtl.css`
  file **instead of** the main stylesheet, not in addition to it. The plugin's
  `-rtl.css` was written as a handful of small overrides layered on top of the
  main file (text direction, a reversed tariff row), never as a full
  stylesheet — so on every RTL visitor, WordPress served only those dozen
  lines and silently dropped the other ~370 lines: every flex/grid rule, icon
  sizing, spacing and the backdrop layer. This is why every fix attempted
  since 0.2.0 for "the widget looks unstyled" only ever addressed a secondary
  symptom — colours, typography, border, radius and shadow kept working
  throughout, because those are emitted by Elementor's own per-widget
  generated CSS, a separate mechanism the RTL bug never touched; only the
  rules that exist solely in the plugin's static stylesheet were missing,
  which is exactly the layout-broken, colour-correct pattern reported.
  Changed to `wp_style_add_data( HANDLE, 'rtl', true )`, which loads the main
  stylesheet and appends `-rtl.css` after it, matching how that file is
  actually written.

## [0.7.0] - 2026-09-16

### Changed

Vehicle Tariff rebuilt against the full design spec:

- The 32px top padding and 16px bottom padding now belong to the card itself,
  and the blue ellipse and the badge are positioned against the card rather
  than the image row, which is what the design's coordinates describe.
- Shadow stated exactly as `0 2px 10px 0 #1824651A`.
- Tariff columns are spaced by a 16px gap with the divider drawn as a
  pseudo-element **in the middle of that gap**, instead of a border on the
  column edge; the offset follows the gap control automatically, on whichever
  side RTL puts it. Each column is a flex column with a 4px gap.
- The rule above the action is **dashed** by default, with style, colour,
  space above (12px), space below (8px) and icon gap (8px) as controls.
- The vehicle image keeps `height: 120px` with automatic width, and gains an
  Image Fit control plus a max-width cap.

### Added

- Badge: padding, radius and offset controls — the badge sizes itself from its
  icon plus its padding — and a per-vehicle **Badge Image** that takes
  precedence over the icon.
- **Backdrop Layer**: an image or SVG laid over the card background and behind
  the content, with size, position, repeat and opacity; set once for the widget
  and overridable per vehicle.

## [0.6.1] - 2026-09-16

### Fixed

- The Vehicle Tariff widget came up unstyled. Two causes, both addressed:
  - The design values lived only in the plugin stylesheet, so nothing showed
    if that file did not reach the page, and the panel's style fields were all
    empty. Every one of them now carries the design value as its control
    default — radius 16, the `#0095FF` accent edge, the `0 2px 10px
    rgba(24,36,101,.1)` shadow, the `#E6F4FF` backdrop, the 48px badge, and the
    typography and colours of the title, tariff, currency and action text — so
    the card matches the design out of the box and the values are visible and
    editable in the panel.
  - Assets are now registered on `wp_enqueue_scripts` as well as Elementor's
    own hook, and each widget enqueues them while rendering, so the stylesheet
    and script load whatever asset-loading mode Elementor is in.
    `get_style_depends()` still declares the dependency for optimised loading.

## [0.6.0] - 2026-09-15

### Added

- **MDotCar Vehicle Tariff widget** — the car-wash tariff cards. A repeater
  holds any number of vehicles; each card carries an image, a badge, a title,
  up to three tariff columns, a link and a value to remember.
  - The whole card is the link, not just the button, and stays keyboard
    reachable with a visible focus ring.
  - Clicking a card writes its Stored Value to `localStorage`, a cookie or both
    under a configurable key, before the link is followed — either the bare
    value or a JSON payload that also carries the title and tariff amounts.
    Storage failures (private mode, disabled storage) never block the link.
  - Amounts are entered as plain digits and rendered grouped in thousands and
    in Persian numerals, both switchable; anything that is not a plain number
    passes through untouched.
  - One tariff column per card can be marked *Featured* for the accent colour
    VIP has in the design, and any column can be hidden.
  - Defaults reproduce the design exactly: 16px radius, the blue accent on the
    reading edge, the blue backdrop ellipse behind the vehicle, the round badge
    with a built-in car outline, hairline-separated tariff columns, and the
    text action with a built-in chevron. Everything is exposed as controls —
    grid columns and gap, card background, radius, shadow and hover lift, image
    and badge sizing, typography and colours for every text role, and the
    divider colours.
  - Sides use logical properties, so the accent edge and the badge sit on the
    reading side in both RTL and LTR.
- A front-end script, loaded only on pages with a widget that needs it, and in
  the editor preview.

## [0.5.1] - 2026-09-08

### Fixed

- Fixed height had no effect. The value was emitted as a CSS custom property
  and only turned into a real `height` by the mode class in the stylesheet, so
  it silently did nothing whenever that class did not match — including on
  every widget saved before 0.3.1, whose height mode is still called `custom`.
  Height and Minimum Height are now separate controls that write `height` and
  `min-height` directly, and `custom` is accepted as an alias of `fixed`, so
  older widgets keep their height without being re-configured.

## [0.5.0] - 2026-09-08

### Changed

- **The icon no longer sits in a wrapper `<span>`.** The `mdotcar-title__icon`
  class now rides on the icon element itself, which is a direct child of the
  box. The wrapper could not carry styling for uploaded SVGs anyway — Elementor
  prints raw `<svg>` markup that takes no class — so it only added a layer
  between the flex box and the icon.
- Icon styling therefore addresses both shapes: `.mdotcar-title > i` for font
  icons (which do take the class) and `.mdotcar-title > svg` for SVGs.
- Icon Size and Box Size now write the custom properties
  `--mdotcar-title-icon-size` and `--mdotcar-title-icon-box` on the box. The
  stylesheet turns the first into `font-size` for a font icon and into
  `width`/`height` for an SVG, which has no font-size of its own.
- Fixed-box mode moved to a `mdotcar-title--icon-box` class on the box. The
  icon element *is* the box: it takes the box size, and padding of half the
  difference between the two sizes keeps the glyph itself at the icon size.

### Migration

- Custom CSS or JavaScript targeting `span.mdotcar-title__icon` or
  `.mdotcar-title__icon--box` must be repointed at `.mdotcar-title > i`,
  `.mdotcar-title > svg` or `.mdotcar-title--icon-box`. Nothing in the panel
  changes: existing settings keep working.

## [0.4.1] - 2026-09-08

### Fixed

- The stylesheet never reached the Elementor editor's preview iframe: it was
  enqueued on `elementor/editor/after_enqueue_styles`, which loads assets into
  the panel document, not the preview where widgets actually render. It is now
  enqueued on `elementor/preview/enqueue_styles`.
- An SVG icon therefore fell back to the intrinsic size in its file (a 72×72
  file rendered 72×72) instead of the configured Icon Size. Beyond fixing the
  enqueue, Icon Size now also writes the SVG's `width`/`height` directly, so the
  size holds even where the stylesheet is missing or overridden, and the
  stylesheet caps the SVG at the icon box with `max-width`/`max-height`.

## [0.4.0] - 2026-09-08

### Added

- Title widget: **Link** control turning the whole box into an anchor, with
  Elementor's target and nofollow options.
- Title widget: **Icon Label**, a visually hidden text read by screen readers —
  an icon-only widget was previously invisible to them.
- Title widget: responsive **Text Align** for the title element, separate from
  Justify Content, using logical values so RTL follows the text direction.
- Title widget: **Clip to Box** for the icon box, so a shadow or an oversized
  glyph can spill out of the radius when wanted.
- `content_template()`: the editor now renders the widget in JavaScript, so the
  panel updates live and inline title editing works instead of round-tripping
  to the server on every keystroke.
- Translations: `languages/mdotcar-elementor.pot` and a complete Persian
  (`fa_IR`) translation of every panel string, generated by `bin/make-pot.py`
  and compiled by `bin/compile-mo.py`.
- Tooling and CI: `phpcs.xml.dist` (WordPress-Core + WordPress-Docs, i18n and
  prefix rules), a `composer.json` for the dev dependencies,
  `bin/check-version.sh` verifying the version matches in the header, the
  constant, `readme.txt` and both changelogs, and a GitHub Actions workflow
  running lint, PHPCS, the version check, a staleness check on the
  translations, and the release build.

### Changed

- Class files renamed to the WordPress convention that includes the full class
  name (`includes/class-mdotcar-elementor-plugin.php`, and so on); the
  autoloader maps class names directly rather than stripping the prefix.
- The whole codebase now passes PHPCS with no errors or warnings.

### Known limitation

- The direction-aware icons on the Justify/Align controls follow the **desktop**
  value of Direction. Setting a different direction for tablet or mobile still
  produces the right CSS, but the panel keeps the desktop axis icons. Elementor's
  own container behaves the same way; changing it needs editor-side JavaScript.

## [0.3.1] - 2026-09-08

### Fixed

- Title widget: a custom height no longer traps the content. The control set
  `height` and `min-height` to the same value, so a wrapping title overflowed
  the box instead of growing it. Height is now a mode — *Fit to content*,
  *Minimum (grows with content)* or *Fixed* — and the value feeds a CSS
  variable the mode class turns into `min-height` or `height`.

## [0.3.0] - 2026-09-08

### Added

- Title widget: **Fixed Box** mode for the icon. When on, the icon takes an
  explicit width and height (Box Size, responsive) with the glyph centred
  inside, plus its own radius and background. Because the cross size is then
  explicit, `align-items: stretch` on the flex box can no longer distort the
  icon, and every icon lines up on the same size regardless of glyph metrics.

### Fixed

- Title widget: the icon's vertical centring no longer depends on the leading
  that font icons carry — the wrapper pins `line-height: 1` and centres its
  child, and the icon is `flex: 0 0 auto` so the flex box cannot stretch or
  shrink it.

### Changed

- Icon Size now sets only the wrapper's `font-size`; the stylesheet scales both
  font icons and SVGs (`1em`) from it, so the two no longer risk drifting apart
  from two separate CSS declarations of the same value.

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
