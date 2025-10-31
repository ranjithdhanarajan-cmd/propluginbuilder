<?php
if (!defined('ABSPATH')) exit;

$instance_id    = 'aiscratch_' . uniqid('', true);
$prize_type     = $card->prize_type ? sanitize_text_field($card->prize_type) : 'text';
$prize_content  = $card->prize_content;
$has_prize      = $prize_type !== 'none' && !empty($prize_content);
$cover_image    = $cover_image ? esc_url($cover_image) : '';
$surface_color  = esc_attr($surface_color);
$requires_lead  = !empty($requires_lead);
$lose_message   = __('Try Again Tomorrow!', 'ai-scratch-card-rewards');
$prize_value    = $has_prize ? wp_strip_all_tags($prize_content) : $lose_message;
$default_result = $has_prize ? 'win' : 'lose';
?>
    <div
    class="aiscratch-card-container"
    id="<?php echo esc_attr($instance_id); ?>"
    data-card-id="<?php echo esc_attr($card->id); ?>"
>
  <div class="aiscratch-scratchpad" style="position:relative;">
   <div
      class="aiscratch-hidden-content"
      style="display:none;"
      data-prize-value="<?php echo esc_attr($prize_value); ?>"
      data-default-result="<?php echo esc_attr($default_result); ?>"
    >
      <?php if ($has_prize && ($prize_type === 'coupon' || $prize_type === 'text')): ?>
        <p class="aiscratch-prize"><?php echo esc_html($prize_content); ?></p>
      <?php elseif ($has_prize && $prize_type === 'link'): ?>
        <a href="<?php echo esc_url($prize_content); ?>" class="aiscratch-prize" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Click to claim your reward!', 'ai-scratch-card-rewards'); ?></a>
      <?php elseif ($has_prize && $prize_type === 'image'): ?>
        <img src="<?php echo esc_url($prize_content); ?>" alt="<?php esc_attr_e('Prize Image', 'ai-scratch-card-rewards'); ?>" class="aiscratch-prize-img" />
      <?php else: ?>
         <p class="aiscratch-lose"><?php echo esc_html($lose_message); ?></p>
      <?php endif; ?>
    </div>

   <div
      class="aiscratch-canvas"
      data-surface-color="<?php echo $surface_color; ?>"
      data-cover-image="<?php echo $cover_image; ?>"
    ></div>

 <?php if ($requires_lead) : ?>
      <div class="aiscratch-lead-form">
        <input type="text" placeholder="<?php esc_attr_e('Your Name', 'ai-scratch-card-rewards'); ?>" class="aiscratch-name">
        <input type="email" placeholder="<?php esc_attr_e('Your Email', 'ai-scratch-card-rewards'); ?>" class="aiscratch-email">
        <label>
          <input type="checkbox" class="aiscratch-consent"> <?php esc_html_e('I agree to receive emails', 'ai-scratch-card-rewards'); ?>
        </label>
        <button type="button" class="aiscratch-submit-lead"><?php esc_html_e('Start Scratching', 'ai-scratch-card-rewards'); ?></button>
      </div>
    <?php endif; ?>
  </div>
</div>
