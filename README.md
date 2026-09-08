# MDotCar Elementor Widgets

Elementor widgets for [mdotcar.com](https://mdotcar.com).

**This repository *is* the plugin** — `mdotcar-elementor.php` sits at the root, so
a downloaded archive installs straight into WordPress.

## Installation

Either build a release ZIP:

```bash
bin/build-zip.sh          # -> dist/mdotcar-elementor-<version>.zip
```

and upload it via *Plugins → Add New → Upload Plugin*; or clone/copy the
repository into `wp-content/plugins/mdotcar-elementor/`.

## Layout

```
mdotcar-elementor.php   # plugin header, constants, bootstrap
uninstall.php           # option cleanup on delete
includes/               # requirements, autoloader, plugin controller, installer
widgets/                # Elementor widget classes
assets/css/             # front-end styles, versioned for cache busting
languages/              # .pot / .po / .mo (text domain: mdotcar-elementor)
readme.txt              # WordPress.org-style readme (Stable tag)
CHANGELOG.md            # Keep a Changelog / SemVer history
bin/                    # dev tooling, excluded from the release ZIP
```

File names follow the WordPress convention of carrying the full class name, so
`MDotCar_Elementor_Widget_Title` lives in
`widgets/class-mdotcar-elementor-widget-title.php`.

## Development

```bash
composer install          # PHPCS + the WordPress standards
composer lint             # or: vendor/bin/phpcs
composer lint:fix         # vendor/bin/phpcbf
bin/check-version.sh      # version matches header, constant, readme, changelogs
python3 bin/make-pot.py   # regenerate languages/mdotcar-elementor.pot
python3 bin/compile-mo.py # compile languages/*.po into .mo
```

CI (`.github/workflows/ci.yml`) runs all of the above plus the release build on
every push. Add a translated string to `languages/mdotcar-elementor-fa_IR.po`
and recompile whenever a new panel label is introduced.

## Widgets

| Widget | Class | Panel |
| --- | --- | --- |
| MDotCar Title | `MDotCar_Elementor_Widget_Title` | MDotCar → MDotCar Title |

### Adding a widget

1. Create `widgets/class-widget-<slug>.php` with class
   `MDotCar_Elementor_Widget_<Slug>` extending `\Elementor\Widget_Base` (the
   autoloader maps underscores to dashes).
2. Add it to the list in `MDotCar_Elementor_Plugin::register_widgets()`, or from
   another plugin:

   ```php
   add_filter( 'mdotcar_elementor_widgets', function ( $widgets ) {
       $widgets[] = 'MDotCar_Elementor_Widget_Badge';
       return $widgets;
   } );
   ```

3. Return `MDotCar_Elementor_Plugin::HANDLE` from `get_style_depends()` so the
   stylesheet loads only on pages using the widget, and add a
   `content_template()` so the Elementor editor previews it without a server
   round-trip.

## Versioning

Semantic Versioning. The version appears in the plugin header, the
`MDOTCAR_MENTORING_VERSION` constant and `readme.txt`'s `Stable tag` — never
edit them by hand:

```bash
bin/bump-version.sh 0.2.0
```

Then add the release section to `CHANGELOG.md` and `readme.txt`. Data
migrations belong on the `mdotcar_elementor_upgrade` action, which fires once
when the stored version differs from the shipped one.
