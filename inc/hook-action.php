<?php

namespace Custom_Css_FEle\Inc;

// If this file is called directly, abort.
defined('ABSPATH') || exit;

use Elementor\Controls_Manager;
use Elementor\Controls_Stack;
use Elementor\Element_Base;
use Elementor\Core\DynamicTags\Dynamic_CSS;
use Elementor\Plugin as Elementor_Plugin;

use Wikimedia\CSS\Parser\Parser;
use Wikimedia\CSS\Sanitizer\StylesheetSanitizer;
use Wikimedia\CSS\Util;

class Hook_Action {

    /**
     * @var Singleton The reference the *Singleton* instance of this class
     */
    public static $instance;

    /**
     * this class initialize function
     *
     * @return void
     */
    public function init() {
        add_action('elementor/element/common/_section_responsive/after_section_end', [$this, 'register_controls'], 10, 2);
        add_action('elementor/element/section/_section_responsive/after_section_end', [$this, 'register_controls'], 10, 2);
        add_action('elementor/element/column/_section_responsive/after_section_end', [$this, 'register_controls'], 10, 2);

        add_action('elementor/element/parse_css', [$this, 'add_post_css'], 10, 2);
        add_action('elementor/css-file/post/parse', [$this, 'add_page_settings_css']);
    }

    /**
     * register controls to elementor widget function
     *
     * @param Controls_Stack $element
     * @param [type] $section_id
     * @return void
     */
    public function register_controls(Controls_Stack $element, $section_id) {

        if (!current_user_can('edit_pages') && !current_user_can('unfiltered_html')) {
            return;
        }

        $element->start_controls_section(
            '_custom_css_f_ele',
            [
                'label' => esc_html__('Custom CSS for Elementor', 'custom-css-for-elementor'),
                'tab' => Controls_Manager::TAB_ADVANCED,
            ]
        );

        $element->start_controls_tabs(
            'style_tabs'
        );

        $element->start_controls_tab(
            '_custom_css_desktop',
            [
                'label' => __('<span class="eicon-device-desktop"></span>', 'custom-css-for-elementor'),
            ]
        );

        $element->add_control(
            '_custom_css_f_ele_title_desktop',
            [
                'label' => esc_html__('Custom CSS', 'custom-css-for-elementor'),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $element->add_control(
            '_custom_css_f_ele_css_desktop',
            [
                'label' => esc_html__('Custom CSS', 'custom-css-for-elementor'),
                'type' => Controls_Manager::CODE,
                'language' => 'css',
                'render_type' => 'ui',
                'show_label' => false,
                'separator' => 'none',
            ]
        );

        $element->end_controls_tab();

        $element->start_controls_tab(
            '_custom_css_tablet',
            [
                'label' => __('<span class="eicon-device-tablet"></span>', 'custom-css-for-elementor'),
            ]
        );

        $element->add_control(
            '_custom_css_f_ele_title_tablet',
            [
                'label' => esc_html__('Custom CSS (Tablet)', 'custom-css-for-elementor'),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $element->add_control(
            '_custom_css_f_ele_css_tablet',
            [
                'type' => Controls_Manager::CODE,
                'label' => esc_html__('Custom CSS (Tablet)', 'custom-css-for-elementor'),
                'language' => 'css',
                'render_type' => 'ui',
                'show_label' => false,
                'separator' => 'none',
            ]
        );

        $element->end_controls_tab();


        $element->start_controls_tab(
            '_custom_css_mobile',
            [
                'label' => __('<span class="eicon-device-mobile"></span>', 'custom-css-for-elementor'),
            ]
        );

        $element->add_control(
            '_custom_css_f_ele_title_mobile',
            [
                'label' => esc_html__('Custom CSS (Mobile)', 'custom-css-for-elementor'),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $element->add_control(
            '_custom_css_f_ele_css_mobile',
            [
                'type' => Controls_Manager::CODE,
                'label' => esc_html__('Custom CSS (Mobile)', 'custom-css-for-elementor'),
                'language' => 'css',
                'render_type' => 'ui',
                'show_label' => false,
                'separator' => 'none',
            ]
        );

        $element->end_controls_tab();
        $element->end_controls_tabs();

        $element->add_control(
            '_custom_css_f_ele_description',
            [
                'raw' => esc_html__('Use "selector" to target wrapper element. Examples:<br>selector {color: red;} // For main element<br>selector .child-element {margin: 10px;} // For child element<br>.my-class {text-align: center;} // Or use any custom selector', 'custom-css-for-elementor'),
                'type' => Controls_Manager::RAW_HTML,
                'content_classes' => 'elementor-descriptor',
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
    public function add_post_css($post_css, $element) {
        if ($post_css instanceof Dynamic_CSS) {
            return;
        }

        $element_settings = $element->get_settings();

        $sanitize_css = $this->parse_css_to_remove_injecting_code($element_settings, $post_css->get_element_unique_selector($element));

        $post_css->get_stylesheet()->add_raw_css($sanitize_css);
    }

    /**
     * add custom css function to page function
     *
     * @param [type] $post_css
     * @return void
     */
    public function add_page_settings_css($post_css) {

        $document = Elementor_Plugin::instance()->documents->get($post_css->get_post_id());

        $element_settings = $document->get_settings();

        $sanitize_css = $this->parse_css_to_remove_injecting_code($element_settings, $document->get_css_wrapper_selector());

        $post_css->get_stylesheet()->add_raw_css($sanitize_css);
    }

    /**
     * validate css and sanitize css for avoiding injection of malicious code function
     *
     * @param [type] $raw_css
     * @return void
     */
    public function parse_css_to_remove_injecting_code($element_settings, $unique_selector) {

        $custom_css = '';

        if (empty($element_settings['_custom_css_f_ele_css_desktop']) && empty($element_settings['_custom_css_f_ele_css_tablet']) && empty($element_settings['_custom_css_f_ele_css_mobile'])) {
            return;
        }

        $custom_css_desktop = trim($element_settings['_custom_css_f_ele_css_desktop']);
        $custom_css_tablet = trim($element_settings['_custom_css_f_ele_css_tablet']);
        $custom_css_mobile = trim($element_settings['_custom_css_f_ele_css_mobile']);

        if (empty($custom_css_desktop) && empty($custom_css_tablet) && empty($custom_css_mobile)) {
            return;
        }

        $custom_css .= ((!empty($custom_css_desktop)) ? $custom_css_desktop : "");
        $custom_css .= ((!empty($custom_css_tablet)) ? " @media (max-width: 768px) { " . $custom_css_tablet . "}" : "");
        $custom_css .= ((!empty($custom_css_mobile)) ? " @media (max-width: 425px) { " . $custom_css_mobile . "}" : "");

        if (empty($custom_css)) {
            return;
        }

        $custom_css = str_replace('selector', $unique_selector, $custom_css);

        $remove_tags_css = wp_kses($custom_css, []);
        $parser = Parser::newFromString($remove_tags_css);
        $parsed_css = $parser->parseStylesheet();

        $sanitizer = StylesheetSanitizer::newDefault();
        $sanitized_css = $sanitizer->sanitize($parsed_css);
        $minified_css = Util::stringify($sanitized_css, ['minify' => true]);

        return $minified_css;
    }

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
