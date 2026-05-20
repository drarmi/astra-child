<?php
defined('ABSPATH') || exit;

$top_text_banner_with_fixed_button = get_field('top_text_banner_with_fixed_button');
$title_banner_with_fixed_button = get_field('title_banner_with_fixed_button');
$subtitle_banner_with_fixed_button = get_field('subtitle_banner_with_fixed_button');
$button_banner_with_fixed_button = get_field('button_banner_with_fixed_button');
$text_banner_with_fixed_button = get_field('text_banner_with_fixed_button');
$gallery_banner_with_fixed_button = get_field('gallery_banner_with_fixed_button');
?>

<div class="banner-with-fixed-button">
    <div class="container">
        <div class="banner-with-fixed-button__intro">
            <?php if ($top_text_banner_with_fixed_button): ?>
                <p class="banner-with-fixed-button__badge"><?php echo esc_html($top_text_banner_with_fixed_button); ?></p>
            <?php endif; ?>

            <?php if ($title_banner_with_fixed_button): ?>
                <h1 class="banner-with-fixed-button__title"><?php echo esc_html($title_banner_with_fixed_button); ?></h1>
            <?php endif; ?>

            <?php if ($subtitle_banner_with_fixed_button): ?>
                <p class="banner-with-fixed-button__subtitle"><?php echo esc_html($subtitle_banner_with_fixed_button); ?></p>
            <?php endif; ?>

            <?php if ($button_banner_with_fixed_button): ?>
                <div class="banner-fixed-button">
                    <div class="banner-fixed-button__bar">
                        <a
                            class="banner-fixed-button__link"
                            href="<?php echo esc_url($button_banner_with_fixed_button['url']); ?>"
                            <?php echo $button_banner_with_fixed_button['target'] ? ' target="' . esc_attr($button_banner_with_fixed_button['target']) . '"' : ''; ?>><?php echo esc_html($button_banner_with_fixed_button['title']); ?></a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($text_banner_with_fixed_button): ?>
                <p class="banner-with-fixed-button__note"><?php echo esc_html($text_banner_with_fixed_button); ?></p>
            <?php endif; ?>
        </div>

        <?php if ($gallery_banner_with_fixed_button): ?>
            <div class="banner-with-fixed-button__gallery">
                <?php foreach ($gallery_banner_with_fixed_button as $image): ?>
                    <img
                        class="banner-with-fixed-button__gallery-img"
                        loading="lazy"
                        src="<?php echo esc_url($image['url'] ?? ''); ?>"
                        alt="<?php echo esc_attr($image['alt'] ?? ''); ?>">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>