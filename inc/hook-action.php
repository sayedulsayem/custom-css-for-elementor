<?php

namespace Custom_Css_FEle\Inc;

// If this file is called directly, abort.
defined( 'ABSPATH' ) || exit;

use Wikimedia\CSS\Util;
use Elementor\Controls_Stack;
use Elementor\Controls_Manager;
use Wikimedia\CSS\Parser\Parser;
use Elementor\Plugin as Elementor_Plugin;
use Elementor\Core\DynamicTags\Dynamic_CSS;
use Wikimedia\CSS\Sanitizer\StylesheetSanitizer;

class Hook_Action {

	/**
	 * @var Singleton The reference the *Singleton* instance of this class
	 */
	public static $instance;
	public $default_breakpoints;

	/**
	 * this class initialize function
	 *
	 * @return void
	 */
	public function init() {

		$this->default_breakpoints = [
			'tablet' => 768,
			'mobile' => 425
		];

		add_action( 'elementor/element/common/_section_responsive/after_section_end', [$this, 'register_controls'], 10, 2 );
		add_action( 'elementor/element/section/_section_responsive/after_section_end', [$this, 'register_controls'], 10, 2 );
		add_action( 'elementor/element/column/_section_responsive/after_section_end', [$this, 'register_controls'], 10, 2 );

		add_action( 'elementor/element/container/_section_responsive/after_section_end', [$this, 'register_controls'], 10, 2 );

		add_action( 'elementor/element/parse_css', [$this, 'add_post_css'], 10, 2 );
		add_action( 'elementor/css-file/post/parse', [$this, 'add_page_settings_css'] );

		add_action( 'elementor/frontend/after_enqueue_scripts', [$this, 'add_custom_css_for_editor'] );
	}

	/**
	 * register controls to elementor widget function
	 *
	 * @param Controls_Stack $element
	 * @param [type] $section_id
	 * @return void
	 */
	public function register_controls( Controls_Stack $element, $section_id ) {

		if ( ! current_user_can( 'edit_pages' ) && ! current_user_can( 'unfiltered_html' ) ) {
			return;
		}

		$element->start_controls_section(
			'_custom_css_f_ele',
			[
				'label' => esc_html__( 'Custom CSS for Elementor', 'custom-css-for-elementor' ),
				'tab'   => Controls_Manager::TAB_ADVANCED
			]
		);

		$element->start_controls_tabs(
			'style_tabs'
		);

		$element->start_controls_tab(
			'_custom_css_desktop',
			[
				'label' => '<span class="eicon-device-desktop" title="' . esc_html__( 'Desktop', 'custom-css-for-elementor' ) . '"></span>'
			]
		);

		$element->add_control(
			'_custom_css_f_ele_title_desktop',
			[
				'label' => esc_html__( 'Custom CSS', 'custom-css-for-elementor' ),
				'type'  => Controls_Manager::HEADING
			]
		);

		$element->add_control(
			'_custom_css_f_ele_css_desktop',
			[
				'label'       => esc_html__( 'Custom CSS', 'custom-css-for-elementor' ),
				'type'        => Controls_Manager::CODE,
				'language'    => 'css',
				'render_type' => 'ui',
				'show_label'  => false,
				'separator'   => 'none'
			]
		);

		$element->end_controls_tab();

		$element->start_controls_tab(
			'_custom_css_tablet',
			[
				'label' => '<span class="eicon-device-tablet" title="' . esc_html__( 'Tablet', 'custom-css-for-elementor' ) . '"></span>'
			]
		);

		$element->add_control(
			'_custom_css_f_ele_title_tablet',
			[
				'label' => esc_html__( 'Custom CSS (Tablet)', 'custom-css-for-elementor' ),
				'type'  => Controls_Manager::HEADING
			]
		);

		$element->add_control(
			'_custom_css_f_ele_css_tablet',
			[
				'type'        => Controls_Manager::CODE,
				'label'       => esc_html__( 'Custom CSS (Tablet)', 'custom-css-for-elementor' ),
				'language'    => 'css',
				'render_type' => 'ui',
				'show_label'  => false,
				'separator'   => 'none'
			]
		);

		$element->end_controls_tab();

		$element->start_controls_tab(
			'_custom_css_mobile',
			[
				'label' => '<span class="eicon-device-mobile" title="' . esc_html__( 'Mobile', 'custom-css-for-elementor' ) . '"></span>'
			]
		);

		$element->add_control(
			'_custom_css_f_ele_title_mobile',
			[
				'label' => esc_html__( 'Custom CSS (Mobile)', 'custom-css-for-elementor' ),
				'type'  => Controls_Manager::HEADING
			]
		);

		$element->add_control(
			'_custom_css_f_ele_css_mobile',
			[
				'type'        => Controls_Manager::CODE,
				'label'       => esc_html__( 'Custom CSS (Mobile)', 'custom-css-for-elementor' ),
				'language'    => 'css',
				'render_type' => 'ui',
				'show_label'  => false,
				'separator'   => 'none'
			]
		);

		$element->end_controls_tab();
		$element->end_controls_tabs();

		$element->add_control(
			'_custom_css_f_ele_description',
			[
				'raw'             => esc_html__( 'Use "selector" to target wrapper element. Examples:<br>selector {color: red;} // For main element<br>selector .child-element {margin: 10px;} // For child element<br>.my-class {text-align: center;} // Or use any custom selector', 'custom-css-for-elementor' ),
				'type'            => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-descriptor'
			]
		);

		$element->add_control(
			'_custom_css_f_ele_notice',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'If the CSS is not reflecting in the editor panel or frontend, you need to write a more specific CSS selector.', 'custom-css-for-elementor' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info'
			]
		);

