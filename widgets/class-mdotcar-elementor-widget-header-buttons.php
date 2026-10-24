<?php
/**
 * MDotCar Header Buttons widget.
 *
 * @package MDotCar\Elementor
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Plugin as Elementor;
use Elementor\Repeater;
use Elementor\Widget_Base;

/**
 * The header's buttons (call, sign in, …), each one a repeater row.
 *
 * A button can be shown on some pages only (or everywhere but some), carry
 * its own id, classes, aria-label and attributes, write any number of cookies
 * or localStorage / sessionStorage entries when it is clicked, show only its
 * icon on some devices, and take one of the site's looks (Accent: the yellow
 * call button; Outline: the grey sign-in one) with every value adjustable,
 * per device.
 */
class MDotCar_Elementor_Widget_Header_Buttons extends Widget_Base {

	/** Root of the widget's markup. */
	const ROOT = '{{WRAPPER}} .mdotcar-hbtns';

	/** One button (inside the repeater). */
	const ITEM = '{{WRAPPER}} .mdotcar-hbtns {{CURRENT_ITEM}}.mdotcar-hbtn';

	/**
	 * Widget key used in templates and the panel.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'mdotcar-header-buttons';
	}

	/**
	 * Label shown in the Elementor panel.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'MDotCar Header Buttons', 'mdotcar-elementor' );
	}

	/**
	 * Icon shown next to the widget in the panel.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-button';
	}

	/**
	 * Panel categories the widget belongs to.
	 *
	 * @return string[]
	 */
	public function get_categories() {
		return array( MDotCar_Elementor_Plugin::CATEGORY );
	}

	/**
	 * Keywords matched by the panel's search box.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array( 'button', 'header', 'call', 'login', 'cookie', 'mdotcar', 'دکمه', 'تماس', 'ورود' );
	}

	/**
	 * Styles enqueued only when the widget is on the page.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( MDotCar_Elementor_Plugin::HANDLE );
	}

	/**
	 * Scripts enqueued only when the widget is on the page.
	 *
	 * @return string[]
	 */
	public function get_script_depends() {
		return array( MDotCar_Elementor_Plugin::HANDLE );
	}

	/**
	 * Registers panel controls.
	 */
	protected function register_controls() {
		$this->register_buttons_controls();
		$this->register_layout_style_controls();
		$this->register_common_style_controls();
	}

	/*
	 * Content
	 */

