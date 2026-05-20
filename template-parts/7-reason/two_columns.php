<?php
defined('ABSPATH') || exit;

$title_two_columns = get_field('title_two_columns');
$text_two_columns = get_field('text_two_columns');
$repeater_two_columns = get_field('repeater_two_columns');

?>

<section class="two-columns">
    <div class="container">
    <?php if ($title_two_columns): ?>
        <div class="two-columns__header"><?php echo wp_kses_post($title_two_columns); ?></div>
    <?php endif; ?>

    <?php if ($text_two_columns): ?>
        <p class="two-columns__subtitle"><?php echo esc_html($text_two_columns); ?></p>
    <?php endif; ?>

    <?php if ($repeater_two_columns): ?>
        <div class="two-columns__grid">
            <?php foreach ($repeater_two_columns as $item): ?>
                <div class="two-columns__card"><?php echo wp_kses_post($item['text_repeater_two_columns'] ?? ''); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    </div>
</section>