<?php
defined('ABSPATH') || exit;

$image_button_with_image = get_field('image_button_with_image');
$text_button_with_image = get_field('text_button_with_image');
$button_button_with_image = get_field('button_button_with_image');
$bottom_text_button_with_image = get_field('bottom_text_button_with_image');

?>

<section class="button-with-image container">
    <div class="button-with-image__inner">
        <?php if ($text_button_with_image || $button_button_with_image || $bottom_text_button_with_image): ?>
            <div class="button-with-image__content">
                <?php if ($text_button_with_image): ?>
                    <div class="button-with-image__text"><?php echo wp_kses_post($text_button_with_image); ?></div>
                <?php endif; ?>

                <div class="button-wrapper">
                    <?php if ($button_button_with_image): ?>
                        <a href="<?php echo esc_url($button_button_with_image['url'] ?? ''); ?>" target="<?php echo esc_attr($button_button_with_image['target'] ?? ''); ?>"><?php echo esc_html($button_button_with_image['title'] ?? ''); ?></a>
                    <?php endif; ?>
                    <?php if ($bottom_text_button_with_image): ?>
                        <p class="button-with-image__bottom"><?php echo esc_html($bottom_text_button_with_image); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($image_button_with_image): ?>
            <div class="button-with-image__media">
                <img
                    class="button-with-image__img"
                    loading="lazy"
                    src="<?php echo esc_url($image_button_with_image['url'] ?? ''); ?>"
                    alt="<?php echo esc_attr($image_button_with_image['alt'] ?? ''); ?>">
            </div>
        <?php endif; ?>
    </div>
</section>