=== MDotCar Elementor Widgets ===
Contributors: claude
Tags: elementor, widget, title, heading, flexbox
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 0.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Custom Elementor widgets for mdotcar.com, starting with the Title widget.

== Description ==

Adds an **MDotCar** category to the Elementor panel.

**MDotCar Title (Style 1)** — a title with an icon, laid out with the complete
set of flexbox controls: display, direction, wrap, align content, justify
content, align items and gap. Justify and align controls swap their icons
between the horizontal and vertical sets as the direction changes, so the icons
always describe the axis they act on. Every layout control is responsive.

Styling covers font size and typography, icon size and color, padding, border,
radius and shadow, plus hover states for border, background and shadow. Width
and height can each be left to fit the content or set to a custom value.

== Installation ==

1. Install and activate Elementor 3.5 or newer.
2. Upload the `mdotcar-elementor` folder to `/wp-content/plugins/`, or upload
   the release ZIP through *Plugins > Add New > Upload Plugin*.
3. Activate the plugin; the widgets appear in the Elementor panel under
   *MDotCar*.

== Changelog ==

= 0.2.0 =
* Reworked the plugin as an Elementor widget pack (`mdotcar-elementor`); the
  0.1.x scaffold targeted classic WordPress widgets under the wrong name.
* Added the MDotCar Title widget (Style 1): title + icon with the full flexbox
  control set, direction-aware control icons, sizing, spacing, border, radius,
  shadow and hover states, all responsive.

= 0.1.1 =
* Plugin files moved to the archive root so the ZIP installs directly in WordPress.
* Added a build script that produces a correctly structured release ZIP.

= 0.1.0 =
* Initial plugin scaffold: requirements check, autoloader, installer with
  version-aware upgrade routine, asset registration, widget pipeline,
  translations and RTL support.
