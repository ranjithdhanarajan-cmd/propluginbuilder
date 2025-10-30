<?php
/**
 * Plugin Name: AI Scratch Card Rewards
 * Description: Create gamified scratch cards with prizes, lead capture, and webhook integrations — all inside WordPress.
 * Version: 1.0.0
 * Author: Ranjith Dhanarajan
 * License: GPL2+
 * Text Domain: ai-scratch-card-rewards
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Plugin Constants
define('AISCRATCH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AISCRATCH_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AISCRATCH_VERSION', '1.0.0');

// Includes
require_once AISCRATCH_PLUGIN_DIR . 'includes/admin-page.php';
require_once AISCRATCH_PLUGIN_DIR . 'includes/shortcode.php';
require_once AISCRATCH_PLUGIN_DIR . 'includes/db-functions.php';
require_once AISCRATCH_PLUGIN_DIR . 'includes/helpers.php';
require_once AISCRATCH_PLUGIN_DIR . 'includes/admin-ajax.php';

// Enqueue Scripts & Styles
function aiscratch_enqueue_assets() {
    wp_enqueue_style('aiscratch-style', AISCRATCH_PLUGIN_URL . 'assets/css/style.css', [], AISCRATCH_VERSION);

    wp_enqueue_script('jquery'); // Required by wScratchPad
    wp_enqueue_script('wScratchPad', AISCRATCH_PLUGIN_URL . 'assets/js/wScratchPad.js', ['jquery'], AISCRATCH_VERSION, true);
    wp_enqueue_script('aiscratch-script', AISCRATCH_PLUGIN_URL . 'assets/js/scratch-interaction.js', ['jquery', 'wScratchPad'], AISCRATCH_VERSION, true);

    wp_localize_script('aiscratch-script', 'AISCRATCH', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('aiscratch_nonce')
    ]);
}
add_action('wp_enqueue_scripts', 'aiscratch_enqueue_assets');

// Activation Hook: create DB tables
register_activation_hook(__FILE__, 'aiscratch_activate');
function aiscratch_activate() {
    require_once AISCRATCH_PLUGIN_DIR . 'includes/db-functions.php';
    aiscratch_create_tables();
}

// Deactivation Hook: no-op for now
register_deactivation_hook(__FILE__, 'aiscratch_deactivate');
function aiscratch_deactivate() {
    // Maybe later: disable schedules, cleanup temp files
}
