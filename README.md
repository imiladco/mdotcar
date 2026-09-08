# mdotcar

WordPress plugin work for [mdotcar.com](https://mdotcar.com).

## `mdotcar-mentoring/`

The Mentoring plugin. Copy or symlink this directory into `wp-content/plugins/`.

```
mdotcar-mentoring/
├── mdotcar-mentoring.php   # plugin header, constants, bootstrap
├── uninstall.php           # option cleanup on delete
├── includes/               # requirements, autoloader, plugin controller, installer
├── widgets/                # WP_Widget subclasses (registered via the widgets filter)
├── assets/css|js/          # front-end assets, versioned for cache busting
├── languages/              # .pot / .po / .mo (text domain: mdotcar-mentoring)
├── bin/bump-version.sh     # updates every place the version is declared
├── readme.txt              # WordPress.org-style readme (Stable tag)
└── CHANGELOG.md            # Keep a Changelog / SemVer history
```

### Adding a widget

1. Create `widgets/class-widget-<slug>.php` with class
   `MDotCar_Mentoring_Widget_<Slug>` extending `WP_Widget` (the autoloader maps
   underscores to dashes).
2. Register it:

   ```php
   add_filter( 'mdotcar_mentoring_widgets', function ( $widgets ) {
       $widgets[] = 'MDotCar_Mentoring_Widget_Mentor';
       return $widgets;
   } );
   ```

3. Enqueue `mdotcar-mentoring` (style/script) inside the widget's `widget()`
   method so only pages using it load the assets.

### Versioning

Semantic Versioning. The version appears in the plugin header, the
`MDOTCAR_MENTORING_VERSION` constant and `readme.txt`'s `Stable tag` — never
edit them by hand:

```bash
mdotcar-mentoring/bin/bump-version.sh 0.2.0
```

Then add the release section to `CHANGELOG.md` and `readme.txt`. Data
migrations belong on the `mdotcar_mentoring_upgrade` action, which fires once
when the stored version differs from the shipped one.
