<?php

namespace Custom_Css_FEle;

// If this file is called directly, abort.
defined('ABSPATH') || exit;

/**
 * Plugin base class to initialize plugin
 *
 */
final class Plugin {
    /**
     * @var Singleton The reference the *Singleton* instance of this class
     */
    public static $instance;

    /**
     * elementor required version
     */
    const CCFE_MINIMUM_ELE_VER = '2.0.0';

    /**
     * php required version
     */
    const CCFE_MINIMUM_PHP_VER = '5.6';

    /**
     * class constructor function
     */
    public function __construct() {
        $this->define_constants();
    }

    /**
     * constent declare function
     *
     * @return void
     */
    public function define_constants() {
        define('CUSTOM_CSS_FELE_VERSION', '1.2.0');
        define('CUSTOM_CSS_FELE_PACKAGE', 'free');
        define('CUSTOM_CSS_FELE_PLUGIN_URL', plugin_dir_url(__FILE__));
        define('CUSTOM_CSS_FELE_PLUGIN_DIR', plugin_dir_path(__FILE__));
    }

    /**
     * init function of plugin
     *
     * @return void
     */
    public function init() {

        // Check if Elementor installed and activated
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_main_plugin']);
            return;
        }

        // Check for required Elementor version
        if (!version_compare(ELEMENTOR_VERSION, self::CCFE_MINIMUM_ELE_VER, '>=')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_elementor_version']);
            return;
        }

        // Check for required PHP version
        if (version_compare(PHP_VERSION, self::CCFE_MINIMUM_PHP_VER, '<')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_php_version']);
            return;
        }

        if (!class_exists('Custom_Css_FEle\Inc\Hook_Action')) {
            include_once CUSTOM_CSS_FELE_PLUGIN_DIR . 'inc/hook-action.php';
        }

        Inc\Hook_Action::instance()->init();
    }

    /**
     * admin notice for missing depended plugin function
     *
     * @return void
     */
    public function admin_notice_missing_main_plugin() {

        if (file_exists(WP_PLUGIN_DIR . '/elementor/elementor.php')) {
            $notice_title = esc_html__('Activate Elementor', 'custom-css-for-elementor');
            $notice_url = wp_nonce_url('plugins.php?action=activate&plugin=elementor/elementor.php&plugin_status=all&paged=1', 'activate-plugin_elementor/elementor.php');
        } else {
            $notice_title = esc_html__('Install Elementor', 'custom-css-for-elementor');
            $notice_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=elementor'), 'install-plugin_elementor');
        }

        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor */
            esc_html__('"%1$s" requires "%2$s" to be installed and activated. %3$s', 'custom-css-for-elementor'),
            '<strong>' . esc_html__('Custom CSS for Elementor', 'custom-css-for-elementor') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'custom-css-for-elementor') . '</strong>',
            '<a href="' . esc_url($notice_url) . '">' . $notice_title . '</a>'
        );

        printf('<div class="notice notice-error is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * admin notice for minimum version required plugin function
     *
     * @return void
     */
    public function admin_notice_minimum_elementor_version() {

        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'custom-css-for-elementor'),
            '<strong>' . esc_html__('Custom CSS for Elementor', 'custom-css-for-elementor') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'custom-css-for-elementor') . '</strong>',
            self::CCFE_MINIMUM_ELE_VER
        );

        printf('<div class="notice notice-error is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * admin notice for minimum version php required function
     *
     * @return void
     */
    public function admin_notice_minimum_php_version() {

        $message = sprintf(
            /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'custom-css-for-elementor'),
            '<strong>' . esc_html__('Custom CSS for Elementor', 'custom-css-for-elementor') . '</strong>',
            '<strong>' . esc_html__('PHP', 'custom-css-for-elementor') . '</strong>',
            self::CCFE_MINIMUM_PHP_VER
        );

        printf('<div class="notice notice-error is-dismissible"><p>%1$s</p></div>', $message);
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
