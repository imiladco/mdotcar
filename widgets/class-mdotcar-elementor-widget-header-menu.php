<?php
/**
 * MDotCar Header Menu widget.
 *
 * @package MDotCar\Elementor
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Plugin as Elementor;
use Elementor\Repeater;
use Elementor\Widget_Base;

/**
 * The site's main menu, from one list of items: a bar with a mega menu on wide
 * screens, and a toggle with an off-canvas drawer on narrow ones.
 *
 * Items are one repeater: a row is a top item, or a sub item of the top item
 * above it. A top item with sub items opens a mega menu (cards with an icon
 * and a description) on desktop and an accordion in the drawer. The drawer's
 * own content around the list (logo, close, search, app buttons) comes from
 * two Elementor templates, so anything Elementor can build fits there.
 *
 * Behaviour (assets/js/mdotcar-elementor.js): the mega menu opens on hover with
 * a grace delay, or on click, and from the keyboard (its caret is a button);
 * Esc, a click outside and leaving close it. The drawer is a modal dialog:
 * focus moves in and is kept there, the page does not scroll behind it, and
 * Esc, the scrim, the close button or anything with the class
 * `mdotcar-menu-close` closes it.
 */
class MDotCar_Elementor_Widget_Header_Menu extends Widget_Base {

	/** Root of the widget's markup. */
	const ROOT = '{{WRAPPER}} .mdotcar-hmenu';

	/** The drawer, wherever the script moved it (it is portalled to <body>). */
	const DRAWER = '.mdotcar-hmenu-drawer[data-for="{{ID}}"]';

	/**
	 * Templates being rendered, so one that contains this widget cannot nest itself forever.
	 *
	 * @var int[]
	 */
	private static $rendering = array();

	/**
	 * Widget key used in templates and the panel.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'mdotcar-header-menu';
	}

	/**
	 * Label shown in the Elementor panel.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'MDotCar Header Menu', 'mdotcar-elementor' );
	}

	/**
	 * Icon shown next to the widget in the panel.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-nav-menu';
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
		return array( 'menu', 'nav', 'header', 'mega', 'drawer', 'mdotcar', 'منو', 'هدر', 'فهرست' );
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
		$this->register_items_controls();
		$this->register_mega_controls();
		$this->register_mobile_controls();
		$this->register_bar_style_controls();
		$this->register_mega_style_controls();
		$this->register_card_style_controls();
		$this->register_toggle_style_controls();
		$this->register_drawer_style_controls();
		$this->register_list_style_controls();
	}

	/*
	 * Content
	 */

