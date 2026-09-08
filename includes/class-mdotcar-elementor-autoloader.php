<?php
/**
 * Class autoloading for the plugin's `MDotCar_Elementor_*` classes.
 *
 * @package MDotCar\Elementor
 */

defined( 'ABSPATH' ) || exit;

/**
 * Maps class names to WordPress-style file names under includes/ and widgets/.
 *
 * MDotCar_Elementor_Widget_Title -> widgets/class-mdotcar-elementor-widget-title.php
 */
class MDotCar_Elementor_Autoloader {

	const PREFIX = 'MDotCar_Elementor_';

	/**
	 * Directories searched, in order.
	 *
	 * @var string[]
	 */
	private static $paths = array( 'includes/', 'widgets/' );

	/**
	 * Registers the autoloader with SPL.
	 */
	public static function register() {
		spl_autoload_register( array( __CLASS__, 'load' ) );
	}

	/**
	 * Requires the file holding the given class, if this plugin owns it.
	 *
	 * @param string $class_name Fully qualified class name being loaded.
	 */
	public static function load( $class_name ) {
		if ( 0 !== strpos( $class_name, self::PREFIX ) ) {
			return;
		}

		$file = 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';

		foreach ( self::$paths as $path ) {
			$candidate = MDOTCAR_ELEMENTOR_DIR . $path . $file;

			if ( is_readable( $candidate ) ) {
				require_once $candidate;
				return;
			}
		}
	}
}