	/**
	 * Content tab: the buttons.
	 */
	private function register_buttons_controls() {
		$this->start_controls_section(
			'section_buttons',
			array(
				'label' => __( 'Buttons', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$repeater = new Repeater();

		$repeater->start_controls_tabs( 'button_tabs' );

		// What it is.
		$repeater->start_controls_tab( 'tab_content', array( 'label' => __( 'Content', 'mdotcar-elementor' ) ) );
		$this->button_content_controls( $repeater );
		$repeater->end_controls_tab();

		// Where it shows, and what it stores.
		$repeater->start_controls_tab( 'tab_behaviour', array( 'label' => __( 'Behaviour', 'mdotcar-elementor' ) ) );
		$this->button_behaviour_controls( $repeater );
		$repeater->end_controls_tab();

		// How it looks.
		$repeater->start_controls_tab( 'tab_style', array( 'label' => __( 'Style', 'mdotcar-elementor' ) ) );
		$this->button_style_controls( $repeater );
		$repeater->end_controls_tab();

		$repeater->end_controls_tabs();

		$this->add_control(
			'buttons',
			array(
				'label'       => __( 'Buttons', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'text'       => '021-41610',
						'link'       => array( 'url' => 'tel:02141610' ),
						'look'       => 'accent',
						'html_id'    => 'call-btn-header',
						'css_class'  => 'kalameh-font',
						'aria'       => 'درخواست آنلاین',
						'attributes' => 'data-mdc-header-request|',
					),
					array(
						'text'      => 'ورود | ثبت‌نام',
						'link'      => array( 'url' => '/client/' ),
						'icon'      => array( 'url' => '/wp-content/uploads/2025/10/login.svg' ),
						'look'      => 'outline',
						'aria'      => 'ورود یا ثبت‌نام',
						'icon_only' => array( 'mobile' ),
					),
				),
				'title_field' => '{{{ text || aria || "—" }}}',
			)
		);

		$this->add_control(
			'editor_note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'In the editor every button shows; one that this page\'s conditions would hide is drawn faded.', 'mdotcar-elementor' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * A button's content: text, link, icon and its HTML.
	 *
	 * @param Repeater $r The buttons repeater.
	 */
	private function button_content_controls( Repeater $r ) {
		$r->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'default'     => __( 'Button', 'mdotcar-elementor' ),
				'label_block' => true,
			)
		);

		$r->add_control(
			'link',
			array(
				'label'       => __( 'Link', 'mdotcar-elementor' ),
				'description' => __( 'A page, or tel:, mailto:, https://wa.me/… and the like.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::URL,
				'dynamic'     => array( 'active' => true ),
				'placeholder' => 'tel:02141610',
			)
		);

		$r->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => array( 'active' => true ),
			)
		);

		$r->add_control(
			'icon_side',
			array(
				'label'   => __( 'Icon Position', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'start',
				'options' => array(
					'start' => __( 'Before the text', 'mdotcar-elementor' ),
					'end'   => __( 'After the text', 'mdotcar-elementor' ),
				),
			)
		);

		$r->add_control(
			'icon_only',
			array(
				'label'       => __( 'Only the Icon On', 'mdotcar-elementor' ),
				'description' => __( 'The text is hidden there (still read out to screen readers).', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => self::devices(),
				'default'     => array(),
			)
		);

		$r->add_control(
			'html_heading',
			array(
				'label'     => __( 'HTML', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$r->add_control(
			'html_id',
			array(
				'label'       => __( 'ID', 'mdotcar-elementor' ),
				'description' => __( 'Unique on the page: with the same widget in a desktop and a mobile header, give each its own.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'call-btn-header',
			)
		);

		$r->add_control(
			'css_class',
			array(
				'label'       => __( 'Classes', 'mdotcar-elementor' ),
				'description' => __( 'Separated by spaces, e.g. kalameh-font for the site\'s heading font.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::TEXT,
			)
		);

		$r->add_control(
			'aria',
			array(
				'label' => __( 'Name for Screen Readers (aria-label)', 'mdotcar-elementor' ),
				'type'  => Controls_Manager::TEXT,
			)
		);

		$r->add_control(
			'attributes',
			array(
				'label'       => __( 'Attributes', 'mdotcar-elementor' ),
				'description' => __( 'One per line: name|value (name alone for an empty one), e.g. data-mdc-header-request|. Event handlers (on…) are not allowed.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
			)
		);
	}

	/**
	 * A button's behaviour: on which pages and devices, and what a click stores.
	 *
	 * @param Repeater $r The buttons repeater.
	 */
	private function button_behaviour_controls( Repeater $r ) {
		$r->add_control(
			'show_mode',
			array(
				'label'   => __( 'Show On', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'all',
				'options' => array(
					'all'    => __( 'Every page', 'mdotcar-elementor' ),
					'only'   => __( 'Only these pages', 'mdotcar-elementor' ),
					'except' => __( 'Every page except these', 'mdotcar-elementor' ),
				),
			)
		);

		$r->add_control(
			'show_pages',
			array(
				'label'       => __( 'Pages', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => self::page_options(),
				'condition'   => array( 'show_mode!' => 'all' ),
			)
		);

		$r->add_control(
			'show_paths',
			array(
				'label'       => __( 'Addresses', 'mdotcar-elementor' ),
				'description' => __( 'One per line; * matches anything, e.g. /client/* or /blog/*. Compared with the address\'s path.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'placeholder' => "/pricing/\n/blog/*",
				'condition'   => array( 'show_mode!' => 'all' ),
			)
		);

		$r->add_control(
			'hide_on',
			array(
				'label'       => __( 'Hide On', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => self::devices(),
				'default'     => array(),
				'separator'   => 'before',
			)
		);

		$r->add_control(
			'store',
			array(
				'label'       => __( 'Store on Click', 'mdotcar-elementor' ),
				'description' => __( 'One per line: where | name | value | days. Where: cookie, local (localStorage) or session (sessionStorage); days for a cookie only (30 if left out; 0 for until the browser closes). The value may use {url}, {path}, {title}, {referrer}, {time} (now, in ms) and {text} (the button\'s).', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 4,
				'placeholder' => "cookie | mdc_source | header | 30\nlocal | mdc_last_page | {path}",
				'separator'   => 'before',
			)
		);
	}

	/**
	 * A button's look: a preset, then anything adjusted per device.
	 *
	 * @param Repeater $r The buttons repeater.
	 */
	private function button_style_controls( Repeater $r ) {
		$r->add_control(
			'look',
			array(
				'label'       => __( 'Look', 'mdotcar-elementor' ),
				'description' => __( 'A starting point; the settings below change it.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'outline',
				'options'     => array(
					'accent'  => __( 'Accent (the yellow call button)', 'mdotcar-elementor' ),
					'outline' => __( 'Outline (the grey sign-in button)', 'mdotcar-elementor' ),
					'solid'   => __( 'Filled (indigo)', 'mdotcar-elementor' ),
					'plain'   => __( 'None', 'mdotcar-elementor' ),
				),
			)
		);

		$r->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'typography',
				'selector' => self::ITEM,
			)
		);

		foreach ( array(
			'color'  => array( __( 'Text Color', 'mdotcar-elementor' ), 'color' ),
			'bg'     => array( __( 'Background', 'mdotcar-elementor' ), 'background-color' ),
			'border' => array( __( 'Border Color', 'mdotcar-elementor' ), 'border-color' ),
		) as $key => $def ) {
			$r->add_responsive_control(
				$key,
				array(
					'label'     => $def[0],
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( self::ITEM => $def[1] . ': {{VALUE}} !important;' ),
				)
			);
		}

		foreach ( array(
			'hover_color'  => array( __( 'Hover: Text Color', 'mdotcar-elementor' ), 'color' ),
			'hover_bg'     => array( __( 'Hover: Background', 'mdotcar-elementor' ), 'background-color' ),
			'hover_border' => array( __( 'Hover: Border Color', 'mdotcar-elementor' ), 'border-color' ),
		) as $key => $def ) {
			$r->add_control(
				$key,
				array(
					'label'     => $def[0],
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( self::ITEM . ':hover, ' . self::ITEM . ':focus-visible' => $def[1] . ': {{VALUE}} !important;' ),
				)
			);
		}

		$r->add_responsive_control(
			'border_width',
			array(
				'label'      => __( 'Border Width', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 6 ) ),
				'selectors'  => array( self::ITEM => 'border-width: {{SIZE}}{{UNIT}} !important; border-style: solid !important;' ),
			)
		);

		$r->add_responsive_control(
			'radius',
			array(
				'label'      => __( 'Radius', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 40 ) ),
				'selectors'  => array( self::ITEM => 'border-radius: {{SIZE}}{{UNIT}} !important;' ),
			)
		);

		$r->add_responsive_control(
			'padding',
			array(
				'label'      => __( 'Padding', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'selectors'  => array( self::ITEM => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;' ),
			)
		);

		$r->add_responsive_control(
			'icon_size',
			array(
				'label'      => __( 'Icon Size', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 8,
						'max' => 48,
					),
				),
				'selectors'  => array( self::ITEM => '--mdotcar-hbtn-icon: {{SIZE}}{{UNIT}};' ),
			)
		);

		$r->add_responsive_control(
			'icon_tone',
			array(
				'label'                => __( 'Icon Color', 'mdotcar-elementor' ),
				'description'          => __( 'White turns any icon white (for a filled button).', 'mdotcar-elementor' ),
				'type'                 => Controls_Manager::SELECT,
				'options'              => array(
					''      => __( 'As the look has it', 'mdotcar-elementor' ),
					'none'  => __( 'Its own colours', 'mdotcar-elementor' ),
					'white' => __( 'White', 'mdotcar-elementor' ),
					'gray'  => __( 'Gray', 'mdotcar-elementor' ),
				),
				'selectors_dictionary' => array(
					'none'  => 'none',
					'white' => 'brightness(0) invert(1)',
					'gray'  => 'grayscale(1)',
				),
				'selectors'            => array( self::ITEM . ' .mdotcar-hbtn__icon' => 'filter: {{VALUE}} !important;' ),
			)
		);

		$r->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'shadow',
				'selector' => self::ITEM,
			)
		);
	}

	/*
	 * Style
	 */

	/**
	 * Style tab: how the buttons sit together.
	 */
	private function register_layout_style_controls() {
		$this->start_controls_section(
			'section_style_layout',
			array(
				'label' => __( 'Layout', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'gap',
			array(
				'label'      => __( 'Gap Between Buttons', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 48 ) ),
				'selectors'  => array( self::ROOT => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'     => __( 'Alignment', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Start', 'mdotcar-elementor' ),
						'icon'  => 'eicon-h-align-right',
					),
					'center'     => array(
						'title' => __( 'Center', 'mdotcar-elementor' ),
						'icon'  => 'eicon-h-align-center',
					),
					'flex-end'   => array(
						'title' => __( 'End', 'mdotcar-elementor' ),
						'icon'  => 'eicon-h-align-left',
					),
				),
				'selectors' => array( self::ROOT => 'justify-content: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'valign',
			array(
				'label'     => __( 'Vertical Alignment', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Top', 'mdotcar-elementor' ),
						'icon'  => 'eicon-v-align-top',
					),
					'center'     => array(
						'title' => __( 'Middle', 'mdotcar-elementor' ),
						'icon'  => 'eicon-v-align-middle',
					),
					'stretch'    => array(
						'title' => __( 'Same Height', 'mdotcar-elementor' ),
						'icon'  => 'eicon-v-align-stretch',
					),
				),
				'selectors' => array( self::ROOT => 'align-items: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: what all the buttons share (a button's own Style tab wins over it).
	 */
	private function register_common_style_controls() {
		$this->start_controls_section(
			'section_style_common',
			array(
				'label' => __( 'All Buttons', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'common_typography',
				'selector' => self::ROOT . ' .mdotcar-hbtn',
			)
		);

		$this->add_responsive_control(
			'common_gap',
			array(
				'label'      => __( 'Gap Between Icon and Text', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 32 ) ),
				'selectors'  => array( self::ROOT . ' .mdotcar-hbtn' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'common_height',
			array(
				'label'      => __( 'Minimum Height', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 24,
						'max' => 80,
					),
				),
				'selectors'  => array( self::ROOT . ' .mdotcar-hbtn' => 'min-height: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'common_transition',
			array(
				'label'      => __( 'Hover Transition (ms)', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 1000 ) ),
				'selectors'  => array( self::ROOT . ' .mdotcar-hbtn' => 'transition-duration: {{SIZE}}ms;' ),
			)
		);

		$this->end_controls_section();
	}

	/*
	 * Data
	 */

	/**
	 * Devices, as Elementor's default breakpoints.
	 *
	 * @return string[]
	 */
	private static function devices() {
		return array(
			'desktop' => __( 'Desktop', 'mdotcar-elementor' ),
			'tablet'  => __( 'Tablet', 'mdotcar-elementor' ),
			'mobile'  => __( 'Mobile', 'mdotcar-elementor' ),
		);
	}

	/**
	 * What a condition can name: kinds of page, then every published page.
	 *
	 * @return string[]
	 */
	private static function page_options() {
		$options = array(
			'front'   => __( '— Home page', 'mdotcar-elementor' ),
			'blog'    => __( '— Posts page (blog)', 'mdotcar-elementor' ),
			'single'  => __( '— Any post', 'mdotcar-elementor' ),
			'archive' => __( '— Archives (categories, tags…)', 'mdotcar-elementor' ),
			'search'  => __( '— Search results', 'mdotcar-elementor' ),
			'404'     => __( '— Not found (404)', 'mdotcar-elementor' ),
		);
		foreach ( get_pages(
			array(
				'number'      => 500,
				'sort_column' => 'post_title',
			)
		) as $page ) {
			$options[ (string) $page->ID ] = $page->post_title ? $page->post_title : '#' . $page->ID;
		}
		return $options;
	}

	/**
	 * Whether the page being viewed is one a button's condition names.
	 *
	 * @param array $row Button.
	 * @return bool
	 */
	private static function page_matches( array $row ) {
		$queried = get_queried_object_id();
		foreach ( (array) ( isset( $row['show_pages'] ) ? $row['show_pages'] : array() ) as $page ) {
			switch ( (string) $page ) {
				case 'front':
					$hit = is_front_page();
					break;
				case 'blog':
					$hit = is_home();
					break;
				case 'single':
					$hit = is_singular( 'post' );
					break;
				case 'archive':
					$hit = is_archive();
					break;
				case 'search':
					$hit = is_search();
					break;
				case '404':
					$hit = is_404();
					break;
				default:
					$hit = $queried && (int) $page === $queried;
			}
			if ( $hit ) {
				return true;
			}
		}

		$request = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '/';
		$request = untrailingslashit( rawurldecode( (string) $request ) );
		foreach ( preg_split( '/\R/', (string) ( isset( $row['show_paths'] ) ? $row['show_paths'] : '' ) ) as $line ) {
			if ( '' === trim( $line ) ) {
				continue;
			}
			// A full address counts by its path; "/" is the home address.
			$path    = wp_parse_url( trim( $line ), PHP_URL_PATH );
			$pattern = untrailingslashit( (string) ( null === $path ? trim( $line ) : $path ) );
			$regex   = '#^' . str_replace( '\*', '.*', preg_quote( $pattern, '#' ) ) . '$#u';
			if ( preg_match( $regex, $request ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Whether a button shows on the page being viewed.
	 *
	 * @param array $row Button.
	 * @return bool
	 */
	private static function shows_here( array $row ) {
		$mode = isset( $row['show_mode'] ) ? $row['show_mode'] : 'all';
		if ( 'all' === $mode ) {
			return true;
		}
		$hit = self::page_matches( $row );
		return 'only' === $mode ? $hit : ! $hit;
	}

	/**
	 * A button's "store on click" lines, checked, for the script.
	 *
	 * @param string $text Lines: where | name | value | days.
	 * @return array[]
	 */
	private static function store_entries( $text ) {
		$out = array();
		foreach ( preg_split( '/\R/', (string) $text ) as $line ) {
			if ( '' === trim( $line ) ) {
				continue;
			}
			$parts = array_map( 'trim', explode( '|', $line ) );
			$where = strtolower( $parts[0] );
			$where = 'localstorage' === $where ? 'local' : ( 'sessionstorage' === $where ? 'session' : $where );
			if ( ! in_array( $where, array( 'cookie', 'local', 'session' ), true ) || empty( $parts[1] ) ) {
				continue;
			}
			// The value is the rest of the line (it may hold a |); a cookie's last part is its days when a number.
			$rest = array_slice( $parts, 2 );
			$days = 30;
			if ( 'cookie' === $where && count( $rest ) > 1 && is_numeric( end( $rest ) ) ) {
				$days = (float) array_pop( $rest );
			}
			$entry = array(
				't' => $where,
				'k' => $parts[1],
				'v' => implode( '|', $rest ),
			);
			if ( 'cookie' === $where ) {
				$entry['d'] = $days;
			}
			$out[] = $entry;
		}
		return $out;
	}

	/**
	 * A button's extra attributes, checked: no event handlers, nothing the other fields set.
	 *
	 * @param string $text Lines: name|value.
	 * @return string[] name => value
	 */
	private static function extra_attributes( $text ) {
		$out = array();
		foreach ( preg_split( '/\R/', (string) $text ) as $line ) {
			if ( '' === trim( $line ) ) {
				continue;
			}
			$parts = explode( '|', $line, 2 );
			$name  = strtolower( trim( $parts[0] ) );
			if ( ! preg_match( '/^[a-z_:][a-z0-9_.:-]*$/', $name ) || 0 === strpos( $name, 'on' ) || in_array( $name, array( 'href', 'id', 'class', 'style', 'target', 'rel', 'aria-label' ), true ) ) {
				continue;
			}
			$out[ $name ] = isset( $parts[1] ) ? trim( $parts[1] ) : '';
		}
		return $out;
	}

	/*
	 * Render
	 */

	/**
	 * Renders the widget.
	 */
	protected function render() {
		MDotCar_Elementor_Plugin::enqueue();

		$s       = $this->get_settings_for_display();
		$editing = Elementor::$instance->editor->is_edit_mode();
		$html    = '';

		foreach ( (array) $s['buttons'] as $row ) {
			$here = self::shows_here( $row );
			if ( ! $here && ! $editing ) {
				continue;
			}
			$html .= $this->button( $row, $here );
		}

		if ( '' === $html ) {
			return;
		}

		echo '<div class="mdotcar-hbtns">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts.
	}

	/**
	 * One button's markup.
	 *
	 * @param array $row  Button.
	 * @param bool  $here Whether its conditions show it on this page.
	 * @return string
	 */
	private function button( array $row, $here ) {
		$look    = in_array( $row['look'], array( 'accent', 'outline', 'solid', 'plain' ), true ) ? $row['look'] : 'outline';
		$classes = array( 'mdotcar-hbtn', 'mdotcar-hbtn--' . $look, 'elementor-repeater-item-' . $row['_id'] );
		foreach ( (array) $row['hide_on'] as $device ) {
			$classes[] = 'mdotcar-hbtn--hide-' . sanitize_key( $device );
		}
		foreach ( (array) $row['icon_only'] as $device ) {
			$classes[] = 'mdotcar-hbtn--icon-only-' . sanitize_key( $device );
		}
		if ( ! $here ) {
			$classes[] = 'is-elsewhere';
		}
		foreach ( preg_split( '/\s+/', trim( (string) $row['css_class'] ) ) as $extra ) {
			if ( '' !== $extra ) {
				$classes[] = sanitize_html_class( $extra );
			}
		}

		$text  = trim( (string) $row['text'] );
		$label = trim( (string) $row['aria'] );
		$attrs = array( 'class' => implode( ' ', array_filter( $classes ) ) );
		if ( '' !== trim( (string) $row['html_id'] ) ) {
			$attrs['id'] = sanitize_html_class( trim( $row['html_id'] ) );
		}
		if ( '' !== $label ) {
			$attrs['aria-label'] = $label;
		} elseif ( $row['icon_only'] && '' !== $text ) {
			$attrs['aria-label'] = $text; // The text may be hidden: it is still its name.
		}
		$store = self::store_entries( $row['store'] );
		if ( $store ) {
			$attrs['data-mdotcar-sets'] = wp_json_encode( $store );
		}
		$attrs += self::extra_attributes( $row['attributes'] );

		$link = isset( $row['link'] ) ? $row['link'] : array();
		$tag  = empty( $link['url'] ) ? 'span' : 'a';
		if ( 'a' === $tag ) {
			$attrs['href'] = esc_url( $link['url'], array( 'http', 'https', 'tel', 'mailto', 'sms', 'whatsapp', 'tg' ) );
			if ( ! empty( $link['is_external'] ) ) {
				$attrs['target'] = '_blank';
			}
			$rel = array_merge( ! empty( $link['is_external'] ) ? array( 'noopener' ) : array(), ! empty( $link['nofollow'] ) ? array( 'nofollow' ) : array() );
			if ( $rel ) {
				$attrs['rel'] = implode( ' ', $rel );
			}
		} else {
			$attrs['role'] = 'button';
		}

		$out = '<' . $tag;
		foreach ( $attrs as $name => $value ) {
			$out .= ' ' . esc_attr( $name ) . ( '' === $value && 'href' !== $name ? '' : '="' . esc_attr( $value ) . '"' );
		}
		$out .= '>';

		$icon = ! empty( $row['icon']['url'] ) ? '<img class="mdotcar-hbtn__icon" src="' . esc_url( $row['icon']['url'] ) . '" alt="" width="20" height="20" aria-hidden="true">' : '';
		$span = '' !== $text ? '<span class="mdotcar-hbtn__text">' . esc_html( $text ) . '</span>' : '';
		$out .= 'end' === $row['icon_side'] ? $span . $icon : $icon . $span;

		return $out . '</' . $tag . '>';
	}
}
