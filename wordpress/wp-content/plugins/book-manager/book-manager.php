<?php
/*
Plugin Name: Book Manager
Description: A plugin to manage books as a custom post type.
Version: 1.0
Author: Your Name
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('BM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BM_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once BM_PLUGIN_DIR . 'includes/class-book-manager.php';
require_once BM_PLUGIN_DIR . 'includes/class-book-manager-settings.php';
require_once BM_PLUGIN_DIR . 'includes/class-book-manager-frontend.php';

// Initialize the plugin
function bm_init()
{
    Book_Manager::get_instance();
    Book_Manager_Settings::get_instance();
    Book_Manager_Frontend::get_instance();
}
add_action('plugins_loaded', 'bm_init');
?>