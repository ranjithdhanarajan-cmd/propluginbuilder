<?php
if (!defined('ABSPATH')) {
    exit;
}

function aiscratch_render_card_shortcode($atts) {
 $atts = shortcode_atts(
        [
            'id' => 0,
        ],
        $atts,
        'ai_scratch_card'
    );

    $card_id = intval($atts['id']);
     if (!$card_id) {
        return '';
    }

    global $wpdb;
    $cards_table = $wpdb->prefix . 'ai_scratch_cards';
    $logs_table  = $wpdb->prefix . 'ai_scratch_logs';

    $card = $wpdb->get_row($wpdb->prepare("SELECT * FROM $cards_table WHERE id = %d", $card_id));

    if (!$card) {
        return '<p>' . esc_html__('Scratch card not found.', 'ai-scratch-card-rewards') . '</p>';
    }

    if (!empty($card->expiration) && strtotime($card->expiration) < current_time('timestamp')) {
        return '<p>' . esc_html__('This scratch card has expired.', 'ai-scratch-card-rewards') . '</p>';
    }
    
if (!empty($card->max_wins)) {
        $wins = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $logs_table WHERE card_id = %d AND result = 'win'", $card_id));
        if ($wins >= (int) $card->max_wins) {
            return '<p>' . esc_html__('All prizes for this scratch card have already been claimed.', 'ai-scratch-card-rewards') . '</p>';
        }
    }

$cookie_key = 'aiscratch_played_' . $card_id;
    if (isset($_COOKIE[$cookie_key])) {
         return '<p>' . esc_html__('You already played this scratch card.', 'ai-scratch-card-rewards') . '</p>';
    }

    wp_enqueue_style('aiscratch-frontend');
    wp_enqueue_script('aiscratch-wscratchpad');
    wp_enqueue_script('aiscratch-frontend');

    static $localized = false;
    if (!$localized) {
        wp_localize_script(
            'aiscratch-frontend',
            'AISCRATCH',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('aiscratch_nonce'),
            ]
        );
        $localized = true;
    }

    $requires_lead = !empty($card->email_capture);
    $surface_color = $card->surface_color ? sanitize_hex_color($card->surface_color) : '';
    if (!$surface_color) {
        $surface_color = '#999999';
    }
    $cover_image   = $card->cover_image ? esc_url($card->cover_image) : '';

    ob_start();
    include AISCRATCH_PLUGIN_DIR . 'templates/card-template.php';
    return ob_get_clean();
}
add_shortcode('ai_scratch_card', 'aiscratch_render_card_shortcode');
