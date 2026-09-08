<?php
/**
 * Activation, deactivation and version upgrades.
 *
 * @package MDotCar\Elementor
 */

defined( 'ABSPATH' ) || exit;

/**
 * Owns the stored plugin version so upgrade routines run exactly once per release.
 */
class MDotCar_Elementor_Installer {

	/** Option holding the installed (last booted) plugin version. */
	const VERSION_OPTION = 'mdotcar_elementor_version';

	/** Option holding the version present at first install; never overwritten. */
	const INSTALLED_AT_OPTION = 'mdotcar_elementor_installed_version';

	/**
	 * Runs on activation.
	 */
	public static function activate() {
		if ( ! get_option( self::INSTALLED_AT_OPTION ) ) {
			add_option( self::INSTALLED_AT_OPTION, MDOTCAR_ELEMENTOR_VERSION );
		}

		self::maybe_upgrade();
	}

	/**
	 * Runs on deactivation. Data is intentionally kept; uninstall.php removes it.
	 */
	public static function deactivate() {
		self::clear_elementor_cache();
	}

	/**
	 * Compares the stored version with the shipped one, runs the upgrade steps in
	 * between, then records the new version. Elementor's generated CSS is
	 * regenerated so widget style changes in a release take effect.
	 */
	public static function maybe_upgrade() {
		$stored  = (string) get_option( self::VERSION_OPTION, '' );
		$current = MDOTCAR_ELEMENTOR_VERSION;

		if ( $stored === $current ) {
			return;
		}

		/**
		 * Fires when the plugin boots at a version other than the stored one.
		 *
		 * @param string $stored  Previously stored version ('' on a fresh install).
		 * @param string $current Version being installed.
		 */
		do_action( 'mdotcar_elementor_upgrade', $stored, $current );

		self::clear_elementor_cache();

		update_option( self::VERSION_OPTION, $current );
	}

	/**
	 * Flushes Elementor's generated CSS files, if Elementor is available.
	 */
	private static function clear_elementor_cache() {
		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
	}
}
