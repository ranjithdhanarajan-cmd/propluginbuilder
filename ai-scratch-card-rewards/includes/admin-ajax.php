<?php
if (!defined('ABSPATH')) exit;

// Handle scratch card result (win/lose)
add_action('wp_ajax_aiscratch_submit_result', 'aiscratch_submit_result');
add_action('wp_ajax_nopriv_aiscratch_submit_result', 'aiscratch_submit_result');

function aiscratch_submit_result() {
    check_ajax_referer('aiscratch_nonce', 'nonce');

    $card_id = intval($_POST['card_id']);
    $result  = sanitize_text_field($_POST['result']);
    $prize   = sanitize_text_field($_POST['prize']);
    $ip      = $_SERVER['REMOTE_ADDR'];
    $user_id = get_current_user_id();

    global $wpdb;
    $log_table = $wpdb->prefix . 'ai_scratch_logs';

    $wpdb->insert($log_table, [
        'card_id'   => $card_id,
        'user_id'   => $user_id,
        'ip_address'=> $ip,
        'result'    => $result,
        'prize'     => $prize,
        'created_at'=> current_time('mysql')
    ]);

    // Set cookie to block replays
    setcookie('aiscratch_played_' . $card_id, 1, time() + (86400 * 30), "/");

    // Get card config for webhook
    $cards_table = $wpdb->prefix . 'ai_scratch_cards';
    $card = $wpdb->get_row($wpdb->prepare("SELECT * FROM $cards_table WHERE id = %d", $card_id));

    $webhook_url = $card->webhook_url;
    if (!empty($webhook_url)) {
        $payload = json_encode([
            'card_id'  => $card_id,
            'user_id'  => $user_id,
            'ip'       => $ip,
            'result'   => $result,
            'prize'    => $prize,
            'timestamp'=> current_time('mysql')
        ]);

        wp_remote_post($webhook_url, [
            'method'    => 'POST',
            'headers'   => ['Content-Type' => 'application/json'],
            'body'      => $payload,
            'timeout'   => 20,
        ]);
    }

    wp_send_json_success('Logged.');
}

// Handle lead capture
add_action('wp_ajax_aiscratch_submit_lead', 'aiscratch_submit_lead');
add_action('wp_ajax_nopriv_aiscratch_submit_lead', 'aiscratch_submit_lead');

function aiscratch_submit_lead() {
    check_ajax_referer('aiscratch_nonce', 'nonce');

    $card_id = intval($_POST['card_id']);
    $name    = sanitize_text_field($_POST['name']);
    $email   = sanitize_email($_POST['email']);
    $consent = isset($_POST['consent']) ? 1 : 0;

    global $wpdb;
    $leads_table = $wpdb->prefix . 'ai_scratch_leads';

    $wpdb->insert($leads_table, [
        'card_id'   => $card_id,
        'name'      => $name,
        'email'     => $email,
        'consent'   => $consent,
        'created_at'=> current_time('mysql')
    ]);

    wp_send_json_success('Lead saved.');
}
