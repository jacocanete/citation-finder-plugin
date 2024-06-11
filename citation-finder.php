<?php

/*
Plugin Name: Citation Finder Block
Description: Citation Finder block for LocalWiz
Version: 1.3
Author: Jaco Gagarin Canete
Author URI: jaco-portfolio.me
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('CF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CF_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include the core class (core class is responsible for loading all other classes)
require_once CF_PLUGIN_DIR . 'includes/class-citation-finder-loader.php';

// Run the plugin (core class)
function run_citation_finder()
{
    $plugin = new Citation_Finder_Loader();
    $plugin->run();
}

run_citation_finder();
