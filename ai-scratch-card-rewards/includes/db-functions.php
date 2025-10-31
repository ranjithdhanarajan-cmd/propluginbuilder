<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('aiscratch_create_tables')) {

function aiscratch_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $cards_table = $wpdb->prefix . 'ai_scratch_cards';
    $logs_table  = $wpdb->prefix . 'ai_scratch_logs';
    $leads_table = $wpdb->prefix . 'ai_scratch_leads';

    $sql1 = "
    CREATE TABLE $cards_table (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        cover_image TEXT,
        prize_type VARCHAR(20),
        prize_content TEXT,
        probability INT DEFAULT 100,
        surface_color VARCHAR(10),
        max_wins INT DEFAULT NULL,
        expiration DATE DEFAULT NULL,
        email_capture TINYINT(1) DEFAULT 0,
        webhook_url TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;
@@ -34,25 +36,27 @@ function aiscratch_create_tables() {
        card_id INT NOT NULL,
        user_id INT DEFAULT 0,
        ip_address VARCHAR(45),
        result VARCHAR(10),
        prize TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;
    ";

    $sql3 = "
    CREATE TABLE $leads_table (
        id INT AUTO_INCREMENT PRIMARY KEY,
        card_id INT NOT NULL,
        name VARCHAR(255),
        email VARCHAR(255),
        consent TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;
    ";

    // ✅ Run each one separately
    dbDelta($sql1);
    dbDelta($sql2);
    dbDelta($sql3);
}

}
