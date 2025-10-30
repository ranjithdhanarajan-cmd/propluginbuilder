<?php
// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Table names
$cards_table = $wpdb->prefix . 'ai_scratch_cards';
$logs_table  = $wpdb->prefix . 'ai_scratch_logs';
$leads_table = $wpdb->prefix . 'ai_scratch_leads';

// Drop custom tables
$wpdb->query("DROP TABLE IF EXISTS $cards_table");
$wpdb->query("DROP TABLE IF EXISTS $logs_table");
$wpdb->query("DROP TABLE IF EXISTS $leads_table");
