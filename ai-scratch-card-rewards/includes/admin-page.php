<?php
if (!defined('ABSPATH')) exit;

// 🔹 All Scratch Cards List
function aiscratch_render_all_cards() {
    global $wpdb;
    $table = $wpdb->prefix . 'ai_scratch_cards';
    $cards = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");

    $deleted = isset($_GET['deleted']) ? intval($_GET['deleted']) : 0;
    ?>
    <div class="wrap">
        <h1>All Scratch Cards</h1>

        <?php if ($deleted) { ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Scratch card deleted.', 'ai-scratch-card-rewards'); ?></p></div>
        <?php } ?>

        <?php if (empty($cards)) { ?>
            <p><?php esc_html_e('No scratch cards found.', 'ai-scratch-card-rewards'); ?> <a href="<?php echo esc_url(admin_url('admin.php?page=ai-scratch-new')); ?>"><?php esc_html_e('Create one now', 'ai-scratch-card-rewards'); ?></a>.</p>
        <?php } else { ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                <th><?php esc_html_e('ID', 'ai-scratch-card-rewards'); ?></th>
                        <th><?php esc_html_e('Title', 'ai-scratch-card-rewards'); ?></th>
                        <th><?php esc_html_e('Status', 'ai-scratch-card-rewards'); ?></th>
                        <th><?php esc_html_e('Shortcode', 'ai-scratch-card-rewards'); ?></th>
                        <th><?php esc_html_e('Created', 'ai-scratch-card-rewards'); ?></th>
                        <th><?php esc_html_e('Actions', 'ai-scratch-card-rewards'); ?></th>
                    </tr>
                </thead>
                <tbody>
                               <?php foreach ($cards as $card) {
                        $expired = $card->expiration && strtotime($card->expiration) < time();
                        $edit_url = add_query_arg(
                            [
                                'page' => 'ai-scratch-new',
                                'card' => $card->id,
                            ],
                            admin_url('admin.php')
                        );
                        $delete_url = wp_nonce_url(
                            add_query_arg(
                                [
                                    'action' => 'aiscratch_delete_card',
                                    'card'   => $card->id,
                                ],
                                admin_url('admin-post.php')
                            ),
                            'aiscratch_delete_card_' . $card->id
                        );
                        ?>
                        <tr>
                            <td><?php echo esc_html($card->id); ?></td>
                            <td><?php echo esc_html($card->title); ?></td>
                            <td>
                                <?php
                                 echo $expired
                                    ? '<span style="color:red;">' . esc_html__('Expired', 'ai-scratch-card-rewards') . '</span>'
                                    : '<span style="color:green;">' . esc_html__('Active', 'ai-scratch-card-rewards') . '</span>';
                                ?>
                            </td>
                            <td><code>[ai_scratch_card id="<?php echo esc_attr($card->id); ?>"]</code></td>
                            <td><?php echo esc_html($card->created_at); ?></td>
                            <td>
                                <a href="<?php echo esc_url($edit_url); ?>"><?php esc_html_e('Edit', 'ai-scratch-card-rewards'); ?></a> |
                                <a href="<?php echo esc_url($delete_url); ?>" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete this scratch card?', 'ai-scratch-card-rewards')); ?>');"><?php esc_html_e('Delete', 'ai-scratch-card-rewards'); ?></a>
                            </td>
                        </tr>
                         <?php } ?>
                </tbody>
            </table>
            <?php } ?>
    </div>
    <?php
}

