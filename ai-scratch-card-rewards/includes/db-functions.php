<?php
if (!defined('ABSPATH')) exit;

function aiscratch_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $cards_table = $wpdb->prefix . 'ai_scratch_cards';
    $logs_table  = $wpdb->prefix . 'ai_scratch_logs';
    $leads_table = $wpdb->prefix . 'ai_scratch_leads';

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Table 1: Scratch Cards
    $sql1 = "CREATE TABLE $cards_table (
        id INT NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        cover_image TEXT,
        prize_type VARCHAR(50),
        prize_content TEXT,
        probability INT DEFAULT 100,
        surface_color VARCHAR(20),
        max_wins INT NULL,
        expiration DATETIME NULL,
        webhook_url TEXT,
        email_capture TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql1);

    // Table 2: Scratch Logs
    $sql2 = "CREATE TABLE $logs_table (
        id BIGINT NOT NULL AUTO_INCREMENT,
        card_id INT NOT NULL,
        user_id BIGINT NULL,
        ip_address VARCHAR(45),
        result VARCHAR(10), -- win/lose
        prize TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql2);

    // Table 3: Leads
    $sql3 = "CREATE TABLE $leads_table (
        id BIGINT NOT NULL AUTO_INCREMENT,
        card_id INT NOT NULL,
        name VARCHAR(255),
        email VARCHAR(255),
        consent TINYINT(1),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql3);
}
