<?php
/**
 * Main plugin controller.
 *
 * @package MDotCar\Elementor
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wires the plugin into Elementor: widget category, widget registration and assets.
 */
final class MDotCar_Elementor_Plugin {

	/** Elementor widget category slug used by every widget in this plugin. */
	const CATEGORY = 'mdotcar';

	/** Handle shared by the front-end stylesheet and script. */
	const HANDLE = 'mdotcar-elementor';

	/**
	 * Shared instance.
	 *
	 * @var MDotCar_Elementor_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Whether run() has already registered the hooks.
	 *
	 * @var bool
	 */
	private $booted = false;

	/**
	 * Returns the shared instance.
	 *
	 * @return MDotCar_Elementor_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Private: the plugin is reached through instance().
	 */
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
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
		add_action( 'elementor/frontend/after_register_styles', array( $this, 'register_assets' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue_editor_assets' ) );
		add_action( 'admin_init', array( 'MDotCar_Elementor_Installer', 'maybe_upgrade' ) );
	}

	/**
	 * Loads translations (fa_IR and friends) from /languages.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'mdotcar-elementor',
			false,
			dirname( MDOTCAR_ELEMENTOR_BASENAME ) . '/languages'
		);
	}

	/**
	 * Adds the "MDotCar" panel category.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
	 */
	public function register_category( $elements_manager ) {
		$elements_manager->add_category(
			self::CATEGORY,
			array(
				'title' => __( 'MDotCar', 'mdotcar-elementor' ),
				'icon'  => 'eicon-font',
			)
		);
	}

	/**
	 * Registers the plugin's Elementor widgets.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 */
	public function register_widgets( $widgets_manager ) {
		/**
		 * Filters the widget classes registered by the plugin.
		 *
		 * @param string[] $widgets Class names extending \Elementor\Widget_Base.
		 */
		$widgets = apply_filters(
			'mdotcar_elementor_widgets',
			array( 'MDotCar_Elementor_Widget_Title' )
		);

		foreach ( $widgets as $widget ) {
			if ( class_exists( $widget ) ) {
				$widgets_manager->register( new $widget() );
			}
		}
	}

	/**
	 * Registers the front-end stylesheet. Widgets depend on it through
	 * get_style_depends(), so it only loads on pages that use them.
	 */
	public function register_assets() {
		wp_register_style(
			self::HANDLE,
			MDOTCAR_ELEMENTOR_URL . 'assets/css/mdotcar-elementor.css',
			array(),
			MDOTCAR_ELEMENTOR_VERSION
		);

		wp_style_add_data( self::HANDLE, 'rtl', 'replace' );
	}

	/**
	 * Loads the same stylesheet inside the Elementor editor preview shell so
	 * widget previews match the front end.
	 */
	public function enqueue_editor_assets() {
		wp_enqueue_style(
			self::HANDLE . '-editor',
			MDOTCAR_ELEMENTOR_URL . 'assets/css/mdotcar-elementor.css',
			array(),
			MDOTCAR_ELEMENTOR_VERSION
		);
	}
}