// 🔹 Create New Scratch Card
function aiscratch_render_create_card() {
    global $wpdb;

    $table   = $wpdb->prefix . 'ai_scratch_cards';
    $card_id = isset($_GET['card']) ? intval($_GET['card']) : 0;
    $card    = null;
    $message = '';
    $error   = '';

    if ($card_id) {
        $card = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $card_id));
        if (!$card) {
            $error = __('Scratch card not found.', 'ai-scratch-card-rewards');
            $card_id = 0;
        }
    }

    if (isset($_POST['aiscratch_submit'])) {
        check_admin_referer('aiscratch_create_card');

 $card_id = isset($_POST['card_id']) ? intval($_POST['card_id']) : 0;

 $title         = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
        $cover_image   = isset($_POST['cover_image']) ? esc_url_raw(wp_unslash($_POST['cover_image'])) : '';
        $prize_type    = isset($_POST['prize_type']) ? sanitize_text_field(wp_unslash($_POST['prize_type'])) : 'text';
        $prize_content = isset($_POST['prize_content']) ? wp_kses_post(wp_unslash($_POST['prize_content'])) : '';
        $probability   = isset($_POST['probability']) ? max(0, min(100, intval($_POST['probability']))) : 100;
        $surface_color = isset($_POST['surface_color']) ? sanitize_hex_color(wp_unslash($_POST['surface_color'])) : '';
        $max_wins_raw  = isset($_POST['max_wins']) ? wp_unslash($_POST['max_wins']) : '';
        $max_wins      = ($max_wins_raw === '' ? null : max(0, intval($max_wins_raw)));
        $expiration    = isset($_POST['expiration']) && $_POST['expiration'] !== '' ? sanitize_text_field(wp_unslash($_POST['expiration'])) : null;
        $email_capture = isset($_POST['email_capture']) ? 1 : 0;
        $webhook_url   = isset($_POST['webhook_url']) ? esc_url_raw(wp_unslash($_POST['webhook_url'])) : '';

        $data = [
            'title'         => $title,
            'cover_image'   => $cover_image,
            'prize_type'    => $prize_type,
            'prize_content' => $prize_content,
            'probability'   => $probability,
            'surface_color' => $surface_color ? $surface_color : null,
            'max_wins'      => $max_wins,
            'expiration'    => $expiration,
            'email_capture' => $email_capture,
            'webhook_url'   => $webhook_url,
             ];

        $formats = ['%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%d', '%s'];

        if (is_null($data['surface_color'])) {
            $data['surface_color'] = null;
        }

       if ($card_id) {
            $updated = $wpdb->update($table, $data, ['id' => $card_id], $formats, ['%d']);
            if (false === $updated) {
                $error = __('Unable to update scratch card.', 'ai-scratch-card-rewards');
            } else {
                $message = __('Scratch card updated successfully.', 'ai-scratch-card-rewards');
                aiscratch_normalize_nullable_fields($card_id, $max_wins, $surface_color, $expiration);
            }
        } else {
            $data['created_at'] = current_time('mysql');
            $inserted = $wpdb->insert($table, $data, array_merge($formats, ['%s']));
            if ($inserted) {
                $card_id = $wpdb->insert_id;
                $message = __('Scratch card created successfully.', 'ai-scratch-card-rewards');
                aiscratch_normalize_nullable_fields($card_id, $max_wins, $surface_color, $expiration);
            } else {
                $error = __('Unable to create scratch card.', 'ai-scratch-card-rewards');
            }
        }

        if ($card_id) {
            $card = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $card_id));
        }
    }

    $title_value         = $card ? $card->title : '';
    $cover_image_value   = $card ? $card->cover_image : '';
    $prize_type_value    = $card ? $card->prize_type : 'text';
    $prize_content_value = $card ? $card->prize_content : '';
    $probability_value   = $card ? intval($card->probability) : 100;
    $surface_color_value = $card && $card->surface_color ? $card->surface_color : '';
    $max_wins_value      = $card && $card->max_wins !== null ? intval($card->max_wins) : '';
    $expiration_value    = $card && $card->expiration ? $card->expiration : '';
    $email_capture_value = $card ? intval($card->email_capture) : 0;
    $webhook_value       = $card ? $card->webhook_url : '';

    ?>

     <div class="wrap">
