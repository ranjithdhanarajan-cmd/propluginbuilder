<?php
if (!defined('ABSPATH')) exit;

// Unique ID for this card instance
$instance_id = 'aiscratch_' . uniqid();
$cover_image = esc_url($card->cover_image);
$surface_color = esc_attr($card->surface_color);
$prize_type = esc_attr($card->prize_type);
$prize_content = esc_html($card->prize_content);

// For this example, we'll show the prize text directly
?>
<div class="aiscratch-card-container" id="<?php echo $instance_id; ?>" data-card-id="<?php echo $card->id; ?>">
  <div class="aiscratch-scratchpad" style="position:relative;">
    <div class="aiscratch-hidden-content" style="display:none;">
      <?php if ($prize_type === 'coupon' || $prize_type === 'text'): ?>
        <p class="aiscratch-prize"><?php echo $prize_content; ?></p>
      <?php elseif ($prize_type === 'link'): ?>
        <a href="<?php echo esc_url($prize_content); ?>" class="aiscratch-prize" target="_blank">Click to claim your reward!</a>
      <?php elseif ($prize_type === 'image'): ?>
        <img src="<?php echo esc_url($prize_content); ?>" alt="Prize Image" class="aiscratch-prize-img" />
      <?php else: ?>
        <p class="aiscratch-lose">Try Again Tomorrow!</p>
      <?php endif; ?>
    </div>

    <!-- Scratch Canvas -->
    <div class="aiscratch-canvas"></div>

    <!-- Optional Lead Capture Placeholder -->
    <div class="aiscratch-lead-form" style="display:none;">
      <input type="text" placeholder="Your Name" class="aiscratch-name">
      <input type="email" placeholder="Your Email" class="aiscratch-email">
      <label>
        <input type="checkbox" class="aiscratch-consent"> I agree to receive emails
      </label>
      <button class="aiscratch-submit-lead">Start Scratching</button>
    </div>
  </div>
</div>
