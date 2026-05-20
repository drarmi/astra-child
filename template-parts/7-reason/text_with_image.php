<?php
defined('ABSPATH') || exit;

$text_text_with_image = get_field('text_text_with_image');
$image_text_with_image = get_field('image_text_with_image');


?>

<section class="text-with-image container">
    <div class="text-with-image__inner">
        <?php if ($text_text_with_image): ?>
            <div class="text-with-image__content"><?php echo wp_kses_post($text_text_with_image); ?></div>
        <?php endif; ?>

        <?php if ($image_text_with_image): ?>
            <div class="text-with-image__media">
                <img
                    class="text-with-image__img"
                    loading="lazy"
                    src="<?php echo esc_url($image_text_with_image['url'] ?? ''); ?>"
                    alt="<?php echo esc_attr($image_text_with_image['alt'] ?? ''); ?>"
                >
            </div>
        <?php endif; ?>
    </div>
</section>