		$element->end_controls_section();
	}

	/**
	 * add custom css function to post function
	 *
	 * @param [type] $post_css
	 * @param [type] $element
	 * @return void
	 */
	public function add_post_css( $post_css, $element ) {
		if ( $post_css instanceof Dynamic_CSS ) {
			return;
		}

		$element_settings = $element->get_settings();

		$sanitize_css = $this->parse_css_to_remove_injecting_code( $element_settings, $post_css->get_element_unique_selector( $element ) );

		$post_css->get_stylesheet()->add_raw_css( $sanitize_css );
	}

	/**
	 * add custom css function to page function
	 *
	 * @param [type] $post_css
	 * @return void
	 */
	public function add_page_settings_css( $post_css ) {

		$document = Elementor_Plugin::instance()->documents->get( $post_css->get_post_id() );

		$element_settings = $document->get_settings();

		$sanitize_css = $this->parse_css_to_remove_injecting_code( $element_settings, $document->get_css_wrapper_selector() );

		$post_css->get_stylesheet()->add_raw_css( $sanitize_css );
	}

	/**
	 * validate css and sanitize css for avoiding injection of malicious code function
	 *
	 * @param [type] $raw_css
	 * @return void
	 */
	public function parse_css_to_remove_injecting_code( $element_settings, $unique_selector ) {

		$custom_css = '';

		if ( empty( $element_settings['_custom_css_f_ele_css_desktop'] ) && empty( $element_settings['_custom_css_f_ele_css_tablet'] ) && empty( $element_settings['_custom_css_f_ele_css_mobile'] ) ) {
			return;
		}

		$custom_css_desktop = trim( $element_settings['_custom_css_f_ele_css_desktop'] );
		$custom_css_tablet  = trim( $element_settings['_custom_css_f_ele_css_tablet'] );
		$custom_css_mobile  = trim( $element_settings['_custom_css_f_ele_css_mobile'] );

		if ( empty( $custom_css_desktop ) && empty( $custom_css_tablet ) && empty( $custom_css_mobile ) ) {
			return;
		}

		$this->default_breakpoints = apply_filters( 'custom_css_for_elementor_breakpoints', $this->default_breakpoints );

		$custom_css .= ( ( ! empty( $custom_css_desktop ) ) ? $custom_css_desktop : '' );
		$custom_css .= ( ( ! empty( $custom_css_tablet ) ) ? ' @media (max-width: ' . $this->default_breakpoints['tablet'] . 'px) { ' . $custom_css_tablet . '}' : '' );
		$custom_css .= ( ( ! empty( $custom_css_mobile ) ) ? ' @media (max-width: ' . $this->default_breakpoints['mobile'] . 'px) { ' . $custom_css_mobile . '}' : '' );

		if ( empty( $custom_css ) ) {
			return;
		}

		$custom_css = str_replace( 'selector', $unique_selector, $custom_css );

		$remove_tags_css = wp_kses( $custom_css, [] );
		$parser          = Parser::newFromString( $remove_tags_css );
		$parsed_css      = $parser->parseStylesheet();

		$sanitizer     = StylesheetSanitizer::newDefault();
		$sanitized_css = $sanitizer->sanitize( $parsed_css );
		$minified_css  = Util::stringify( $sanitized_css, ['minify' => true] );

		return $minified_css;
	}

	public function get_script_depends() {
		return ['editor-css-script'];
	}

	public function add_custom_css_for_editor() {
		wp_enqueue_script(
			'purify',
			CUSTOM_CSS_FELE_PLUGIN_URL . 'assets/js/purify.min.js',
			[],
			'3.0.6',
			true
		);

		wp_enqueue_script(
			'editor-css-script',
			CUSTOM_CSS_FELE_PLUGIN_URL . 'assets/js/editor-css-script.js',
			['elementor-frontend', 'purify'],
			CUSTOM_CSS_FELE_VERSION,
			true
		);

		$this->default_breakpoints = apply_filters( 'custom_css_for_elementor_breakpoints', $this->default_breakpoints );

		wp_localize_script(
			'editor-css-script',
			'modelData',
			[
				'postID'      => get_the_ID(),
				'breakpoints' => $this->default_breakpoints
			]
		);
	}

	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return Singleton The *Singleton* instance.
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
