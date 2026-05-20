<?php
defined('ABSPATH') || exit;

$textr_main_repeater = get_field('textr_main_repeater');
$repeater_main_repeater = get_field('repeater_main_repeater');

?>

<section class="reason-repeater">
    <?php if ($textr_main_repeater): ?>
        <div class="reason-repeater__header"><?php echo wp_kses_post($textr_main_repeater); ?></div>
    <?php endif; ?>

    <?php if ($repeater_main_repeater): ?>
        <div class="reason-repeater__list">
            <?php foreach ($repeater_main_repeater as $index => $row): ?>
                <?php
                $number = $index + 1;
                $parity = ($number % 2 === 1) ? 'odd' : 'even';
                ?>
                <div class="reason-repeater__row reason-repeater__row--<?php echo esc_attr($parity); ?>">
                    <div class="reason-repeater__inner container">
                        <div class="reason-repeater__text">
                            <div class="reason-repeater__num"><span><?php echo (int) $number; ?></span></div>
                            <p class="reason-repeater__title"><?php echo esc_html($row['title_repeater_main_repeater'] ?? ''); ?></p>
                            <div class="reason-repeater__content">
                                <?php echo wp_kses_post($row['text_repeater_main_repeater'] ?? ''); ?>
                            </div>
                        </div>

                        <?php if (! empty($row['image_repeater_main_repeater']['url'])): ?>
                            <div class="reason-repeater__media">
                                <img
                                    class="reason-repeater__img"
                                    loading="lazy"
                                    src="<?php echo esc_url($row['image_repeater_main_repeater']['url']); ?>"
                                    alt="<?php echo esc_attr($row['image_repeater_main_repeater']['alt'] ?? ''); ?>">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>