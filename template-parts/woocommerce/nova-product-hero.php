<?php
/**
 * Product page hero: gallery + summary from WooCommerce (and optional ACF).
 *
 * @package Astra Child
 */

defined( 'ABSPATH' ) || exit;

$pid = get_queried_object_id();
if ( ! $pid ) {
	return;
}

global $product;

if ( ! $product instanceof WC_Product || (int) $product->get_id() !== (int) $pid ) {
	$product = wc_get_product( $pid );
}

if ( ! $product instanceof WC_Product ) {
	return;
}

$gf = static function ( $name ) {
	return function_exists( 'get_field' ) ? get_field( $name ) : null;
};

/**
 * @param mixed $img ACF image (array|int) or null.
 * @return int Attachment ID or 0.
 */
$nova_hero_img_id = static function ( $img ) {
	if ( is_numeric( $img ) ) {
		return (int) $img;
	}
	if ( is_array( $img ) && ! empty( $img['ID'] ) ) {
		return (int) $img['ID'];
	}
	return 0;
};

$usage_line      = $gf( 'nova_hero_usage_line' );
$repurchase_line = $gf( 'nova_hero_repurchase_line' );
$trust_id         = $nova_hero_img_id( $gf( 'nova_hero_trust_image' ) );

$tests             = $gf( 'nova_hero_testimonials' );
$delivery_features = $gf( 'nova_hero_delivery_features' );
$how_title         = $gf( 'nova_hero_how_title' );
$how_rows          = $gf( 'nova_hero_how_rows' );

if ( null === $usage_line || false === $usage_line ) {
	$usage_line = 'מספיק לכ־60 ימי שימוש יומיומי';
}
if ( null === $repurchase_line || false === $repurchase_line ) {
	$repurchase_line = 'הבחירה של הלקוחות הקבועות: נרכש מחדש כל 60 יום בממוצע';
}
$usage_line      = is_string( $usage_line ) ? trim( $usage_line ) : '';
$repurchase_line = is_string( $repurchase_line ) ? trim( $repurchase_line ) : '';

if ( null === $delivery_features || false === $delivery_features || ! is_array( $delivery_features ) ) {
	$delivery_features = array(
		array( 'text' => '3 דוגמיות מיוחדות (סה״כ 15 מ״ל) חינם בכל קנייה' ),
		array( 'text' => 'החזר כספי מובטח עד 30 יום' ),
		array( 'text' => 'משלוח חינם, תוך 48 שעות לכל הארץ' ),
	);
}

$how_title = is_string( $how_title ) && '' !== trim( $how_title ) ? trim( $how_title ) : 'איך להשתמש';

if ( null === $how_rows || false === $how_rows || ! is_array( $how_rows ) ) {
	$how_rows = array(
		array(
			'label' => 'מתי',
			'value' => 'בערב, על עור נקי',
		),
		array(
			'label' => 'כמה',
			'value' => 'בגודל אפונה',
		),
		array(
			'label' => 'איך',
			'value' => 'עיסוי עדין עד ספיגה מלאה',
		),
		array(
			'label' => 'טיפ',
			'value' => 'שלבי עם קרם לחות לתוצאות מיטביות',
		),
	);
}

/**
 * @param array $row Delivery row.
 * @return int
 */
$nova_delivery_icon_id = static function ( $row ) use ( $nova_hero_img_id ) {
	if ( ! empty( $row['icon_image'] ) ) {
		return $nova_hero_img_id( $row['icon_image'] );
	}
	return 0;
};

/**
 * @param array $row Delivery row (optional legacy `icon` meta).
 * @return string
 */
$nova_delivery_icon_class = static function ( $row ) {
	if ( ! empty( $row['icon'] ) && is_string( $row['icon'] ) ) {
		return trim( $row['icon'] );
	}
	return '';
};
?>
<section class="nova-product-hero" aria-label="<?php echo esc_attr( $product->get_name() ); ?>">
	<div class="nova-product-hero__grid">
		<div class="nova-product-hero__media">
			<div class="woocommerce">
				<?php woocommerce_show_product_images(); ?>
			</div>
		</div>

		<div class="nova-product-hero__summary">
			<?php if ( function_exists( 'woocommerce_breadcrumb' ) ) : ?>
				<nav class="nova-product-hero__breadcrumbs woocommerce" aria-label="<?php echo esc_attr__( 'Breadcrumb', 'woocommerce' ); ?>">
					<?php woocommerce_breadcrumb(); ?>
				</nav>
			<?php endif; ?>

			<h1 class="nova-product-hero__title"><?php echo esc_html( $product->get_name() ); ?></h1>

			<div class="nova-product-hero__price-usage">
				<div class="nova-product-hero__price">
					<?php woocommerce_template_single_price(); ?>
				</div>
				<?php if ( '' !== $usage_line ) : ?>
					<p class="nova-product-hero__usage-line">
					<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M12.8337 7.00002C12.8337 10.22 10.2203 12.8334 7.00033 12.8334C3.78033 12.8334 1.16699 10.22 1.16699 7.00002C1.16699 3.78002 3.78033 1.16669 7.00033 1.16669C10.2203 1.16669 12.8337 3.78002 12.8337 7.00002Z" stroke="#A95775" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M9.16418 8.85503L7.35585 7.77586C7.04085 7.58919 6.78418 7.14003 6.78418 6.77253V4.38086" stroke="#A95775" stroke-linecap="round" stroke-linejoin="round"/>