	/**
	 * Content tab: the items.
	 */
	private function register_items_controls() {
		$this->start_controls_section(
			'section_items',
			array(
				'label' => __( 'Menu Items', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'level',
			array(
				'label'       => __( 'Level', 'mdotcar-elementor' ),
				'description' => __( 'A sub item belongs to the nearest top item above it.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'top',
				'options'     => array(
					'top' => __( 'Top item', 'mdotcar-elementor' ),
					'sub' => __( 'Sub item', 'mdotcar-elementor' ),
				),
			)
		);

		$repeater->add_control(
			'label',
			array(
				'label'       => __( 'Label', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'default'     => __( 'Item', 'mdotcar-elementor' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'mobile_label',
			array(
				'label'       => __( 'Label in the Drawer', 'mdotcar-elementor' ),
				'description' => __( 'Optional. Leave empty to use the same label.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'link',
			array(
				'label'       => __( 'Link', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::URL,
				'dynamic'     => array( 'active' => true ),
				'placeholder' => '/service/',
			)
		);

		$repeater->add_control(
			'icon',
			array(
				'label'       => __( 'Icon', 'mdotcar-elementor' ),
				'description' => __( 'A sub item: on its mega menu card. A top item: beside it in the drawer. An SVG works best.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::MEDIA,
				'dynamic'     => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'description',
			array(
				'label'     => __( 'Description', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::TEXTAREA,
				'dynamic'   => array( 'active' => true ),
				'rows'      => 3,
				'condition' => array( 'level' => 'sub' ),
			)
		);

		$repeater->add_control(
			'soon',
			array(
				'label'        => __( 'Coming Soon', 'mdotcar-elementor' ),
				'description'  => __( 'Shown faded with the "coming soon" note, and not a link.', 'mdotcar-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
			)
		);

		$repeater->add_control(
			'show_on',
			array(
				'label'   => __( 'Show On', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'both',
				'options' => array(
					'both'    => __( 'Bar and drawer', 'mdotcar-elementor' ),
					'desktop' => __( 'Bar only', 'mdotcar-elementor' ),
					'mobile'  => __( 'Drawer only', 'mdotcar-elementor' ),
				),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Items', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => self::default_items(),
				'title_field' => '{{{ "sub" === level ? "↳ " : "" }}}{{{ label }}}',
			)
		);

		$this->add_control(
			'soon_text',
			array(
				'label'   => __( 'Coming Soon Note', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '(به زودی)',
			)
		);

		$this->add_control(
			'nav_label',
			array(
				'label'   => __( 'Menu Name for Screen Readers', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => 'منوی اصلی',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Content tab: how the mega menu opens and where.
	 */
	private function register_mega_controls() {
		$this->start_controls_section(
			'section_mega',
			array(
				'label' => __( 'Mega Menu', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'mega_open',
			array(
				'label'       => __( 'Opens On', 'mdotcar-elementor' ),
				'description' => __( 'Hover: the label stays a link. Click: the label opens the menu. A touch screen always opens on the first tap.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'hover',
				'options'     => array(
					'hover' => __( 'Hover', 'mdotcar-elementor' ),
					'click' => __( 'Click', 'mdotcar-elementor' ),
				),
			)
		);

		$this->add_responsive_control(
			'mega_columns',
			array(
				'label'          => __( 'Columns', 'mdotcar-elementor' ),
				'type'           => Controls_Manager::NUMBER,
				'min'            => 1,
				'max'            => 6,
				'default'        => 3,
				'tablet_default' => 2,
				'selectors'      => array(
					self::ROOT . ' .mdotcar-hmenu__mega-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
				),
			)
		);

		$this->add_control(
			'mega_anchor',
			array(
				'label'       => __( 'Opens Below', 'mdotcar-elementor' ),
				'description' => __( 'The panel\'s top edge. "Header" is the header template, or the outermost container the widget is in.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'header',
				'options'     => array(
					'header' => __( 'The header', 'mdotcar-elementor' ),
					'bar'    => __( 'The menu bar', 'mdotcar-elementor' ),
				),
			)
		);

		$this->add_control(
			'mega_overlay',
			array(
				'label'        => __( 'Dim the Page Behind', 'mdotcar-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'editor_mega',
			array(
				'label'        => __( 'Keep Open in the Editor', 'mdotcar-elementor' ),
				'description'  => __( 'Only while editing, to style the cards.', 'mdotcar-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'separator'    => 'before',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Content tab: the toggle and the drawer.
	 */
	private function register_mobile_controls() {
		$this->start_controls_section(
			'section_mobile',
			array(
				'label' => __( 'Mobile Drawer', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'breakpoint',
			array(
				'label'   => __( 'Drawer From', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'tablet',
				'options' => array(
					'tablet' => __( 'Tablet and below (1024px)', 'mdotcar-elementor' ),
					'mobile' => __( 'Mobile only (767px)', 'mdotcar-elementor' ),
					'always' => __( 'Always', 'mdotcar-elementor' ),
				),
			)
		);

		$this->add_control(
			'toggle_icon',
			array(
				'label'       => __( 'Toggle Icon', 'mdotcar-elementor' ),
				'description' => __( 'Leave empty for the built-in one.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::MEDIA,
			)
		);

		$this->add_control(
			'toggle_label',
			array(
				'label'   => __( 'Toggle Name for Screen Readers', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => 'باز کردن منو',
			)
		);

		$this->add_control(
			'drawer_side',
			array(
				'label'   => __( 'Drawer Side', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::CHOOSE,
				'default' => 'right',
				'options' => array(
					'right' => array(
						'title' => __( 'Right', 'mdotcar-elementor' ),
						'icon'  => 'eicon-h-align-right',
					),
					'left'  => array(
						'title' => __( 'Left', 'mdotcar-elementor' ),
						'icon'  => 'eicon-h-align-left',
					),
				),
			)
		);

		$templates = self::templates();

		$this->add_control(
			'template_top',
			array(
				'label'       => __( 'Template Above the Menu', 'mdotcar-elementor' ),
				'description' => __( 'For the logo, a close button and the search. Saved in Templates → Saved Templates.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => $templates,
				'separator'   => 'before',
			)
		);

		$this->add_control(
			'template_bottom',
			array(
				'label'       => __( 'Template Below the Menu', 'mdotcar-elementor' ),
				'description' => __( 'For the app download and sign-in buttons.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => $templates,
			)
		);

		$this->add_control(
			'templates_note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Anything in the templates with the class mdotcar-menu-close closes the drawer (Advanced → CSS Classes).', 'mdotcar-elementor' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->add_control(
			'close_button',
			array(
				'label'        => __( 'Built-in Close Button', 'mdotcar-elementor' ),
				'description'  => __( 'Turn off when a template brings its own.', 'mdotcar-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'separator'    => 'before',
			)
		);

		$this->add_control(
			'close_label',
			array(
				'label'   => __( 'Close Name for Screen Readers', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => 'بستن منو',
			)
		);

		$this->add_control(
			'editor_drawer',
			array(
				'label'        => __( 'Show the Drawer in the Editor', 'mdotcar-elementor' ),
				'description'  => __( 'Only while editing, to style it.', 'mdotcar-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'separator'    => 'before',
			)
		);

		$this->end_controls_section();
	}

	/*
	 * Style
	 */

	/**
	 * Style tab: the bar.
	 */
	private function register_bar_style_controls() {
		$this->start_controls_section(
			'section_style_bar',
			array(
				'label' => __( 'Menu Bar', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'bar_gap',
			array(
				'label'      => __( 'Gap Between Items', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 120 ) ),
				'selectors'  => array( self::ROOT => '--mdotcar-hmenu-gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'bar_height',
			array(
				'label'       => __( 'Item Height', 'mdotcar-elementor' ),
				'description' => __( 'The height the mouse can hover in; usually the header\'s.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'range'       => array(
					'px' => array(
						'min' => 24,
						'max' => 160,
					),
				),
				'selectors'   => array( self::ROOT => '--mdotcar-hmenu-height: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'bar_align',
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
				'selectors' => array( self::ROOT . ' .mdotcar-hmenu__bar' => 'justify-content: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'bar_typography',
				'selector' => self::ROOT . ' .mdotcar-hmenu__link',
			)
		);

		$this->start_controls_tabs( 'bar_tabs' );

		foreach ( array(
			'normal' => __( 'Normal', 'mdotcar-elementor' ),
			'hover'  => __( 'Hover / Open', 'mdotcar-elementor' ),
			'active' => __( 'Current Page', 'mdotcar-elementor' ),
		) as $state => $label ) {
			$this->start_controls_tab( 'bar_tab_' . $state, array( 'label' => $label ) );
			$this->add_control(
				'bar_color_' . $state,
				array(
					'label'     => __( 'Color', 'mdotcar-elementor' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( self::ROOT => '--mdotcar-hmenu-color-' . $state . ': {{VALUE}};' ),
				)
			);
			if ( 'active' !== $state ) {
				$this->add_control(
					'caret_color_' . $state,
					array(
						'label'     => __( 'Arrow Color', 'mdotcar-elementor' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => array( self::ROOT => '--mdotcar-hmenu-caret-' . $state . ': {{VALUE}};' ),
					)
				);
			}
			$this->end_controls_tab();
		}

		$this->end_controls_tabs();

		$this->add_control(
			'indicator_color',
			array(
				'label'       => __( 'Open Indicator', 'mdotcar-elementor' ),
				'description' => __( 'The line under the item whose mega menu is open.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::COLOR,
				'separator'   => 'before',
				'selectors'   => array( self::ROOT => '--mdotcar-hmenu-indicator: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'last_divider',
			array(
				'label'        => __( 'Divider Before the Last Item', 'mdotcar-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'prefix_class' => 'mdotcar-hmenu-divider-',
			)
		);

		$this->add_control(
			'last_divider_color',
			array(
				'label'     => __( 'Divider Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array( 'last_divider' => 'yes' ),
				'selectors' => array( self::ROOT => '--mdotcar-hmenu-divider: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the mega menu panel.
	 */
	private function register_mega_style_controls() {
		$this->start_controls_section(
			'section_style_mega',
			array(
				'label' => __( 'Mega Menu Panel', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'mega_width',
			array(
				'label'      => __( 'Content Width', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 300,
						'max' => 1600,
					),
				),
				'selectors'  => array( self::ROOT => '--mdotcar-mega-width: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'mega_bg',
			array(
				'label'     => __( 'Background', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::ROOT => '--mdotcar-mega-bg: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'mega_padding',
			array(
				'label'      => __( 'Padding', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'selectors'  => array( self::ROOT . ' .mdotcar-hmenu__mega' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'mega_radius',
			array(
				'label'      => __( 'Radius', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'selectors'  => array( self::ROOT . ' .mdotcar-hmenu__mega' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'mega_shadow',
				'selector' => self::ROOT . ' .mdotcar-hmenu__mega',
			)
		);

		$this->add_responsive_control(
			'mega_gap',
			array(
				'label'      => __( 'Gap Between Cards', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 64 ) ),
				'selectors'  => array( self::ROOT . ' .mdotcar-hmenu__mega-grid' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'mega_overlay_color',
			array(
				'label'     => __( 'Page Dim Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array( 'mega_overlay' => 'yes' ),
				'selectors' => array( self::ROOT => '--mdotcar-mega-dim: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the cards in the mega menu.
	 */
	private function register_card_style_controls() {
		$this->start_controls_section(
			'section_style_card',
			array(
				'label' => __( 'Mega Menu Cards', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => __( 'Padding', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'selectors'  => array( self::ROOT . ' .mdotcar-hmenu__card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'card_radius',
			array(
				'label'      => __( 'Radius', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 40 ) ),
				'selectors'  => array( self::ROOT . ' .mdotcar-hmenu__card' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'card_hover_bg',
			array(
				'label'     => __( 'Hover Background', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::ROOT => '--mdotcar-card-hover-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'card_title_heading',
			array(
				'label'     => __( 'Title', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'card_title_typography',
				'selector' => self::ROOT . ' .mdotcar-hmenu__card-title',
			)
		);

		$this->add_control(
			'card_title_color',
			array(
				'label'     => __( 'Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::ROOT => '--mdotcar-card-title: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'card_title_hover_color',
			array(
				'label'     => __( 'Hover Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::ROOT => '--mdotcar-card-title-hover: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'card_desc_heading',
			array(
				'label'     => __( 'Description', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'card_desc_typography',
				'selector' => self::ROOT . ' .mdotcar-hmenu__card-desc',
			)
		);

		$this->add_control(
			'card_desc_color',
			array(
				'label'     => __( 'Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::ROOT => '--mdotcar-card-desc: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'card_icon_heading',
			array(
				'label'     => __( 'Icon', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'card_icon_size',
			array(
				'label'      => __( 'Circle Size', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 16,
						'max' => 96,
					),
				),
				'selectors'  => array( self::ROOT => '--mdotcar-card-icon: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'card_icon_padding',
			array(
				'label'      => __( 'Space Around the Icon', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 32 ) ),
				'selectors'  => array( self::ROOT => '--mdotcar-card-icon-pad: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'card_icon_border',
			array(
				'label'     => __( 'Circle Border', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::ROOT => '--mdotcar-card-icon-border: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'card_icon_gray',
			array(
				'label'        => __( 'Gray Until Hovered', 'mdotcar-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'prefix_class' => 'mdotcar-hmenu-gray-',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the drawer toggle.
	 */
	private function register_toggle_style_controls() {
		$this->start_controls_section(
			'section_style_toggle',
			array(
				'label' => __( 'Drawer Toggle', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'toggle_size',
			array(
				'label'      => __( 'Size', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 16,
						'max' => 64,
					),
				),
				'selectors'  => array( self::ROOT => '--mdotcar-toggle-size: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'toggle_color',
			array(
				'label'       => __( 'Color', 'mdotcar-elementor' ),
				'description' => __( 'For the built-in icon.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array( self::ROOT => '--mdotcar-toggle-color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the drawer.
	 */
	private function register_drawer_style_controls() {
		$this->start_controls_section(
			'section_style_drawer',
			array(
				'label' => __( 'Drawer', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'drawer_width',
			array(
				'label'      => __( 'Width', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vw' ),
				'range'      => array(
					'px' => array(
						'min' => 200,
						'max' => 600,
					),
				),
				'selectors'  => array( self::DRAWER => '--mdotcar-drawer-width: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'drawer_bg',
			array(
				'label'     => __( 'Background', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::DRAWER => '--mdotcar-drawer-bg: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'drawer_padding',
			array(
				'label'      => __( 'Padding', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'selectors'  => array( self::DRAWER . ' .mdotcar-hmenu-drawer__panel' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'drawer_gap',
			array(
				'label'       => __( 'Gap Between Parts', 'mdotcar-elementor' ),
				'description' => __( 'Between the templates and the menu.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'range'       => array( 'px' => array( 'max' => 64 ) ),
				'selectors'   => array( self::DRAWER . ' .mdotcar-hmenu-drawer__panel' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'drawer_scrim',
			array(
				'label'     => __( 'Page Dim Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::DRAWER => '--mdotcar-drawer-scrim: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'drawer_blur',
			array(
				'label'      => __( 'Page Blur', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 24 ) ),
				'selectors'  => array( self::DRAWER => '--mdotcar-drawer-blur: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the menu list in the drawer.
	 */
	private function register_list_style_controls() {
		$this->start_controls_section(
			'section_style_list',
			array(
				'label' => __( 'Drawer Menu', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'list_typography',
				'selector' => self::DRAWER . ' .mdotcar-hmenu-list__link',
			)
		);

		$this->add_control(
			'list_color',
			array(
				'label'     => __( 'Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::DRAWER => '--mdotcar-list-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'list_active_color',
			array(
				'label'     => __( 'Current Page / Open Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::DRAWER => '--mdotcar-list-active: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'list_open_bg',
			array(
				'label'     => __( 'Open Item Background', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::DRAWER => '--mdotcar-list-open-bg: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'list_row_padding',
			array(
				'label'      => __( 'Row Height (Padding)', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 32 ) ),
				'selectors'  => array( self::DRAWER => '--mdotcar-list-pad: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'list_icon_size',
			array(
				'label'      => __( 'Icon Size', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 10,
						'max' => 40,
					),
				),
				'selectors'  => array( self::DRAWER => '--mdotcar-list-icon: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'list_separator',
			array(
				'label'     => __( 'Separator Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::DRAWER => '--mdotcar-list-sep: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'list_arrow',
			array(
				'label'     => __( 'Arrow Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::DRAWER => '--mdotcar-list-arrow: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'sub_heading',
			array(
				'label'     => __( 'Sub Items', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'sub_typography',
				'selector' => self::DRAWER . ' .mdotcar-hmenu-list__sub a, ' . self::DRAWER . ' .mdotcar-hmenu-list__sub span',
			)
		);

		$this->add_control(
			'sub_color',
			array(
				'label'     => __( 'Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::DRAWER => '--mdotcar-sub-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'sub_line',
			array(
				'label'     => __( 'Guide Line Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::DRAWER => '--mdotcar-sub-line: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	/*
	 * Data
	 */

	/**
	 * The menu mdotcar.com had when the widget was introduced, so a new widget starts as the site's header.
	 *
	 * @return array[]
	 */
	private static function default_items() {
		$up   = '/wp-content/uploads/';
		$item = static function ( $level, $label, $link, $icon = '', $extra = array() ) use ( $up ) {
			return array_merge(
				array(
					'level' => $level,
					'label' => $label,
					'link'  => array( 'url' => $link ),
					'icon'  => array( 'url' => $icon ? $up . $icon : '' ),
				),
				$extra
			);
		};

		return array(
			$item( 'top', 'خانه', '/', '2024/07/home-2.svg', array( 'mobile_label' => 'صفحه اصلی' ) ),
			$item( 'top', 'خدمات', '/service/', '2024/07/reserve.svg' ),
			$item( 'sub', 'امداد خودرو', '/car-assistance/', '2026/04/smart-car.svg', array( 'description' => 'امداد خودرو، شبانه‌روزی و با اعزام سریع کارشناس به محل شما' ) ),
			$item( 'sub', 'شبکه تعمیرگاهی', '/repair-shops/', '2026/04/setting-2.svg', array( 'description' => 'تعمیر خودرو در تعمیرگاه‌های معتبر توسط کارشناسان مجرب' ) ),
			$item( 'sub', 'عیب‌یابی خودرو', '/check-light/', '2026/04/1213297-200-1.svg', array( 'description' => 'عیب‌یابی و آگاهی از معانی چراغ‌های هشدار خودرو' ) ),
			$item( 'sub', 'یدک‌کش و حمل خودرو', '/car-shipping/', '2026/04/driving.svg', array( 'description' => 'حمل خودروی شما به هر مقصدی با یدک‌کش و راننده‌ای مجرب' ) ),
			$item( 'sub', 'تعویض روغن', '/oil-change-on-site/', '2026/04/calendar.svg', array( 'description' => 'تعویض روغن، هر جا که باشید، بدون معطلی' ) ),
			$item( 'sub', 'کارواش سیار', '/carwash/', '2026/04/bucket.svg', array( 'description' => 'سرویس کارواش با شستشوی نانو در محل شما' ) ),
			$item( 'sub', 'چک قبل از سفر', '/service-check-before-trip/', '2026/04/task.svg', array( 'description' => 'بررسی کامل وضعیت خودرو پیش از سفر برای اطمینان از ایمنی و کارکرد صحیح' ) ),
			$item( 'sub', 'استعلام و پرداخت خودرویی', '/vehicle-services/', '2026/10/Frame-2147225186-1.svg', array( 'description' => 'استعلام و پرداخت یکپارچه خدمات خودرویی؛ از خلافی تا عوارض و طرح ترافیک' ) ),
			$item( 'top', 'قیمت خدمات', '/pricing/', '2024/07/tag.svg', array( 'mobile_label' => 'تعرفه‌ها' ) ),
			$item( 'top', 'دانستنی‌ها', '/blog/', '2024/07/document-copy-1.svg' ),
			$item( 'top', 'درباره ما', '/aboutus/', '2024/07/bubble.svg' ),
			$item( 'top', 'ثبت‌نام امدادگر', '/attracting-rescuers/', '2024/07/emoji-normal.svg', array( 'mobile_label' => 'همکاری با امداتکار' ) ),
		);
	}

	/**
	 * Saved Elementor templates, for the drawer's two slots.
	 *
	 * @return string[] id => title
	 */
	private static function templates() {
		$options = array( '' => __( '— None —', 'mdotcar-elementor' ) );
		$posts   = get_posts(
			array(
				'post_type'      => 'elementor_library',
				'post_status'    => 'publish',
				'posts_per_page' => 200,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		foreach ( $posts as $post ) {
			$options[ (string) $post->ID ] = $post->post_title ? $post->post_title : '#' . $post->ID;
		}

		return $options;
	}

	/**
	 * Rows grouped as top items with their sub items, as one place shows them.
	 *
	 * @param array  $rows  Repeater rows.
	 * @param string $where 'desktop' or 'mobile'.
	 * @return array[] Each: the top row plus 'children'.
	 */
	private static function tree( array $rows, $where ) {
		$tree = array();

		foreach ( $rows as $row ) {
			$show = isset( $row['show_on'] ) ? $row['show_on'] : 'both';
			if ( 'both' !== $show && $where !== $show ) {
				continue;
			}
			if ( 'sub' === ( isset( $row['level'] ) ? $row['level'] : 'top' ) ) {
				if ( $tree ) {
					$tree[ count( $tree ) - 1 ]['children'][] = $row;
				}
				continue; // A sub item before any top item has nowhere to go.
			}
			$row['children'] = array();
			$tree[]          = $row;
		}

		return $tree;
	}

	/**
	 * Whether a link points at the page being viewed (path only; a trailing slash does not matter).
	 *
	 * @param array $link URL control value.
	 * @return bool
	 */
	private static function is_current( $link ) {
		$url = isset( $link['url'] ) ? trim( $link['url'] ) : '';
		if ( '' === $url || '#' === $url[0] ) {
			return false;
		}

		$path = wp_parse_url( $url, PHP_URL_PATH );
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( $host && wp_parse_url( home_url(), PHP_URL_HOST ) !== $host ) {
			return false;
		}

		$request = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '/';

		return untrailingslashit( rawurldecode( (string) $path ) ) === untrailingslashit( rawurldecode( (string) $request ) );
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
		$rows    = isset( $s['items'] ) && is_array( $s['items'] ) ? $s['items'] : array();
		$id      = $this->get_id();
		$editing = Elementor::$instance->editor->is_edit_mode();

		$classes = array(
			'mdotcar-hmenu',
			'mdotcar-hmenu--bp-' . ( isset( $s['breakpoint'] ) ? $s['breakpoint'] : 'tablet' ),
		);
		if ( $editing && 'yes' === $s['editor_mega'] ) {
			$classes[] = 'is-editor-mega';
		}

		$this->add_render_attribute(
			'root',
			array(
				'class'              => $classes,
				'data-mdotcar-hmenu' => $id,
				'data-open'          => 'click' === $s['mega_open'] ? 'click' : 'hover',
				'data-anchor'        => 'bar' === $s['mega_anchor'] ? 'bar' : 'header',
				'data-dim'           => 'yes' === $s['mega_overlay'] ? '1' : '0',
				'data-editor-drawer' => $editing && 'yes' === $s['editor_drawer'] ? '1' : '0',
			)
		);
		?>
		<div <?php $this->print_render_attribute_string( 'root' ); ?>>
			<nav class="mdotcar-hmenu__nav" aria-label="<?php echo esc_attr( $s['nav_label'] ); ?>">
				<ul class="mdotcar-hmenu__bar">
					<?php $this->render_bar( self::tree( $rows, 'desktop' ), $s, $id ); ?>
				</ul>
			</nav>
			<button type="button" class="mdotcar-hmenu__toggle" aria-expanded="false" aria-controls="mdotcar-drawer-<?php echo esc_attr( $id ); ?>" aria-label="<?php echo esc_attr( $s['toggle_label'] ); ?>">
				<?php
				if ( ! empty( $s['toggle_icon']['url'] ) ) {
					echo '<img src="' . esc_url( $s['toggle_icon']['url'] ) . '" alt="" aria-hidden="true">';
				} else {
					echo self::svg_burger(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup.
				}
				?>
			</button>
			<div class="mdotcar-hmenu__dim" aria-hidden="true"></div>
			<?php $this->render_drawer( self::tree( $rows, 'mobile' ), $s, $id ); ?>
		</div>
		<?php
	}

	/**
	 * The bar's items, and the mega menu under any that has sub items.
	 *
	 * @param array[] $tree Top items with children.
	 * @param array   $s    Settings.
	 * @param string  $id   Widget id.
	 */
	private function render_bar( array $tree, array $s, $id ) {
		foreach ( $tree as $n => $top ) {
			$kids    = $top['children'];
			$current = self::is_current( $top['link'] );
			foreach ( $kids as $kid ) {
				$current = $current || self::is_current( $kid['link'] );
			}
			$classes = array( 'mdotcar-hmenu__item' );
			if ( $kids ) {
				$classes[] = 'has-mega';
			}
			if ( $current ) {
				$classes[] = 'is-current';
			}
			if ( 'yes' === $top['soon'] ) {
				$classes[] = 'is-soon';
			}
			$mega = 'mdotcar-mega-' . $id . '-' . $n;
			?>
			<li class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
				<?php $this->render_link( $top, 'mdotcar-hmenu__link kalameh-font', $top['label'], $s, $current && ! $kids ); // kalameh-font: the site's heading font class. ?>
				<?php if ( $kids ) : ?>
					<button type="button" class="mdotcar-hmenu__caret" aria-expanded="false" aria-controls="<?php echo esc_attr( $mega ); ?>" aria-label="<?php echo esc_attr( $top['label'] ); ?>">
						<?php echo self::svg_chevron(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?>
					</button>
					<div class="mdotcar-hmenu__mega" id="<?php echo esc_attr( $mega ); ?>">
						<ul class="mdotcar-hmenu__mega-grid">
							<?php foreach ( $kids as $kid ) : ?>
								<li>
									<?php $this->render_card( $kid, $s ); ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</li>
			<?php
		}
	}

	/**
	 * A mega menu card: icon, title and description; a link unless it is coming soon.
	 *
	 * @param array $row Sub item.
	 * @param array $s   Settings.
	 */
	private function render_card( array $row, array $s ) {
		$soon    = 'yes' === $row['soon'];
		$current = self::is_current( $row['link'] );
		$tag     = $soon || empty( $row['link']['url'] ) ? 'span' : 'a';
		$attrs   = 'class="mdotcar-hmenu__card' . ( $soon ? ' is-soon' : '' ) . ( $current ? ' is-current' : '' ) . '"';
		if ( 'a' === $tag ) {
			$attrs .= $this->link_attrs( $row['link'] ) . ( $current ? ' aria-current="page"' : '' );
		} elseif ( $soon ) {
			$attrs .= ' aria-disabled="true"';
		}
		?>
		<<?php echo esc_html( $tag ); ?> <?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts. ?>>
			<span class="mdotcar-hmenu__card-icon" aria-hidden="true">
				<?php if ( ! empty( $row['icon']['url'] ) ) : ?>
					<img src="<?php echo esc_url( $row['icon']['url'] ); ?>" alt="" loading="lazy" decoding="async">
				<?php endif; ?>
			</span>
			<span class="mdotcar-hmenu__card-text">
				<span class="mdotcar-hmenu__card-title">
					<?php echo esc_html( $row['label'] ); ?>
					<?php if ( $soon ) : ?>
						<span class="mdotcar-hmenu__soon"><?php echo esc_html( $s['soon_text'] ); ?></span>
					<?php endif; ?>
				</span>
				<?php if ( ! empty( $row['description'] ) ) : ?>
					<span class="mdotcar-hmenu__card-desc"><?php echo esc_html( $row['description'] ); ?></span>
				<?php endif; ?>
			</span>
		</<?php echo esc_html( $tag ); ?>>
		<?php
	}

	/**
	 * The drawer: the top template, the menu list, the bottom template.
	 *
	 * @param array[] $tree Top items with children.
	 * @param array   $s    Settings.
	 * @param string  $id   Widget id.
	 */
	private function render_drawer( array $tree, array $s, $id ) {
		$side = 'left' === $s['drawer_side'] ? 'left' : 'right';
		?>
		<div class="mdotcar-hmenu-drawer mdotcar-hmenu-drawer--<?php echo esc_attr( $side ); ?> elementor-element-<?php echo esc_attr( $id ); ?>" data-for="<?php echo esc_attr( $id ); ?>" id="mdotcar-drawer-<?php echo esc_attr( $id ); ?>" hidden>
			<div class="mdotcar-hmenu-drawer__scrim" data-mdotcar-close></div>
			<div class="mdotcar-hmenu-drawer__panel" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( $s['nav_label'] ); ?>" tabindex="-1">
				<?php if ( 'yes' === $s['close_button'] ) : ?>
					<button type="button" class="mdotcar-hmenu-drawer__close" data-mdotcar-close aria-label="<?php echo esc_attr( $s['close_label'] ); ?>">
						<?php echo self::svg_close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?>
					</button>
				<?php endif; ?>
				<?php $this->render_template( $s['template_top'], 'top' ); ?>
				<nav class="mdotcar-hmenu-list__nav" aria-label="<?php echo esc_attr( $s['nav_label'] ); ?>">
					<ul class="mdotcar-hmenu-list">
						<?php
						foreach ( $tree as $n => $top ) {
							$this->render_row( $top, $s, $id . '-' . $n );
						}
						?>
					</ul>
				</nav>
				<?php $this->render_template( $s['template_bottom'], 'bottom' ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * A row of the drawer's list; with sub items, an accordion.
	 *
	 * @param array  $top Top item with children.
	 * @param array  $s   Settings.
	 * @param string $key Unique key for ids.
	 */
	private function render_row( array $top, array $s, $key ) {
		$kids    = $top['children'];
		$label   = ! empty( $top['mobile_label'] ) ? $top['mobile_label'] : $top['label'];
		$current = self::is_current( $top['link'] );
		$inside  = false;
		foreach ( $kids as $kid ) {
			$inside = $inside || self::is_current( $kid['link'] );
		}
		$classes = array( 'mdotcar-hmenu-list__row' );
		if ( $kids ) {
			$classes[] = 'has-sub';
		}
		if ( $current || $inside ) {
			$classes[] = 'is-current';
		}
		if ( 'yes' === $top['soon'] ) {
			$classes[] = 'is-soon';
		}
		$icon = ! empty( $top['icon']['url'] ) ? '<img class="mdotcar-hmenu-list__icon" src="' . esc_url( $top['icon']['url'] ) . '" alt="" aria-hidden="true">' : '<span class="mdotcar-hmenu-list__icon" aria-hidden="true"></span>';
		?>
		<li class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<?php if ( $kids ) : ?>
				<button type="button" class="mdotcar-hmenu-list__link" aria-expanded="false" aria-controls="mdotcar-sub-<?php echo esc_attr( $key ); ?>">
					<?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>
					<span class="mdotcar-hmenu-list__label"><?php echo esc_html( $label ); ?></span>
					<?php echo self::svg_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?>
				</button>
				<div class="mdotcar-hmenu-list__sub" id="mdotcar-sub-<?php echo esc_attr( $key ); ?>">
					<div class="mdotcar-hmenu-list__sub-inner">
						<?php
						foreach ( $kids as $kid ) {
							$kid_label = ! empty( $kid['mobile_label'] ) ? $kid['mobile_label'] : $kid['label'];
							$this->render_link( $kid, 'mdotcar-hmenu-list__sublink', $kid_label, $s, self::is_current( $kid['link'] ) );
						}
						?>
					</div>
				</div>
			<?php else : ?>
				<?php
				$this->render_link(
					$top,
					'mdotcar-hmenu-list__link',
					$icon . '<span class="mdotcar-hmenu-list__label">' . esc_html( $label ) . '</span>' . self::svg_arrow(),
					$s,
					$current,
					true
				);
				?>
			<?php endif; ?>
		</li>
		<?php
	}

	/**
	 * A link, or a plain span when it is coming soon or has no URL.
	 *
	 * @param array  $row     Item.
	 * @param string $css_class Class.
	 * @param string $content Text, or markup when $html.
	 * @param array  $s       Settings.
	 * @param bool   $current Whether it is the current page.
	 * @param bool   $html    Whether $content is already safe markup.
	 */
	private function render_link( array $row, $css_class, $content, array $s, $current, $html = false ) {
		$soon  = 'yes' === $row['soon'];
		$inner = $html ? $content : esc_html( $content );
		if ( $soon ) {
			$inner .= '<span class="mdotcar-hmenu__soon">' . esc_html( $s['soon_text'] ) . '</span>';
		}
		if ( $soon || empty( $row['link']['url'] ) ) {
			echo '<span class="' . esc_attr( $css_class . ( $soon ? ' is-soon' : '' ) ) . '"' . ( $soon ? ' aria-disabled="true"' : '' ) . '>' . $inner . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped parts.
			return;
		}
		echo '<a class="' . esc_attr( $css_class . ( $current ? ' is-current' : '' ) ) . '"' . $this->link_attrs( $row['link'] ) . ( $current ? ' aria-current="page"' : '' ) . '>' . $inner . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped parts.
	}

	/**
	 * The href, target and rel of a URL control value, escaped, with a leading space.
	 *
	 * @param array $link URL control value.
	 * @return string
	 */
	private function link_attrs( array $link ) {
		$out = ' href="' . esc_url( $link['url'] ) . '"';
		if ( ! empty( $link['is_external'] ) ) {
			$out .= ' target="_blank"';
		}
		$rel = array();
		if ( ! empty( $link['is_external'] ) ) {
			$rel[] = 'noopener';
		}
		if ( ! empty( $link['nofollow'] ) ) {
			$rel[] = 'nofollow';
		}
		if ( $rel ) {
			$out .= ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"';
		}
		return $out;
	}

	/**
	 * Prints a saved template into the drawer (never itself, never nested in itself).
	 *
	 * @param string $id   Template id.
	 * @param string $slot 'top' or 'bottom'.
	 */
	private function render_template( $id, $slot ) {
		$id = absint( $id );
		if ( ! $id || in_array( $id, self::$rendering, true ) || 'publish' !== get_post_status( $id ) ) {
			return;
		}
		self::$rendering[] = $id;
		$html              = Elementor::$instance->frontend->get_builder_content_for_display( $id, true );
		array_pop( self::$rendering );
		if ( $html ) {
			echo '<div class="mdotcar-hmenu-drawer__slot mdotcar-hmenu-drawer__slot--' . esc_attr( $slot ) . '">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor's rendered template.
		}
	}

	/*
	 * Icons (currentColor, so the colour controls reach them)
	 */

	/**
	 * The bar's down chevron.
	 *
	 * @return string
	 */
	private static function svg_chevron() {
		return '<svg width="24" height="25" viewBox="0 0 24 25" fill="none" aria-hidden="true" focusable="false"><path d="M19.92 9.45 13.4 15.97a2 2 0 0 1-2.8 0L4.08 9.45" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	}

	/**
	 * The drawer list's arrow (points to the reading end; flipped for LTR in CSS).
	 *
	 * @return string
	 */
	private static function svg_arrow() {
		return '<svg class="mdotcar-hmenu-list__arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false"><path d="M10 13.28 5.65 8.93a1.32 1.32 0 0 1 0-1.86L10 2.72" stroke="currentColor" stroke-width="1.2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	}

	/**
	 * The built-in toggle icon.
	 *
	 * @return string
	 */
	private static function svg_burger() {
		return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M3 7h18M3 12h18M3 17h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
	}

	/**
	 * The built-in close icon (the one the drawer uses on mdotcar.com).
	 *
	 * @return string
	 */
	private static function svg_close() {
		return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M9.17 14.83 14.83 9.17M14.83 14.83 9.17 9.17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 22h6c5 0 7-2 7-7V9c0-5-2-7-7-7H9C4 2 2 4 2 9v6c0 5 2 7 7 7Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	}
}
