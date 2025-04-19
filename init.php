<?php
/**
 * Initialize the Content Hooks Tester
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Include the content hooks tester class
require_once plugin_dir_path(__FILE__) . 'includes/class-mpai-content-hooks-tester.php';

// Include the API hooks tester class
require_once plugin_dir_path(__FILE__) . 'includes/class-mpai-api-hooks-tester.php';

// Initialize the content hooks tester
new MPAI_Content_Hooks_Tester();

// Initialize the API hooks tester
new MPAI_API_Hooks_Tester();

// Log initialization
if (function_exists('mpai_log_debug')) {
    mpai_log_debug('Content Hooks Tester initialized');
    mpai_log_debug('API Hooks Tester initialized');
}