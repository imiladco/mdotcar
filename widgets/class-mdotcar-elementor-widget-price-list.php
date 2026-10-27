<?php
/**
 * MDotCar Price List widget.
 *
 * @package MDotCar\Elementor
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;

/**
 * A price list of parts: a header row (part, the vehicles it fits, price) and
 * one row per part from a repeater, white and grey in turn.
 *
 * It is a real table (head, rows, cells, with their roles stated, since the
 * rows are laid out as a grid), so search engines and screen readers read it
 * as one. A numeric price is written with thousands separators and, by
 * default, Persian digits; anything else (call for a price, …) as typed.
 *
 * Every style control carries mdotcar.com's design as its default, so the
 * panel shows the values in use rather than empty fields.
 */
class MDotCar_Elementor_Widget_Price_List extends Widget_Base {

	/** Root of the widget's markup. */
	const ROOT = '{{WRAPPER}} .mdotcar-plist';

	/** Every row, the header's included. */
	const ROWS = '{{WRAPPER}} .mdotcar-plist__row';

	/** The body's rows. */
	const BODY_ROWS = '{{WRAPPER}} .mdotcar-plist__body .mdotcar-plist__row';

	/**
	 * Widget key used in templates and the panel.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'mdotcar-price-list';
	}

	/**
	 * Label shown in the Elementor panel.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'MDotCar Price List', 'mdotcar-elementor' );
	}

	/**
	 * Icon shown next to the widget in the panel.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-table';
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
		return array( 'price', 'list', 'table', 'parts', 'tariff', 'mdotcar', 'قیمت', 'لیست', 'جدول', 'قطعه' );
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
	 * Registers panel controls.
	 */
	protected function register_controls() {
		$this->register_rows_controls();
		$this->register_header_controls();
		$this->register_price_controls();
		$this->register_box_style_controls();
		$this->register_header_style_controls();
		$this->register_row_style_controls();
		$this->register_part_style_controls();
		$this->register_price_style_controls();
	}

	/*
	 * Content
	 */