<h1><?php echo $card_id ? esc_html__('Edit Scratch Card', 'ai-scratch-card-rewards') : esc_html__('Create New Scratch Card', 'ai-scratch-card-rewards'); ?></h1>

        <?php if ($message) { ?>
            <div class="notice notice-success is-dismissible"><p><?php echo esc_html($message); ?></p></div>
        <?php } ?>

        <?php if ($error) { ?>
            <div class="notice notice-error"><p><?php echo esc_html($error); ?></p></div>
        <?php } ?>

        <form method="post">
            <?php wp_nonce_field('aiscratch_create_card'); ?>
            <input type="hidden" name="card_id" value="<?php echo esc_attr($card_id); ?>">
            <table class="form-table">
                <tr>
             <th><label for="title"><?php esc_html_e('Card Title', 'ai-scratch-card-rewards'); ?></label></th>
                    <td><input type="text" name="title" required class="regular-text" value="<?php echo esc_attr($title_value); ?>"></td>
                </tr>
                <tr>
                <th><label for="cover_image"><?php esc_html_e('Cover Image URL', 'ai-scratch-card-rewards'); ?></label></th>
                    <td><input type="text" name="cover_image" class="regular-text" placeholder="https://example.com/cover.jpg" value="<?php echo esc_attr($cover_image_value); ?>"></td>
                </tr>
                <tr>
                <th><label for="prize_type"><?php esc_html_e('Prize Type', 'ai-scratch-card-rewards'); ?></label></th>
                    <td>
                        <select name="prize_type">
                     <?php
                            $options = [
                                'text'   => __('Custom Text', 'ai-scratch-card-rewards'),
                                'coupon' => __('Coupon Code', 'ai-scratch-card-rewards'),
                                'link'   => __('Redirect Link', 'ai-scratch-card-rewards'),
                                'image'  => __('Image', 'ai-scratch-card-rewards'),
                                'none'   => __('No Prize', 'ai-scratch-card-rewards'),
                            ];
                            foreach ($options as $value => $label) {
                                printf('<option value="%1$s" %2$s>%3$s</option>', esc_attr($value), selected($prize_type_value, $value, false), esc_html($label));
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                <th><label for="prize_content"><?php esc_html_e('Prize Content', 'ai-scratch-card-rewards'); ?></label></th>
                    <td><input type="text" name="prize_content" class="regular-text" placeholder="10% OFF or URL or image link" value="<?php echo esc_attr($prize_content_value); ?>"></td>
                </tr>
                <tr>
                <th><label for="probability"><?php esc_html_e('Win Probability (%)', 'ai-scratch-card-rewards'); ?></label></th>
                    <td><input type="number" name="probability" value="<?php echo esc_attr($probability_value); ?>" min="0" max="100"></td>
                </tr>
                <tr>
                <th><label for="surface_color"><?php esc_html_e('Scratch Surface Color', 'ai-scratch-card-rewards'); ?></label></th>
                    <td><input type="text" name="surface_color" class="regular-text" placeholder="#999999" value="<?php echo esc_attr($surface_color_value); ?>"></td>
                </tr>
                <tr>
               <th><label for="max_wins"><?php esc_html_e('Max Wins', 'ai-scratch-card-rewards'); ?></label></th>
                    <td><input type="number" name="max_wins" placeholder="<?php esc_attr_e('Optional', 'ai-scratch-card-rewards'); ?>" value="<?php echo esc_attr($max_wins_value); ?>"></td>
                </tr>
                <tr>
                 <th><label for="expiration"><?php esc_html_e('Expiration Date', 'ai-scratch-card-rewards'); ?></label></th>
                    <td><input type="date" name="expiration" value="<?php echo esc_attr($expiration_value); ?>"></td>
                </tr>
                <tr>
                <th><label for="email_capture"><?php esc_html_e('Require Email Before Scratch', 'ai-scratch-card-rewards'); ?></label></th>
                    <td>
                        <label>
                            <input type="checkbox" name="email_capture" value="1" <?php checked($email_capture_value, 1); ?>>
                            <?php esc_html_e('Enable lead capture', 'ai-scratch-card-rewards'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                <th><label for="webhook_url"><?php esc_html_e('Webhook URL (optional)', 'ai-scratch-card-rewards'); ?></label></th>
                    <td><input type="text" name="webhook_url" class="regular-text" placeholder="https://your-crm-endpoint.com/webhook" value="<?php echo esc_attr($webhook_value); ?>"></td>
                </tr>
            </table>

            <?php submit_button($card_id ? __('Update Scratch Card', 'ai-scratch-card-rewards') : __('Create Scratch Card', 'ai-scratch-card-rewards'), 'primary', 'aiscratch_submit'); ?>
        </form>
    </div>
    <?php
}

function aiscratch_handle_delete_card() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permission to delete scratch cards.', 'ai-scratch-card-rewards'));
    }

    $card_id = isset($_GET['card']) ? intval($_GET['card']) : 0;
    check_admin_referer('aiscratch_delete_card_' . $card_id);

    if (!$card_id) {
        wp_safe_redirect(add_query_arg(['page' => 'ai-scratch-cards'], admin_url('admin.php')));
        exit;
    }

    global $wpdb;
    $cards_table = $wpdb->prefix . 'ai_scratch_cards';
    $logs_table  = $wpdb->prefix . 'ai_scratch_logs';
    $leads_table = $wpdb->prefix . 'ai_scratch_leads';

    $wpdb->delete($cards_table, ['id' => $card_id], ['%d']);
    $wpdb->delete($logs_table, ['card_id' => $card_id], ['%d']);
    $wpdb->delete($leads_table, ['card_id' => $card_id], ['%d']);

    wp_safe_redirect(add_query_arg(['page' => 'ai-scratch-cards', 'deleted' => 1], admin_url('admin.php')));
    exit;
}
add_action('admin_post_aiscratch_delete_card', 'aiscratch_handle_delete_card');

