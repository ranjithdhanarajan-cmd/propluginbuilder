<?php
if (!defined('ABSPATH')) {
    exit;
}

function aiscratch_render_card_shortcode($atts) {
    $atts = shortcode_atts([
        'id' => 0,
    ], $atts);

    $card_id = intval($atts['id']);
    if (!$card_id) return '';

    global $wpdb;
    $table = $wpdb->prefix . 'ai_scratch_cards';
    $card = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $card_id));

    if (!$card) return '<p>Scratch card not found.</p>';

    // Block repeat plays via cookie or IP
    $ip = $_SERVER['REMOTE_ADDR'];
    $cookie_key = 'aiscratch_played_' . $card_id;
    if (isset($_COOKIE[$cookie_key])) {
        return '<p>You already played this scratch card.</p>';
    }

    ob_start();
    include AISCRATCH_PLUGIN_DIR . 'templates/card-template.php';
    return ob_get_clean();
}
add_shortcode('ai_scratch_card', 'aiscratch_render_card_shortcode');
