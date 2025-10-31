function aiscratch_submit_result() {
    check_ajax_referer('aiscratch_nonce', 'nonce');

    $card_id = intval($_POST['card_id']);
    $result  = sanitize_text_field($_POST['result']);
    $prize   = sanitize_text_field($_POST['prize']);
    $ip      = $_SERVER['REMOTE_ADDR'];
    $user_id = get_current_user_id();

    global $wpdb;
    $log_table   = $wpdb->prefix . 'ai_scratch_logs';
    $cards_table = $wpdb->prefix . 'ai_scratch_cards';

    // ✅ Save log (includes prize)
    $wpdb->insert($log_table, [
        'card_id'    => $card_id,
        'user_id'    => $user_id,
        'ip_address' => $ip,
        'result'     => $result,
        'prize'      => $prize, // Only if you've added this to DB
        'created_at' => current_time('mysql')
    ]);

    // ✅ Prevent repeat plays
    setcookie('aiscratch_played_' . $card_id, 1, time() + (86400 * 30), "/");

    // ✅ Fire webhook if configured
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
            'method'  => 'POST',
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => $payload,
            'timeout' => 20,
        ]);
    }

    wp_send_json_success('Logged.');
}
