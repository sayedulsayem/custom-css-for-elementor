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
use Elementor\Modules\AtomicWidgets\PropTypes\Primitives\String_Prop_Type;

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

		// Atomic Elements (Elementor experiment) use a props-schema API instead
		// of the classic controls stack. The props must exist in the schema so
		// the values survive `Props_Parser` validation on save; the editing UI
		// is injected into the atomic "Style" tab via JS (see
		// assets/js/atomic-editor-css.js).
		add_filter( 'elementor/atomic-widgets/props-schema', [ $this, 'register_atomic_props_schema' ] );
		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'add_atomic_editor_assets' ] );
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
	 * add custom css props to every atomic element's props schema so the
	 * values survive `Props_Parser` validation on save
	 *
	 * @param array $schema
	 * @return array
	 */
	public function register_atomic_props_schema( $schema ) {
		if ( ! is_array( $schema ) || isset( $schema['_custom_css_f_ele_css_desktop'] ) || ! class_exists( String_Prop_Type::class ) ) {
			return $schema;
		}

		// Loaded lazily: the class extends Elementor's String_Prop_Type, which
		// only exists once the Atomic Widgets module is active.
		if ( ! class_exists( Custom_Css_Prop_Type::class ) ) {
			include_once CUSTOM_CSS_FELE_PLUGIN_DIR . 'inc/custom-css-prop-type.php';
		}

		$prop_type_class = class_exists( Custom_Css_Prop_Type::class )
			? Custom_Css_Prop_Type::class
			: String_Prop_Type::class;

		$schema['_custom_css_f_ele_css_desktop'] = $prop_type_class::make();
		$schema['_custom_css_f_ele_css_tablet']  = $prop_type_class::make();
		$schema['_custom_css_f_ele_css_mobile']  = $prop_type_class::make();

		return $schema;
	}

	/**
	 * enqueue the script that injects the Custom CSS section into the
	 * Atomic Elements "Style" tab of the editing panel
	 *
	 * @return void
	 */
	public function add_atomic_editor_assets() {
		if ( ! class_exists( 'Elementor\Modules\AtomicWidgets\Module' ) || ! \Elementor\Modules\AtomicWidgets\Module::is_active() ) {
			return;
		}

		wp_enqueue_script(
			'purify',
			CUSTOM_CSS_FELE_PLUGIN_URL . 'assets/js/purify.min.js',
			[],
			'3.4.17',
			true
		);

		wp_enqueue_script(
			'atomic-editor-css',
			CUSTOM_CSS_FELE_PLUGIN_URL . 'assets/js/atomic-editor-css.js',
			[ 'purify' ],
			CUSTOM_CSS_FELE_VERSION,
			true
		);

		$this->default_breakpoints = apply_filters( 'custom_css_for_elementor_breakpoints', $this->default_breakpoints );

		wp_localize_script(
			'atomic-editor-css',
			'atomicEditorCssData',
			[
				'title'       => esc_html__( 'Custom CSS for Elementor', 'custom-css-for-elementor' ),
				'desktop'     => esc_html__( 'Custom CSS (Desktop)', 'custom-css-for-elementor' ),
				'tablet'      => esc_html__( 'Custom CSS (Tablet)', 'custom-css-for-elementor' ),
				'mobile'      => esc_html__( 'Custom CSS (Mobile)', 'custom-css-for-elementor' ),
				'description' => esc_html__( 'Use "selector" to target the element itself, or "selector .child" for elements inside it. Examples: selector {color: red;} // For the element itself, selector .child-element {margin: 10px;} // For child elements, .my-class {text-align: center;} // Or use any custom selector', 'custom-css-for-elementor' ),
				'notice'      => esc_html__( 'If the CSS is not reflecting in the editor panel or frontend, you need to write a more specific CSS selector.', 'custom-css-for-elementor' ),
				'postID'      => get_the_ID(),
				'breakpoints' => $this->default_breakpoints,
			]
		);
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

		$unique_selector = $post_css->get_element_unique_selector( $element );

		// Atomic Elements don't render the `.elementor-element-{id}` wrapper
		// class on widgets, so target the `data-interaction-id` attribute
		// that every atomic element/widget outputs instead.
		if ( $this->is_atomic_element( $element ) ) {
			$unique_selector = $this->get_atomic_unique_selector( $post_css, $element );
		}

		$sanitize_css = $this->parse_css_to_remove_injecting_code( $element_settings, $unique_selector );

		if ( ! empty( $sanitize_css ) ) {
			$post_css->get_stylesheet()->add_raw_css( $sanitize_css );
		}
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

		if ( ! empty( $sanitize_css ) ) {
			$post_css->get_stylesheet()->add_raw_css( $sanitize_css );
		}
	}

	/**
	 * whether the element is an Elementor Atomic Element/Widget
	 *
	 * @param mixed $element
	 * @return bool
	 */
	private function is_atomic_element( $element ): bool {
		return class_exists( 'Elementor\Modules\AtomicWidgets\Utils\Utils' )
			&& \Elementor\Modules\AtomicWidgets\Utils\Utils::is_atomic( $element );
	}

	/**
	 * build the unique selector for an atomic element. Atomic widgets render
	 * a bare element (e.g. an `h2`) without the `.elementor-element-{id}`
	 * class, but every atomic element outputs `data-interaction-id`
	 * (`origin_id` when present, otherwise the element id) in both the
	 * frontend templates and the editor canvas.
	 *
	 * @param \Elementor\Core\Files\CSS\Post $post_css
	 * @param mixed                          $element
	 * @return string
	 */
	private function get_atomic_unique_selector( $post_css, $element ): string {
		$origin_id       = $element->get_data( 'origin_id' );
		$interaction_id  = ! empty( $origin_id ) ? $origin_id : $element->get_id();

		return '.elementor-' . $post_css->get_post_id() . ' [data-interaction-id="' . $interaction_id . '"]';
	}

	/**
	 * validate css and sanitize css for avoiding injection of malicious code function
	 *
	 * @param [type] $raw_css
	 * @return void
	 */
	public function parse_css_to_remove_injecting_code( $element_settings, $unique_selector ) {

		$custom_css = '';

		$custom_css_desktop = $this->get_custom_css_value( $element_settings['_custom_css_f_ele_css_desktop'] ?? null );
		$custom_css_tablet  = $this->get_custom_css_value( $element_settings['_custom_css_f_ele_css_tablet'] ?? null );
		$custom_css_mobile  = $this->get_custom_css_value( $element_settings['_custom_css_f_ele_css_mobile'] ?? null );

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

	/**
	 * normalize a custom CSS setting value to a trimmed string.
	 * Classic widgets store a plain string, while Atomic Elements store
	 * transformable prop values like [ '$$type' => 'string', 'value' => '...' ].
	 *
	 * @param mixed $value
	 * @return string
	 */
	private function get_custom_css_value( $value ): string {
		if ( is_array( $value ) ) {
			$value = $value['value'] ?? null;
		}

		return is_string( $value ) ? trim( $value ) : '';
	}

	public function get_script_depends() {
		return ['editor-css-script'];
	}

	public function add_custom_css_for_editor() {
		wp_enqueue_script(
			'purify',
			CUSTOM_CSS_FELE_PLUGIN_URL . 'assets/js/purify.min.js',
			[],
			'3.4.17',
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