function aiscratch_normalize_nullable_fields($card_id, $max_wins, $surface_color, $expiration) {
    if (!$card_id) {
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'ai_scratch_cards';

    if (is_null($max_wins)) {
        $wpdb->query($wpdb->prepare("UPDATE $table SET max_wins = NULL WHERE id = %d", $card_id));
    }

    if (!$surface_color) {
        $wpdb->query($wpdb->prepare("UPDATE $table SET surface_color = NULL WHERE id = %d", $card_id));
    }

    if (!$expiration) {
        $wpdb->query($wpdb->prepare("UPDATE $table SET expiration = NULL WHERE id = %d", $card_id));
    }
}

// 🔹 Analytics Page
function aiscratch_render_analytics() {
    global $wpdb;

    $cards_table = $wpdb->prefix . 'ai_scratch_cards';
    $logs_table  = $wpdb->prefix . 'ai_scratch_logs';

    $total_plays = $wpdb->get_var("SELECT COUNT(*) FROM $logs_table");
    $total_wins  = $wpdb->get_var("SELECT COUNT(*) FROM $logs_table WHERE result = 'win'");
    $win_rate    = $total_plays > 0 ? round(($total_wins / $total_plays) * 100, 2) : 0;

    $cards = $wpdb->get_results("SELECT * FROM $cards_table ORDER BY created_at DESC");
    ?>
    <div class="wrap">
        <h1>Scratch Card Analytics</h1>

        <h2>Global Stats</h2>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
@@ -173,43 +328,43 @@ function aiscratch_render_analytics() {
                    <td>Total Plays</td>
                    <td><?php echo esc_html($total_plays); ?></td>
                </tr>
                <tr>
                    <td>Total Wins</td>
                    <td><?php echo esc_html($total_wins); ?></td>
                </tr>
                <tr>
                    <td>Global Win Rate</td>
                    <td><?php echo esc_html($win_rate); ?>%</td>
                </tr>
            </tbody>
        </table>

        <h2>Per Card Stats</h2>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Card Title</th>
                    <th>Total Plays</th>
                    <th>Wins</th>
                    <th>Win Rate</th>
                </tr>
            </thead>
            <tbody>
<?php foreach ($cards as $card) {
                    $card_id = intval($card->id);
         $plays   = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $logs_table WHERE card_id = %d", $card_id));
                    $wins    = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $logs_table WHERE card_id = %d AND result = 'win'", $card_id));
                    $rate    = $plays > 0 ? round(($wins / $plays) * 100, 2) : 0;
                    ?>
                    <tr>
                        <td><?php echo esc_html($card->title); ?></td>
                        <td><?php echo esc_html($plays); ?></td>
                        <td><?php echo esc_html($wins); ?></td>
                        <td><?php echo esc_html($rate); ?>%</td>
                    </tr>
        <?php } ?>
            </tbody>
        </table>
    </div>
    <?php
}
