<?php
/**
 * Plugin Name:       MDotCar Elementor Widgets
 * Plugin URI:        https://mdotcar.com/
 * Description:       Custom Elementor widgets for mdotcar.com, starting with the Title widget.
 * Version:           0.4.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Requires Plugins:  elementor
 * Elementor tested up to: 3.28.0
 * Elementor Pro tested up to: 3.28.0
 * Author:            claude
 * Author URI:        https://mdotcar.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mdotcar-elementor
 * Domain Path:       /languages
 *
 * @package MDotCar\Elementor
 */

defined( 'ABSPATH' ) || exit;

/**
 * Single source of truth for the plugin version (semver).
 * Bump with bin/bump-version.sh; never edit by hand.
 */
define( 'MDOTCAR_ELEMENTOR_VERSION', '0.4.1' );

/** Minimum environment the plugin supports. */
define( 'MDOTCAR_ELEMENTOR_MIN_PHP', '7.4' );
define( 'MDOTCAR_ELEMENTOR_MIN_WP', '6.0' );
define( 'MDOTCAR_ELEMENTOR_MIN_ELEMENTOR', '3.5.0' );

define( 'MDOTCAR_ELEMENTOR_FILE', __FILE__ );
define( 'MDOTCAR_ELEMENTOR_DIR', plugin_dir_path( __FILE__ ) );
define( 'MDOTCAR_ELEMENTOR_URL', plugin_dir_url( __FILE__ ) );
define( 'MDOTCAR_ELEMENTOR_BASENAME', plugin_basename( __FILE__ ) );

require_once MDOTCAR_ELEMENTOR_DIR . 'includes/class-mdotcar-elementor-requirements.php';
require_once MDOTCAR_ELEMENTOR_DIR . 'includes/class-mdotcar-elementor-installer.php';

/**
 * Boots the plugin once the environment has been validated.
 */
function mdotcar_elementor_bootstrap() {
	$requirements = new MDotCar_Elementor_Requirements(
		MDOTCAR_ELEMENTOR_MIN_PHP,
		MDOTCAR_ELEMENTOR_MIN_WP,
		MDOTCAR_ELEMENTOR_MIN_ELEMENTOR
	);

	if ( ! $requirements->is_satisfied() ) {
		add_action( 'admin_notices', array( $requirements, 'render_notice' ) );
		return;
	}

	require_once MDOTCAR_ELEMENTOR_DIR . 'includes/class-mdotcar-elementor-autoloader.php';
	MDotCar_Elementor_Autoloader::register();

	MDotCar_Elementor_Plugin::instance()->run();
}
add_action( 'plugins_loaded', 'mdotcar_elementor_bootstrap' );

register_activation_hook( __FILE__, array( 'MDotCar_Elementor_Installer', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'MDotCar_Elementor_Installer', 'deactivate' ) );
