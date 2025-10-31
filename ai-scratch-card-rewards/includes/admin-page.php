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
