<?php
/**
 * Main plugin controller.
 *
 * @package MDotCar\Mentoring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wires the plugin's hooks. Widgets register themselves through `register_widgets()`.
 */
final class MDotCar_Mentoring_Plugin {

	/** @var MDotCar_Mentoring_Plugin|null */
	private static $instance = null;

	/** @var bool */
	private $booted = false;

	/**
	 * @return MDotCar_Mentoring_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {}

	/**
	 * Registers hooks. Safe to call more than once.
	 */
	public function run() {
		if ( $this->booted ) {
			return;
		}

		$this->booted = true;

		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'admin_init', array( MDotCar_Mentoring_Installer::class, 'maybe_upgrade' ) );
	}

	/**
	 * Loads translations (fa_IR and friends) from /languages.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'mdotcar-mentoring',
			false,
			dirname( MDOTCAR_MENTORING_BASENAME ) . '/languages'
		);
	}

	/**
	 * Registers the plugin's widgets.
	 *
	 * Widget classes are added here as they are implemented; each one must extend
	 * WP_Widget and live in widgets/ so the autoloader can find it.
	 */
	public function register_widgets() {
		/**
		 * Filters the widget classes registered by the plugin.
		 *
		 * @param string[] $widgets Class names extending WP_Widget.
		 */
		$widgets = apply_filters( 'mdotcar_mentoring_widgets', array() );

		foreach ( $widgets as $widget ) {
			if ( class_exists( $widget ) ) {
				register_widget( $widget );
			}
		}
	}

	/**
	 * Registers front-end assets. They are enqueued on demand by each widget so
	 * pages without the widget stay clean.
	 */
	public function register_assets() {
		$version = MDOTCAR_MENTORING_VERSION;

		wp_register_style(
			'mdotcar-mentoring',
			MDOTCAR_MENTORING_URL . 'assets/css/mentoring.css',
			array(),
			$version
		);

		wp_style_add_data( 'mdotcar-mentoring', 'rtl', 'replace' );

		wp_register_script(
			'mdotcar-mentoring',
			MDOTCAR_MENTORING_URL . 'assets/js/mentoring.js',
			array(),
			$version,
			true
		);
	}
}
