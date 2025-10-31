<?php
if (!defined('ABSPATH')) {
    exit;
}

function aiscratch_submit_result() {
    check_ajax_referer('aiscratch_nonce', 'nonce');

$card_id = isset($_POST['card_id']) ? intval($_POST['card_id']) : 0;
    $result  = isset($_POST['result']) ? sanitize_text_field(wp_unslash($_POST['result'])) : '';
    $prize   = isset($_POST['prize']) ? sanitize_text_field(wp_unslash($_POST['prize'])) : '';
    $ip      = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
    $user_id = get_current_user_id();

    if (!$card_id) {
        wp_send_json_error(['message' => __('Invalid scratch card.', 'ai-scratch-card-rewards')], 400);
    }

    if (!in_array($result, ['win', 'lose'], true)) {
        $result = 'lose';
    }

    global $wpdb;
    $log_table   = $wpdb->prefix . 'ai_scratch_logs';
    $cards_table = $wpdb->prefix . 'ai_scratch_cards';

    $card = $wpdb->get_row($wpdb->prepare("SELECT * FROM $cards_table WHERE id = %d", $card_id));
    if (!$card) {
        wp_send_json_error(['message' => __('Scratch card not found.', 'ai-scratch-card-rewards')], 404);
    }

     $wpdb->insert(
        $log_table,
        [
            'card_id'    => $card_id,
            'user_id'    => $user_id,
            'ip_address' => $ip,
            'result'     => $result,
            'prize'      => $prize,
            'created_at' => current_time('mysql'),
        ],
        ['%d', '%d', '%s', '%s', '%s', '%s']
    );
      
    if (!headers_sent()) {
        $cookie_args = [
            'expires'  => time() + (DAY_IN_SECONDS * 30),
            'path'     => defined('COOKIEPATH') ? COOKIEPATH : '/',
            'domain'   => defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '',
            'secure'   => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        ];

        setcookie('aiscratch_played_' . $card_id, 1, $cookie_args);
    }

    $webhook_url = !empty($card->webhook_url) ? esc_url_raw($card->webhook_url) : '';
    if (!empty($webhook_url)) {
  $payload = [
            'card_id'   => $card_id,
            'user_id'   => $user_id,
            'ip'        => $ip,
            'result'    => $result,
            'prize'     => $prize,
            'timestamp' => current_time('mysql'),
        ];

        wp_remote_post(
            $webhook_url,
            [
                'method'      => 'POST',
                'headers'     => ['Content-Type' => 'application/json'],
                'body'        => wp_json_encode($payload),
                'timeout'     => 20,
                'data_format' => 'body',
            ]
        );
    }

    wp_send_json_success(['message' => __('Scratch result recorded.', 'ai-scratch-card-rewards')]);
}

function aiscratch_submit_lead() {
    check_ajax_referer('aiscratch_nonce', 'nonce');

    $card_id = isset($_POST['card_id']) ? intval($_POST['card_id']) : 0;
    $name    = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $email   = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $consent = isset($_POST['consent']) ? intval($_POST['consent']) : 0;

    if (!$card_id || empty($name) || empty($email) || !is_email($email)) {
        wp_send_json_error(['message' => __('Please provide a valid name and email.', 'ai-scratch-card-rewards')], 400);
    }

    global $wpdb;
    $cards_table = $wpdb->prefix . 'ai_scratch_cards';
    $card_exists = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $cards_table WHERE id = %d", $card_id));
    if (!$card_exists) {
        wp_send_json_error(['message' => __('Scratch card not found.', 'ai-scratch-card-rewards')], 404);
    }

    $leads_table = $wpdb->prefix . 'ai_scratch_leads';
    $wpdb->insert(
        $leads_table,
        [
            'card_id'    => $card_id,
            'name'       => $name,
            'email'      => $email,
            'consent'    => $consent ? 1 : 0,
            'created_at' => current_time('mysql'),
        ],
        ['%d', '%s', '%s', '%d', '%s']
    );

    if ($wpdb->last_error) {
        wp_send_json_error(['message' => __('Unable to save lead. Please try again.', 'ai-scratch-card-rewards')], 500);
    }

    do_action('aiscratch_lead_captured', [
        'card_id' => $card_id,
        'name'    => $name,
        'email'   => $email,
        'consent' => $consent ? 1 : 0,
    ]);

    wp_send_json_success(['message' => __('Lead captured successfully.', 'ai-scratch-card-rewards')]);
}