</svg>

						<?php echo esc_html( $usage_line ); ?>
					</p>
				<?php endif; ?>
			</div>

			<div class="nova-product-hero__rating">
				<div class="flashy-star-rating"></div>
			</div>

			<?php if ( is_array( $tests ) && $tests ) : ?>
				<ul class="nova-product-hero__testimonials">
					<?php foreach ( $tests as $row ) : ?>
						<?php
						$quote = isset( $row['quote'] ) ? $row['quote'] : '';
						$name  = isset( $row['name'] ) ? $row['name'] : '';
						$role  = isset( $row['subtitle'] ) ? $row['subtitle'] : '';
						$img   = isset( $row['image'] ) ? $row['image'] : null;
						if ( ! $quote ) {
							continue;
						}
						$img_id = $nova_hero_img_id( $img );
						?>
						<li class="nova-product-hero__testimonial">
							

									<?php
									if ( $img_id ) {
										echo wp_get_attachment_image( $img_id, 'thumbnail', false, array( 'alt' => $name ? esc_attr( $name ) : '' ) );
									}
									?>
									<span class="nova-product-hero__testimonial-meta">
										<?php echo wp_kses_post( wpautop( $quote ) ); ?></blockquote>
								
										<?php if ( $name ) : ?>
											<span class="nova-product-hero__testimonial-name"><?php echo esc_html( $name ); ?></span>
										<?php endif; ?>
										<?php if ( $role ) : ?>
											<span class="nova-product-hero__testimonial-role"><?php echo esc_html( $role ); ?></span>
										<?php endif; ?>
									</span>
								
							
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( '' !== $repurchase_line ) : ?>
				<p class="nova-product-hero__repurchase">
				<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M12.8337 7.00002C12.8337 10.22 10.2203 12.8334 7.00033 12.8334C3.78033 12.8334 1.81449 9.59002 1.81449 9.59002M1.81449 9.59002H4.45116M1.81449 9.59002V12.5067M1.16699 7.00002C1.16699 3.78002 3.75699 1.16669 7.00033 1.16669C10.8912 1.16669 12.8337 4.41002 12.8337 4.41002M12.8337 4.41002V1.49335M12.8337 4.41002H10.2437" stroke="#757575" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
	
				<?php echo esc_html( $repurchase_line ); ?></p>
			<?php endif; ?>

			<div class="nova-product-hero__cart nova-product-hero__cart--primary woocommerce">
				<?php woocommerce_template_single_add_to_cart(); ?>
			</div>

			<div class="nova-product-hero__short-desc woocommerce-product-details__short-description">
				<?php woocommerce_template_single_excerpt(); ?>
			</div>

			<?php if ( is_array( $delivery_features ) && $delivery_features ) : ?>
				<ul class="nova-product-hero__icon-list nova-product-hero__icon-list--delivery">
					<?php foreach ( $delivery_features as $row ) : ?>
						<?php
						$txt     = isset( $row['text'] ) ? (string) $row['text'] : '';
						$icon_id = $nova_delivery_icon_id( $row );
						$icon_cl = $nova_delivery_icon_class( $row );
						if ( '' === trim( wp_strip_all_tags( $txt ) ) ) {
							continue;
						}
						?>
						<li>
							<?php if ( $icon_id ) : ?>
								<span class="nova-product-hero__list-icon-img" aria-hidden="true">
									<?php echo wp_get_attachment_image( $icon_id, 'thumbnail', false, array( 'loading' => 'lazy' ) ); ?>
								</span>
							<?php elseif ( $icon_cl ) : ?>
								<span class="nova-product-hero__bullet-icon" aria-hidden="true"><i class="<?php echo esc_attr( $icon_cl ); ?>"></i></span>
							<?php endif; ?>
							<span class="nova-product-hero__bullet-text"><?php echo esc_html( $txt ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( is_array( $how_rows ) && $how_rows ) : ?>
				<section class="nova-product-hero__how" aria-labelledby="nova-hero-how-heading">
					<h2 id="nova-hero-how-heading" class="nova-product-hero__how-heading"><?php echo esc_html( $how_title ); ?></h2>
					<div class="nova-product-hero__how-rows">
						<?php foreach ( $how_rows as $row ) : ?>
							<?php
							$hl      = isset( $row['label'] ) ? trim( (string) $row['label'] ) : '';
							$hv      = isset( $row['value'] ) ? trim( (string) $row['value'] ) : '';
							$icon_id = ! empty( $row['icon_image'] ) ? $nova_hero_img_id( $row['icon_image'] ) : 0;
							if ( '' === $hl && '' === $hv ) {
								continue;
							}
							?>
							<div class="nova-product-hero__how-row">
								<?php if ( $icon_id ) : ?>
									<div class="nova-product-hero__how-row-icon" aria-hidden="true">
										<?php echo wp_get_attachment_image( $icon_id, 'thumbnail', false, array( 'loading' => 'lazy' ) ); ?>
									</div>
								<?php endif; ?>
								<div class="nova-product-hero__how-row-copy">
									<?php if ( '' !== $hl ) : ?>
										<span class="nova-product-hero__how-label"><?php echo esc_html( $hl ); ?></span>
									<?php endif; ?>
									<?php if ( '' !== $hv ) : ?>
										<span class="nova-product-hero__how-value"><?php echo nl2br( esc_html( $hv ), false ); ?></span>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( $trust_id ) : ?>
				<div class="nova-product-hero__trust">
					<?php echo wp_get_attachment_image( $trust_id, 'medium', false, array( 'loading' => 'lazy' ) ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
