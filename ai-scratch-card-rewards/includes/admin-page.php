<?php
if (!defined('ABSPATH')) {
    exit;
}

function aiscratch_admin_menu() {
    add_menu_page(
        __('Scratch Cards', 'ai-scratch-card-rewards'),
        __('Scratch Cards', 'ai-scratch-card-rewards'),
        'manage_options',
        'ai-scratch-cards',
        'aiscratch_render_all_cards',
        'dashicons-schedule',
        26
    );

    add_submenu_page(
        'ai-scratch-cards',
        __('All Scratch Cards', 'ai-scratch-card-rewards'),
        __('All Scratch Cards', 'ai-scratch-card-rewards'),
        'manage_options',
        'ai-scratch-cards',
        'aiscratch_render_all_cards'
    );

    add_submenu_page(
        'ai-scratch-cards',
        __('Create New Scratch Card', 'ai-scratch-card-rewards'),
        __('Create New', 'ai-scratch-card-rewards'),
        'manage_options',
        'ai-scratch-new',
        'aiscratch_render_create_form'
    );

    add_submenu_page(
        'ai-scratch-cards',
        __('Analytics', 'ai-scratch-card-rewards'),
        __('Analytics', 'ai-scratch-card-rewards'),
        'manage_options',
        'ai-scratch-analytics',
        'aiscratch_render_analytics'
    );
}
add_action('admin_menu', 'aiscratch_admin_menu');

// Placeholder: You can fill these with actual content later
function aiscratch_render_all_cards() {
    echo '<div class="wrap"><h1>All Scratch Cards</h1><p>This is where the list of scratch cards will go.</p></div>';
}

function aiscratch_render_create_form() {
    echo '<div class="wrap"><h1>Create New Scratch Card</h1><p>This is where the form to create a new scratch card will go.</p></div>';
}

function aiscratch_render_analytics() {
    echo '<div class="wrap"><h1>Analytics</h1><p>This is where analytics data will be shown.</p></div>';
}
