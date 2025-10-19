<?php
/*
 * Plugin Name: Custom CSS for Elementor
 * Plugin URI: https://wordpress.org/plugins/custom-css-for-elementor/
 * Description: A lightweight plugin that open an option to add custom CSS code for each device (desktop, tablets, mobiles) by elementor widgets.
 * Version: 2.1.1
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Author: Sayedul Sayem
 * Author URI: https://sayedulsayem.com/
 * Text Domain: custom-css-for-elementor
 * Domain Path: /i18n/
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Elementor tested up to: 3.32
 * Elementor Pro tested up to: 3.32
 */

// If this file is called directly, abort.
defined( 'ABSPATH' ) || exit;

// include the autoloader file
require 'vendor/autoload.php';

// run plugin initialization file
require 'plugin.php';

/**
 * load plugin after initialize wordpress core
 */
add_action( 'plugins_loaded', function () {
	Custom_Css_FEle\Plugin::instance()->init();
} );
