<?php
/**
 * Plugin Name:       AI Scratch Card Rewards
 * Plugin URI:        https://example.com/ai-scratch-card-rewards
 * Description:       Create interactive scratch card promotions with optional lead capture, prize logging, and analytics.
 * Version:           1.0.0
 * Author:            Pro Plugin Builder
 * Text Domain:       ai-scratch-card-rewards
 * Requires at least: 5.8
 * Requires PHP:      7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'AISCRATCH_PLUGIN_VERSION', '1.0.0' );
define( 'AISCRATCH_PLUGIN_FILE', __FILE__ );
define( 'AISCRATCH_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'AISCRATCH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AISCRATCH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once AISCRATCH_PLUGIN_DIR . 'includes/db-functions.php';
require_once AISCRATCH_PLUGIN_DIR . 'includes/admin-page.php';
require_once AISCRATCH_PLUGIN_DIR . 'includes/shortcode.php';
require_once AISCRATCH_PLUGIN_DIR . 'includes/admin-ajax.php';

/**
 * Run on plugin activation.
 */
function aiscratch_activate() {
    aiscratch_create_tables();
}
register_activation_hook( __FILE__, 'aiscratch_activate' );

/**
 * Register custom admin menu pages.
 */
function aiscratch_register_admin_menu() {
    add_menu_page(
        __( 'AI Scratch Cards', 'ai-scratch-card-rewards' ),
        __( 'AI Scratch Cards', 'ai-scratch-card-rewards' ),
        'manage_options',
        'ai-scratch-cards',
        'aiscratch_render_all_cards',
        'dashicons-awards',
        56
    );

    add_submenu_page(
        'ai-scratch-cards',
        __( 'All Scratch Cards', 'ai-scratch-card-rewards' ),
        __( 'All Cards', 'ai-scratch-card-rewards' ),
        'manage_options',
        'ai-scratch-cards',
        'aiscratch_render_all_cards'
    );

    add_submenu_page(
        'ai-scratch-cards',
        __( 'Create Scratch Card', 'ai-scratch-card-rewards' ),
        __( 'Add New', 'ai-scratch-card-rewards' ),
        'manage_options',
        'ai-scratch-new',
        'aiscratch_render_create_card'
    );

    add_submenu_page(
        'ai-scratch-cards',
        __( 'Scratch Card Analytics', 'ai-scratch-card-rewards' ),
        __( 'Analytics', 'ai-scratch-card-rewards' ),
        'manage_options',
        'ai-scratch-analytics',
        'aiscratch_render_analytics'
    );
}
add_action( 'admin_menu', 'aiscratch_register_admin_menu' );

/**
 * Register front-end assets so they can be enqueued when the shortcode is rendered.
 */
function aiscratch_register_front_assets() {
    wp_register_style(
        'aiscratch-frontend',
        AISCRATCH_PLUGIN_URL . 'assets/css/style.css',
        [],
        AISCRATCH_PLUGIN_VERSION
    );

    wp_register_script(
        'aiscratch-wscratchpad',
        AISCRATCH_PLUGIN_URL . 'assets/js/wScratchPad.js',
        [ 'jquery' ],
        AISCRATCH_PLUGIN_VERSION,
        true
    );

    wp_register_script(
        'aiscratch-frontend',
        AISCRATCH_PLUGIN_URL . 'assets/js/scratch-interaction.js',
        [ 'jquery', 'aiscratch-wscratchpad' ],
        AISCRATCH_PLUGIN_VERSION,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'aiscratch_register_front_assets' );

// AJAX endpoints for both authenticated and guest players.
add_action( 'wp_ajax_aiscratch_submit_result', 'aiscratch_submit_result' );
add_action( 'wp_ajax_nopriv_aiscratch_submit_result', 'aiscratch_submit_result' );
add_action( 'wp_ajax_aiscratch_submit_lead', 'aiscratch_submit_lead' );
add_action( 'wp_ajax_nopriv_aiscratch_submit_lead', 'aiscratch_submit_lead' );
