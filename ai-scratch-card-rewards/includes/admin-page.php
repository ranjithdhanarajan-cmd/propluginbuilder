function aiscratch_render_all_cards() {
    global $wpdb;
    $table = $wpdb->prefix . 'ai_scratch_cards';
    $cards = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
    ?>
    <div class="wrap">
        <h1>All Scratch Cards</h1>

        <?php if (empty($cards)) : ?>
            <p>No scratch cards found. <a href="?page=ai-scratch-new">Create one now</a>.</p>
        <?php else : ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Shortcode</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cards as $card) : ?>
                        <tr>
                            <td><?php echo esc_html($card->id); ?></td>
                            <td><?php echo esc_html($card->title); ?></td>
                            <td>
                                <?php
                                $expired = $card->expiration && strtotime($card->expiration) < time();
                                echo $expired ? '<span style="color:red;">Expired</span>' : '<span style="color:green;">Active</span>';
                                ?>
                            </td>
                            <td><code>[ai_scratch_card id="<?php echo esc_attr($card->id); ?>"]</code></td>
                            <td><?php echo esc_html($card->created_at); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
}
    // Handle form submission
    if (isset($_POST['aiscratch_submit'])) {
        check_admin_referer('aiscratch_create_card');

        $table = $wpdb->prefix . 'ai_scratch_cards';

        $title          = sanitize_text_field($_POST['title']);
        $cover_image    = esc_url_raw($_POST['cover_image']);
        $prize_type     = sanitize_text_field($_POST['prize_type']);
        $prize_content  = sanitize_text_field($_POST['prize_content']);
        $probability    = intval($_POST['probability']);
        $surface_color  = sanitize_hex_color($_POST['surface_color']);
        $max_wins       = $_POST['max_wins'] !== '' ? intval($_POST['max_wins']) : null;
        $expiration     = $_POST['expiration'] !== '' ? sanitize_text_field($_POST['expiration']) : null;
        $email_capture  = isset($_POST['email_capture']) ? 1 : 0;
        $webhook_url    = esc_url_raw($_POST['webhook_url']);

        $wpdb->insert($table, [
            'title'         => $title,
            'cover_image'   => $cover_image,
            'prize_type'    => $prize_type,
            'prize_content' => $prize_content,
            'probability'   => $probability,
            'surface_color' => $surface_color,
            'max_wins'      => $max_wins,
            'expiration'    => $expiration,
            'email_capture' => $email_capture,
            'webhook_url'   => $webhook_url,
            'created_at'    => current_time('mysql')
        ]);

        echo '<div class="notice notice-success"><p>Scratch card created successfully.</p></div>';
    }

    // Form UI
    ?>
    <div class="wrap">
        <h1>Create New Scratch Card</h1>
        <form method="post">
            <?php wp_nonce_field('aiscratch_create_card'); ?>

            <table class="form-table">
                <tr>
                    <th><label for="title">Card Title</label></th>
                    <td><input type="text" name="title" required class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="cover_image">Cover Image URL</label></th>
                    <td><input type="text" name="cover_image" class="regular-text" placeholder="https://example.com/cover.jpg"></td>
                </tr>
                <tr>
                    <th><label for="prize_type">Prize Type</label></th>
                    <td>
                        <select name="prize_type">
                            <option value="text">Custom Text</option>
                            <option value="coupon">Coupon Code</option>
                            <option value="link">Redirect Link</option>
                            <option value="image">Image</option>
                            <option value="none">No Prize</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="prize_content">Prize Content</label></th>
                    <td><input type="text" name="prize_content" class="regular-text" placeholder="10% OFF or URL or image link"></td>
                </tr>
                <tr>
                    <th><label for="probability">Win Probability (%)</label></th>
                    <td><input type="number" name="probability" value="100" min="0" max="100"></td>
                </tr>
                <tr>
                    <th><label for="surface_color">Scratch Surface Color</label></th>
                    <td><input type="text" name="surface_color" class="regular-text" placeholder="#999999"></td>
                </tr>
                <tr>
                    <th><label for="max_wins">Max Wins</label></th>
                    <td><input type="number" name="max_wins" placeholder="Optional"></td>
                </tr>
                <tr>
                    <th><label for="expiration">Expiration Date</label></th>
                    <td><input type="date" name="expiration"></td>
                </tr>
                <tr>
                    <th><label for="email_capture">Require Email Before Scratch</label></th>
                    <td><input type="checkbox" name="email_capture" value="1"> Enable lead capture</td>
                </tr>
                <tr>
                    <th><label for="webhook_url">Webhook URL (optional)</label></th>
                    <td><input type="text" name="webhook_url" class="regular-text" placeholder="https://your-crm-endpoint.com/webhook"></td>
                </tr>
            </table>

            <?php submit_button('Create Scratch Card', 'primary', 'aiscratch_submit'); ?>
        </form>
    </div>
    <?php
}