	/**
	 * Content tab: the rows.
	 */
	private function register_rows_controls() {
		$this->start_controls_section(
			'section_rows',
			array(
				'label' => __( 'Rows', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'name',
			array(
				'label'       => __( 'Part', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'default'     => 'باتری ۶۰ آمپر تویوتا',
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'fits',
			array(
				'label'       => __( 'Fits', 'mdotcar-elementor' ),
				'description' => __( 'The vehicles it fits.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'default'     => 'تویوتا، کرولا، تویوتا',
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'price',
			array(
				'label'       => __( 'Price', 'mdotcar-elementor' ),
				'description' => __( 'A number, written without separators (3450000); or any text, shown as typed.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'default'     => '3450000',
			)
		);

		$repeater->add_control(
			'unit',
			array(
				'label'       => __( 'Currency Unit', 'mdotcar-elementor' ),
				'description' => __( 'Leave empty for the list\'s own.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::TEXT,
			)
		);

		$repeater->add_control(
			'link',
			array(
				'label'   => __( 'Link', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::URL,
				'dynamic' => array( 'active' => true ),
			)
		);

		$row = static function ( $name ) {
			return array(
				'name'  => $name,
				'fits'  => 'تویوتا، کرولا، تویوتا',
				'price' => '3450000',
			);
		};

		$this->add_control(
			'rows',
			array(
				'label'       => __( 'Rows', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					$row( 'باتری ۶۰ آمپر تویوتا' ),
					$row( 'باتری ۶۰ آمپر تویوتا' ),
					$row( 'باتری ۶۰ آمپر تویوتا' ),
					$row( 'فیوز ۳۰ آمپر' ),
					$row( 'فیوز ۱۵ آمپر' ),
					$row( 'فیوز ۱۰ آمپر' ),
				),
				'title_field' => '{{{ name }}}',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Content tab: the header row.
	 */
	private function register_header_controls() {
		$this->start_controls_section(
			'section_header',
			array(
				'label' => __( 'Header', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_header',
			array(
				'label'        => __( 'Show the Header', 'mdotcar-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		foreach ( array(
			'part'  => array( __( 'Part Column', 'mdotcar-elementor' ), 'قطعه/ کالا' ),
			'fits'  => array( __( 'Fits Column', 'mdotcar-elementor' ), 'تناسب خودرو' ),
			'price' => array( __( 'Price Column', 'mdotcar-elementor' ), 'قیمت (تومان)' ),
		) as $key => $def ) {
			$this->add_control(
				'head_' . $key,
				array(
					'label'       => $def[0],
					'type'        => Controls_Manager::TEXT,
					'default'     => $def[1],
					'label_block' => true,
					'separator'   => 'part' === $key ? 'before' : 'none',
					'condition'   => array( 'show_header' => 'yes' ),
				)
			);
			$this->add_control(
				'head_' . $key . '_icon',
				array(
					'label'       => __( 'Icon', 'mdotcar-elementor' ),
					'description' => __( 'Leave empty for the built-in one.', 'mdotcar-elementor' ),
					'type'        => Controls_Manager::MEDIA,
					'condition'   => array( 'show_header' => 'yes' ),
				)
			);
		}

		$this->end_controls_section();
	}

	/**
	 * Content tab: how prices are written.
	 */
	private function register_price_controls() {
		$this->start_controls_section(
			'section_price',
			array(
				'label' => __( 'Prices', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'unit',
			array(
				'label'   => __( 'Currency Unit', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => 'تومان',
			)
		);

		$this->add_control(
			'format',
			array(
				'label'        => __( 'Group the Digits', 'mdotcar-elementor' ),
				'description'  => __( 'A numeric price gets a separator every three digits.', 'mdotcar-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'separator',
			array(
				'label'     => __( 'Separator', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '،',
				'condition' => array( 'format' => 'yes' ),
			)
		);

		$this->add_control(
			'digits',
			array(
				'label'   => __( 'Digits', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'fa',
				'options' => array(
					'fa' => __( 'Persian (۰۱۲)', 'mdotcar-elementor' ),
					'en' => __( 'Latin (012)', 'mdotcar-elementor' ),
				),
			)
		);

		$this->end_controls_section();
	}

	/*
	 * Style (every default is the site's design, so the panel shows it)
	 */

	/**
	 * A dimensions default.
	 *
	 * @param int $top    Top.
	 * @param int $side   Right and left.
	 * @param int $bottom Bottom; the top when left out.
	 * @return array
	 */
	private static function box( $top, $side, $bottom = null ) {
		$bottom = null === $bottom ? $top : $bottom;
		return array(
			'top'      => (string) $top,
			'right'    => (string) $side,
			'bottom'   => (string) $bottom,
			'left'     => (string) $side,
			'unit'     => 'px',
			'isLinked' => $top === $side && $side === $bottom,
		);
	}

	/**
	 * Typography defaults.
	 *
	 * @param int    $size   Font size.
	 * @param string $weight Font weight.
	 * @param int    $line   Line height.
	 * @return array
	 */
	private static function type( $size, $weight, $line ) {
		return array(
			'typography'  => array( 'default' => 'custom' ),
			'font_size'   => array(
				'default' => array(
					'unit' => 'px',
					'size' => $size,
				),
			),
			'font_weight' => array( 'default' => $weight ),
			'line_height' => array(
				'default' => array(
					'unit' => 'px',
					'size' => $line,
				),
			),
		);
	}

	/**
	 * Style tab: the box around it all.
	 */
	private function register_box_style_controls() {
		$this->start_controls_section(
			'section_style_box',
			array(
				'label' => __( 'Box', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'box_bg',
			array(
				'label'     => __( 'Background', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( self::ROOT => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'           => 'box_border',
				'selector'       => self::ROOT,
				'fields_options' => array(
					'border' => array( 'default' => 'solid' ),
					'width'  => array( 'default' => self::box( 1, 1 ) ),
					'color'  => array( 'default' => '#FFD24A' ),
				),
			)
		);

		$this->add_responsive_control(
			'box_radius',
			array(
				'label'      => __( 'Radius', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'default'    => self::box( 24, 24 ),
				'selectors'  => array( self::ROOT => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'box_padding',
			array(
				'label'      => __( 'Padding', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'default'    => self::box( 12, 12 ),
				'selectors'  => array( self::ROOT => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'box_shadow',
				'selector' => self::ROOT,
			)
		);

		$this->add_control(
			'columns',
			array(
				'label'       => __( 'Column Widths', 'mdotcar-elementor' ),
				'description' => __( 'Part, fits, price; e.g. 1fr 1fr 1fr, or 2fr 1fr 1fr for a wider part column.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '1fr 1fr 1fr',
				'separator'   => 'before',
				'selectors'   => array( self::ROOT => '--mdotcar-plist-cols: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'column_gap',
			array(
				'label'      => __( 'Gap Between Columns', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 64 ) ),
				'default'    => array(
					'unit' => 'px',
					'size' => 16,
				),
				'selectors'  => array( self::ROWS => 'column-gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'row_gap',
			array(
				'label'      => __( 'Gap Between Rows', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 32 ) ),
				'default'    => array(
					'unit' => 'px',
					'size' => 0,
				),
				'selectors'  => array( self::ROOT . ' .mdotcar-plist__body' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		foreach ( array(
			'part'  => array( __( 'Part Column Alignment', 'mdotcar-elementor' ), 'start', 'start' ),
			'fits'  => array( __( 'Fits Column Alignment', 'mdotcar-elementor' ), 'center', 'start' ),
			'price' => array( __( 'Price Column Alignment', 'mdotcar-elementor' ), 'end', 'end' ),
		) as $key => $def ) {
			$this->add_responsive_control(
				'align_' . $key,
				array(
					'label'          => $def[0],
					'type'           => Controls_Manager::CHOOSE,
					'default'        => $def[1],
					'mobile_default' => $def[2], // On phones the fit sits under the part, at the start.
					'options'        => array(
						'start'  => array(
							'title' => __( 'Start', 'mdotcar-elementor' ),
							'icon'  => 'eicon-text-align-right',
						),
						'center' => array(
							'title' => __( 'Center', 'mdotcar-elementor' ),
							'icon'  => 'eicon-text-align-center',
						),
						'end'    => array(
							'title' => __( 'End', 'mdotcar-elementor' ),
							'icon'  => 'eicon-text-align-left',
						),
					),
					'selectors'      => array( self::ROWS . ' .mdotcar-plist__cell--' . $key => 'justify-content: {{VALUE}}; text-align: {{VALUE}};' ),
				)
			);
		}

		$this->end_controls_section();
	}

	/**
	 * Style tab: the header row.
	 */
	private function register_header_style_controls() {
		$this->start_controls_section(
			'section_style_header',
			array(
				'label'     => __( 'Header', 'mdotcar-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_header' => 'yes' ),
			)
		);

		$head = self::ROOT . ' .mdotcar-plist__head .mdotcar-plist__row';

		$this->add_control(
			'head_bg',
			array(
				'label'     => __( 'Background', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFEBAA',
				'selectors' => array( $head => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'head_color',
			array(
				'label'     => __( 'Text Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#212121',
				'selectors' => array( $head => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'head_typography',
				'selector'       => $head . ' .mdotcar-plist__cell',
				'fields_options' => self::type( 16, '500', 24 ),
			)
		);

		$this->add_responsive_control(
			'head_padding',
			array(
				'label'      => __( 'Padding', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'default'    => self::box( 12, 16 ),
				'selectors'  => array( $head => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'head_radius',
			array(
				'label'      => __( 'Radius', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 40 ) ),
				'default'    => array(
					'unit' => 'px',
					'size' => 16,
				),
				'selectors'  => array( $head => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'head_icon_size',
			array(
				'label'      => __( 'Icon Size', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 12,
						'max' => 48,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 24,
				),
				'selectors'  => array( $head . ' .mdotcar-plist__head-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'head_icon_gap',
			array(
				'label'      => __( 'Gap Between Icon and Text', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 32 ) ),
				'default'    => array(
					'unit' => 'px',
					'size' => 8,
				),
				'selectors'  => array( $head . ' .mdotcar-plist__cell' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'head_spacing',
			array(
				'label'      => __( 'Space Below', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 32 ) ),
				'default'    => array(
					'unit' => 'px',
					'size' => 0,
				),
				'selectors'  => array( self::ROOT . ' .mdotcar-plist__head' => 'margin-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the rows.
	 */
	private function register_row_style_controls() {
		$this->start_controls_section(
			'section_style_rows',
			array(
				'label' => __( 'Rows', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'row_typography',
				'selector'       => self::BODY_ROWS . ' .mdotcar-plist__cell',
				'fields_options' => self::type( 14, '400', 22 ),
			)
		);

		$this->add_control(
			'row_color',
			array(
				'label'     => __( 'Text Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#212121',
				'selectors' => array( self::BODY_ROWS => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'row_bg',
			array(
				'label'     => __( 'Background (odd rows)', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( self::BODY_ROWS . ':nth-child(odd)' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'row_bg_even',
			array(
				'label'     => __( 'Background (even rows)', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#F5F5F5',
				'selectors' => array( self::BODY_ROWS . ':nth-child(even)' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'row_hover_bg',
			array(
				'label'     => __( 'Hover Background', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::BODY_ROWS . ':hover' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'row_padding',
			array(
				'label'      => __( 'Padding', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'default'    => self::box( 12, 16 ),
				'selectors'  => array( self::BODY_ROWS => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'row_radius',
			array(
				'label'      => __( 'Radius', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 40 ) ),
				'default'    => array(
					'unit' => 'px',
					'size' => 12,
				),
				'selectors'  => array( self::BODY_ROWS => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the part column (its icon tile).
	 */
	private function register_part_style_controls() {
		$this->start_controls_section(
			'section_style_part',
			array(
				'label' => __( 'Part Icon', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$tile = self::BODY_ROWS . ' .mdotcar-plist__tile';

		$this->add_responsive_control(
			'tile_size',
			array(
				'label'      => __( 'Tile Size', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 16,
						'max' => 64,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 32,
				),
				'selectors'  => array( $tile => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'tile_icon',
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
				'default'    => array(
					'unit' => 'px',
					'size' => 20,
				),
				'selectors'  => array( $tile . ' img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'tile_bg',
			array(
				'label'     => __( 'Tile Background', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( $tile => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'tile_radius',
			array(
				'label'      => __( 'Tile Radius', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 32 ) ),
				'default'    => array(
					'unit' => 'px',
					'size' => 8,
				),
				'selectors'  => array( $tile => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'part_gap',
			array(
				'label'      => __( 'Gap Between Icon and Text', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 32 ) ),
				'default'    => array(
					'unit' => 'px',
					'size' => 8,
				),
				'selectors'  => array( self::BODY_ROWS . ' .mdotcar-plist__cell--part' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the price and its unit.
	 */
	private function register_price_style_controls() {
		$this->start_controls_section(
			'section_style_price',
			array(
				'label' => __( 'Price', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'price_typography',
				'selector'       => self::BODY_ROWS . ' .mdotcar-plist__amount',
				'fields_options' => self::type( 14, '500', 22 ),
			)
		);

		$this->add_control(
			'price_color',
			array(
				'label'     => __( 'Price Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#212121',
				'selectors' => array( self::BODY_ROWS . ' .mdotcar-plist__amount' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'unit_heading',
			array(
				'label'     => __( 'Currency Unit', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'unit_typography',
				'selector'       => self::BODY_ROWS . ' .mdotcar-plist__unit',
				'fields_options' => self::type( 12, '400', 22 ),
			)
		);

		$this->add_control(
			'unit_color',
			array(
				'label'     => __( 'Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#212121',
				'selectors' => array( self::BODY_ROWS . ' .mdotcar-plist__unit' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'unit_gap',
			array(
				'label'      => __( 'Gap Before the Unit', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 24 ) ),
				'default'    => array(
					'unit' => 'px',
					'size' => 4,
				),
				'selectors'  => array( self::BODY_ROWS . ' .mdotcar-plist__cell--price' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	/*
	 * Render
	 */

	/**
	 * A price as the list writes it: grouped digits, Persian or Latin; text as typed.
	 *
	 * @param string $price Price as entered.
	 * @param array  $s     Settings.
	 * @return array [ text, whether it is a number ]
	 */
	private static function price_text( $price, array $s ) {
		$raw   = trim( (string) $price );
		$latin = strtr( $raw, array_combine( array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩' ), array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ) ) );
		$plain = preg_replace( '/[\s,،٬\.]/u', '', $latin );
		if ( '' === $plain || ! ctype_digit( $plain ) ) {
			return array( $raw, false );
		}
		$out = 'yes' === $s['format'] ? number_format( (float) $plain, 0, '.', "\x01" ) : $plain;
		$out = str_replace( "\x01", (string) $s['separator'], $out );
		if ( 'en' !== $s['digits'] ) {
			$out = strtr( $out, array_combine( range( '0', '9' ), array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ) ) );
		}
		return array( $out, true );
	}

	/**
	 * Renders the widget.
	 */
	protected function render() {
		MDotCar_Elementor_Plugin::enqueue();

		$s = $this->get_settings_for_display();
		?>
		<table class="mdotcar-plist" role="table">
			<?php if ( 'yes' === $s['show_header'] ) : ?>
				<thead class="mdotcar-plist__head" role="rowgroup">
					<tr class="mdotcar-plist__row" role="row">
						<?php
						foreach ( array( 'part', 'fits', 'price' ) as $key ) {
							$icon = ! empty( $s[ 'head_' . $key . '_icon' ]['url'] )
								? '<img class="mdotcar-plist__head-icon" src="' . esc_url( $s[ 'head_' . $key . '_icon' ]['url'] ) . '" alt="" aria-hidden="true">'
								: self::svg( $key );
							echo '<th class="mdotcar-plist__cell mdotcar-plist__cell--' . esc_attr( $key ) . '" role="columnheader" scope="col">' . $icon . '<span>' . esc_html( $s[ 'head_' . $key ] ) . '</span></th>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
						}
						?>
					</tr>
				</thead>
			<?php endif; ?>
			<tbody class="mdotcar-plist__body" role="rowgroup">
				<?php
				foreach ( (array) $s['rows'] as $row ) {
					$this->render_row( $row, $s );
				}
				?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * One row.
	 *
	 * @param array $row Repeater row.
	 * @param array $s   Settings.
	 */
	private function render_row( array $row, array $s ) {
		list( $price, $numeric ) = self::price_text( $row['price'], $s );
		$unit                    = '' !== trim( (string) $row['unit'] ) ? $row['unit'] : $s['unit'];

		$name = esc_html( $row['name'] );
		if ( ! empty( $row['link']['url'] ) ) {
			$attrs = ' href="' . esc_url( $row['link']['url'] ) . '"';
			if ( ! empty( $row['link']['is_external'] ) ) {
				$attrs .= ' target="_blank" rel="noopener' . ( ! empty( $row['link']['nofollow'] ) ? ' nofollow' : '' ) . '"';
			} elseif ( ! empty( $row['link']['nofollow'] ) ) {
				$attrs .= ' rel="nofollow"';
			}
			$name = '<a class="mdotcar-plist__link"' . $attrs . '>' . $name . '</a>';
		}
		?>
		<tr class="mdotcar-plist__row elementor-repeater-item-<?php echo esc_attr( $row['_id'] ); ?>" role="row">
			<td class="mdotcar-plist__cell mdotcar-plist__cell--part" role="cell">
				<?php if ( ! empty( $row['icon']['url'] ) ) : ?>
					<span class="mdotcar-plist__tile" aria-hidden="true"><img src="<?php echo esc_url( $row['icon']['url'] ); ?>" alt="" loading="lazy" decoding="async"></span>
				<?php endif; ?>
				<span class="mdotcar-plist__name"><?php echo $name; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></span>
			</td>
			<td class="mdotcar-plist__cell mdotcar-plist__cell--fits" role="cell"><?php echo esc_html( $row['fits'] ); ?></td>
			<td class="mdotcar-plist__cell mdotcar-plist__cell--price" role="cell">
				<span class="mdotcar-plist__amount"><?php echo esc_html( $price ); ?></span>
				<?php if ( $numeric && '' !== trim( (string) $unit ) ) : ?>
					<span class="mdotcar-plist__unit"><?php echo esc_html( $unit ); ?></span>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * The header's built-in icons (currentColor, so they take the header's colour).
	 *
	 * @param string $key part, fits or price.
	 * @return string
	 */
	private static function svg( $key ) {
		$paths = array(
			'part'  => '<path d="M3.17 7.44 12 12.55l8.77-5.08M12 21.61v-9.07"/><path d="M9.93 2.48 4.59 5.44c-1.21.67-2.2 2.35-2.2 3.73v5.65c0 1.38.99 3.06 2.2 3.73l5.34 2.97c1.14.63 3.01.63 4.15 0l5.34-2.97c1.21-.67 2.2-2.35 2.2-3.73V9.17c0-1.38-.99-3.06-2.2-3.73l-5.34-2.97c-1.15-.63-3.01-.63-4.15.01Z"/>',
			'fits'  => '<path d="M15.51 2.83H8.49C6 2.83 5.45 4.07 5.13 5.59L4 11h16l-1.13-5.41c-.32-1.52-.87-2.76-3.36-2.76ZM21.99 19.82c.11 1.17-.83 2.18-2.03 2.18h-1.88c-1.08 0-1.23-.46-1.42-1.03l-.2-.6c-.28-.82-.46-1.37-1.9-1.37H9.44c-1.44 0-1.65.62-1.9 1.37l-.2.6C7.15 21.54 7 22 5.92 22H4.04c-1.2 0-2.14-1.01-2.03-2.18l.56-6.09C2.71 12.23 3 11 5.62 11h12.76c2.62 0 2.91 1.23 3.05 2.73l.56 6.09ZM4 8H3M21 8h-1M12 3v2M10.5 5h3M6 15h3M15 15h3"/>',
			'price' => '<path d="M18.6 6.2c0 1.66-2.95 3-6.6 3s-6.6-1.34-6.6-3 2.95-3 6.6-3 6.6 1.34 6.6 3ZM18.6 6.2v5.8c0 1.66-2.95 3-6.6 3s-6.6-1.34-6.6-3V6.2M18.6 12v5.8c0 1.66-2.95 3-6.6 3s-6.6-1.34-6.6-3V12"/>',
		);
		return '<svg class="mdotcar-plist__head-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[ $key ] . '</svg>';
	}
}
