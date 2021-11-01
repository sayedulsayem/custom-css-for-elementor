<?php
/*
 * Plugin Name: Custom CSS for Elementor
 * Plugin URI: https://wordpress.org/plugins/custom-css-for-elementor/
 * Description: A lightweight plugin that open an option to add custom CSS code for each device (desktop, tablets, mobiles) by elementor widgets. 
 * Version: 1.2.0
 * Author: sayedulsayem
 * Author URI: https://sayedulsayem.com/
 * Text Domain: custom-css-for-element
 * Domain Path: /languages/
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Elementor tested up to: 3.4.6
 * Elementor Pro tested up to: 3.4.2
 */


// If this file is called directly, abort.
defined('ABSPATH') || exit;

// include the autoloader file
require 'vendor/autoload.php';

// run plugin initialization file
require 'plugin.php';

/**
 * load plugin after initialize wordpress core
 */
add_action('plugins_loaded', function () {
    Custom_Css_FEle\Plugin::instance()->init();
});
