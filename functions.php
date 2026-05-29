<?php
/**
 * Astra Child — Enjoy Nova
 *
 * @package Astra Child
 */

defined( 'ABSPATH' ) || exit;

define( 'NOVA_CHILD_DIR', get_stylesheet_directory() );
define( 'NOVA_CHILD_URI', get_stylesheet_directory_uri() );

if(file_exists(NOVA_CHILD_DIR . '/includes/nova-rotating-banner.php')) {
    require_once NOVA_CHILD_DIR . '/includes/nova-rotating-banner.php';
}

/**
 * Enqueue parent + child styles.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		$file_ver = static function ( $path ) {
			return is_string( $path ) && is_file( $path ) ? (string) filemtime( $path ) : gmdate( 'YmdHis' );
		};

		$parent_css = get_template_directory() . '/style.css';
		$child_css  = get_stylesheet_directory() . '/style.css';

		wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css', array(), $file_ver( $parent_css ) );
		wp_enqueue_style( 'astra-child-style', get_stylesheet_uri(), array( 'parent-style' ), $file_ver( $child_css ) );

		if ( is_page_template( 'page-templates/7-reason.php' ) ) {
			$landing_css = NOVA_CHILD_DIR . '/assets/css/nova-landing.css';
			wp_enqueue_style(
				'enjoy-nova-landing',
				NOVA_CHILD_URI . '/assets/css/nova-landing.css',
				array( 'astra-child-style' ),
				$file_ver( $landing_css )
			);
			$theme_fonts_css = NOVA_CHILD_DIR . '/assets/css/nova-theme-fonts.css';
			wp_enqueue_style(
				'enjoy-nova-theme-fonts',
				NOVA_CHILD_URI . '/assets/css/nova-theme-fonts.css',
				array( 'enjoy-nova-landing' ),
				$file_ver( $theme_fonts_css )
			);
			$reason_css = NOVA_CHILD_DIR . '/assets/css/7-reason.css';
			wp_enqueue_style(
				'enjoy-nova-7-reason',
				NOVA_CHILD_URI . '/assets/css/7-reason.css',
				array( 'enjoy-nova-theme-fonts' ),
				$file_ver( $reason_css )
			);
			$reason_js = NOVA_CHILD_DIR . '/assets/js/7-reason.js';
			wp_enqueue_script(
				'enjoy-nova-7-reason',
				NOVA_CHILD_URI . '/assets/js/7-reason.js',
				array(),
				$file_ver( $reason_js ),
				true
			);
		}

		if (
			function_exists( 'is_checkout' )
			&& is_checkout()
			&& ! is_order_received_page()
			&& ( ! function_exists( 'nova_checkout_has_cf_redirect' ) || ! nova_checkout_has_cf_redirect() )
		) {
			$checkout_deps = wp_style_is( 'woocommerce-general', 'registered' ) ? array( 'woocommerce-general' ) : array( 'astra-child-style' );
			$checkout_css  = NOVA_CHILD_DIR . '/assets/css/nova-checkout.css';
			wp_enqueue_style(
				'enjoy-nova-checkout',
				NOVA_CHILD_URI . '/assets/css/nova-checkout.css',
				$checkout_deps,
				$file_ver( $checkout_css )
			);

			$checkout_js = NOVA_CHILD_DIR . '/assets/js/nova-checkout.js';
			if ( is_file( $checkout_js ) ) {
				$checkout_script_deps = array( 'jquery' );
				if ( wp_script_is( 'wc-checkout', 'registered' ) ) {
					$checkout_script_deps[] = 'wc-checkout';
				}
				wp_enqueue_script(
					'enjoy-nova-checkout',
					NOVA_CHILD_URI . '/assets/js/nova-checkout.js',
					$checkout_script_deps,
					$file_ver( $checkout_js ),
					true
				);
				wp_localize_script(
					'enjoy-nova-checkout',
					'novaCheckout',
					array(
						'ajax_url'      => admin_url( 'admin-ajax.php' ),
						'remove_action' => 'wcf_woo_remove_cart_product',
						'remove_nonce'  => wp_create_nonce( 'wcf-remove-cart-product' ),
					)
				);
			}
		}

		if ( is_product() ) {
			$deps = wp_style_is( 'woocommerce-general', 'registered' ) ? array( 'woocommerce-general' ) : array();
			// Old handle may still be registered (cache/snippets) with ver=1.0.0 — drop it, use a new handle + filemtime.
			wp_dequeue_style( 'nova-product-hero' );
			wp_deregister_style( 'nova-product-hero' );
			$hero_path = NOVA_CHILD_DIR . '/assets/css/nova-product-hero.css';
			wp_enqueue_style(
				'enjoy-nova-product-hero',
				NOVA_CHILD_URI . '/assets/css/nova-product-hero.css',
				$deps,
				$file_ver( $hero_path )
			);

			$sticky_css = NOVA_CHILD_DIR . '/assets/css/nova-sticky-cart-bar.css';
			$sticky_js  = NOVA_CHILD_DIR . '/assets/js/nova-sticky-cart-bar.js';
			wp_enqueue_style(
				'enjoy-nova-sticky-cart-bar',
				NOVA_CHILD_URI . '/assets/css/nova-sticky-cart-bar.css',
				array( 'enjoy-nova-product-hero' ),
				$file_ver( $sticky_css )
			);
			wp_enqueue_script(
				'enjoy-nova-sticky-cart-bar',
				NOVA_CHILD_URI . '/assets/js/nova-sticky-cart-bar.js',
				array( 'jquery' ),
				$file_ver( $sticky_js ),
				true
			);
			$flashy_label_js = NOVA_CHILD_DIR . '/assets/js/nova-flashy-rating-label.js';
			if ( is_file( $flashy_label_js ) ) {
				wp_enqueue_script(
					'enjoy-nova-flashy-rating-label',
					NOVA_CHILD_URI . '/assets/js/nova-flashy-rating-label.js',
					array(),
					$file_ver( $flashy_label_js ),
					true
				);
				wp_localize_script(
					'enjoy-nova-flashy-rating-label',
					'novaFlashyRating',
					array(
						'label' => (string) apply_filters( 'nova_flashy_rating_reviews_label', 'ביקורות' ),
					)
				);
			}
		}
	},
	99
);

/**
 * Restore filemtime cache-busting for child theme CSS in assets/css/.
 *
 * Theme Editor (ms_theme_editor_src) replaces ver= with wp_get_theme()->Version (1.0.0)
 * for any stylesheet URL containing the child theme slug. Scripts are unaffected.
 *
 * @param string|false $src    Style URL.
 * @param string       $handle Style handle (unused but required by filter).
 * @return string|false
 */
add_filter(
	'style_loader_src',
	function ( $src, $_handle ) {
		if ( ! is_string( $src ) || '' === $src ) {
			return $src;
		}
		$assets_prefix = NOVA_CHILD_URI . '/assets/css/';
		if ( false === strpos( $src, $assets_prefix ) ) {
			return $src;
		}
		$file = basename( strtok( $src, '?' ) );
		if ( ! is_string( $file ) || '' === $file ) {
			return $src;
		}
		$path = NOVA_CHILD_DIR . '/assets/css/' . $file;
		$ver  = is_file( $path ) ? (string) filemtime( $path ) : (string) time();
		return add_query_arg( 'ver', $ver, remove_query_arg( 'ver', $src ) );
	},
	99999,
	2
);

/**
 * WooCommerce single-product gallery: arrows + dot pager (Flexslider).
 */
add_filter(
	'woocommerce_single_product_carousel_options',
	function ( $options ) {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return $options;
		}
		if ( ! is_array( $options ) ) {
			return $options;
		}
		$options['directionNav'] = true;
		$options['controlNav']   = true;
		$options['rtl']          = is_rtl();
		return $options;
	},
	100
);

/**
 * Disable WooCommerce image zoom on hover (single product gallery).
 */
add_filter( 'woocommerce_single_product_zoom_enabled', '__return_false' );

/**
 * Keep product gallery images non-clickable, matching the live-site behavior.
 */
add_filter( 'woocommerce_single_product_photoswipe_enabled', '__return_false' );

add_action(
	'after_setup_theme',
	function () {
		remove_theme_support( 'wc-product-gallery-lightbox' );
	},
	100
);

add_filter(
	'woocommerce_product_get_gallery_image_ids',
	function ( $gallery_image_ids, $product ) {
		if ( ! function_exists( 'is_product' ) || ! is_product() || ! ( $product instanceof WC_Product ) ) {
			return $gallery_image_ids;
		}

		$main_image_id = (int) $product->get_image_id();
		if ( ! $main_image_id || ! is_array( $gallery_image_ids ) ) {
			return $gallery_image_ids;
		}

		return array_values(
			array_filter(
				$gallery_image_ids,
				static function ( $gallery_image_id ) use ( $main_image_id ) {
					return (int) $gallery_image_id !== $main_image_id;
				}
			)
		);
	},
	100,
	2
);

add_filter(
	'woocommerce_single_product_image_thumbnail_html',
	function ( $html, $attachment_id ) {
		if ( ! function_exists( 'is_product' ) || ! is_product() || ! is_string( $html ) ) {
			return $html;
		}

		global $product;
		if ( $product instanceof WC_Product && (int) $attachment_id === (int) $product->get_image_id() ) {
			return '';
		}

		$unlinked_html = preg_replace( '#<a\b[^>]*>\s*(<img\b[^>]*>)\s*</a>#i', '$1', $html );
		return is_string( $unlinked_html ) ? $unlinked_html : $html;
	},
	100,
	2
);

/**
 * Chevron SVG for quantity stepper (up = increase).
 *
 * @return string
 */
function nova_get_qty_stepper_svg_up() {
	return '<svg class="nova-qty__svg" width="22" height="20" viewBox="0 0 22 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M16.059 13.5583L11.0007 8.5L5.94238 13.5583" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/**
 * Chevron SVG for quantity stepper (down = decrease).
 *
 * @return string
 */
function nova_get_qty_stepper_svg_down() {
	return '<svg class="nova-qty__svg" width="22" height="20" viewBox="0 0 22 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M5.94095 6.44167L10.9993 11.5L16.0576 6.44167" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/**
 * Open quantity stepper wrapper (label + input follow from WooCommerce).
 *
 * @return void
 */
function nova_wc_quantity_stepper_before() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	echo '<div class="nova-qty nova-qty--stepper">';
}

/**
 * Vertical spin buttons (up / down) to the right of the input, then close wrapper.
 *
 * @return void
 */
function nova_wc_quantity_stepper_after() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	echo '<div class="nova-qty__spin">';
	echo '<button type="button" class="nova-qty__btn nova-qty__btn--up" aria-label="' . esc_attr__( 'Increase quantity', 'woocommerce' ) . '">' . nova_get_qty_stepper_svg_up() . '</button>';
	echo '<button type="button" class="nova-qty__btn nova-qty__btn--down" aria-label="' . esc_attr__( 'Reduce quantity', 'woocommerce' ) . '">' . nova_get_qty_stepper_svg_down() . '</button>';
	echo '</div></div>';
}

add_action( 'woocommerce_before_quantity_input_field', 'nova_wc_quantity_stepper_before', 5 );
add_action( 'woocommerce_after_quantity_input_field', 'nova_wc_quantity_stepper_after', 15 );

/**
 * Product hero: Flashy stars + review count with Hebrew label.
 *
 * Uses flashy_get_product_reviews() when the Flashy plugin is active.
 * Falls back to the JS placeholder if the API helper is unavailable.
 *
 * @param WC_Product|null $product Product; defaults to global $product.
 * @return void
 */
function nova_render_product_hero_flashy_rating( $product = null ) {
	if ( ! function_exists( 'flashy_get_product_reviews' ) ) {
		echo '<motion class="flashy-star-rating"></motion>';
		return;
	}
	

	echo '<div class="nova-flashy-rating">';
	echo '<motion class="flashy-star-rating"></motion>';
	echo '</div>';
}

/**
 * Print the custom product hero once per request.
 *
 * @return void
 */
function nova_render_product_hero_markup() {
	get_template_part( 'template-parts/woocommerce/nova-product-hero' );
}

/**
 * Bottom sticky purchase bar (single product).
 *
 * @return void
 */
function nova_render_sticky_cart_bar_markup() {
	get_template_part( 'template-parts/woocommerce/nova-sticky-cart-bar' );
}

/**
 * Gate: single product, then render sticky bar once (footer + shortcode share this).
 *
 * @return void
 */
function nova_try_render_sticky_cart_bar() {
	static $done = false;
	if ( $done ) {
		return;
	}
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	if ( ! function_exists( 'WC' ) ) {
		return;
	}

	global $product;
	$pid = get_queried_object_id();
	$p   = $product instanceof WC_Product ? $product : wc_get_product( $pid );

	/**
	 * Return false to skip the sticky cart bar on a product.
	 *
	 * @param bool            $enabled Whether to render the bar.
	 * @param WC_Product|bool $p       Product object or false.
	 */
	if ( ! apply_filters( 'nova_sticky_cart_bar_enabled', true, $p ) ) {
		return;
	}

	$done = true;
	nova_render_sticky_cart_bar_markup();
}

add_action( 'wp_footer', 'nova_try_render_sticky_cart_bar', 20 );

/**
 * Gate: single product, filter, then render once (avoids duplicate if several hooks fire).
 *
 * @return void
 */
function nova_try_render_product_hero() {
	static $done = false;
	if ( $done ) {
		return;
	}
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	global $product;
	$pid = get_queried_object_id();
	$p   = $product instanceof WC_Product ? $product : wc_get_product( $pid );

	/**
	 * Return false to skip the PHP hero on a product.
	 *
	 * @param bool            $enabled Whether to render the hero.
	 * @param WC_Product|bool $p       Product object or false.
	 */
	if ( ! apply_filters( 'nova_product_hero_enabled', true, $p ) ) {
		return;
	}

	$done = true;
	nova_render_product_hero_markup();
}

/*
 * Hook 1: inside WooCommerce single product (most reliable with Elementor + WC).
 * Fires from content-single-product.php after the_post().
 */
add_action( 'woocommerce_before_single_product', 'nova_try_render_product_hero', 1 );

/*
 * Hook 2: Astra before main inner content (covers classic WC + Astra wrapper).
 * If hook 1 already ran, nova_try_render_product_hero is a no-op.
 */
add_action( 'astra_primary_content_top', 'nova_try_render_product_hero', 5 );

/*
 * Hook 3: Elementor Theme Builder (single) when the document is rendered without WC single wrapper.
 */
add_action(
	'elementor/theme/before_do_single',
	function () {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}
		nova_try_render_product_hero();
	},
	1
);

/**
 * After migrating fully, delete the duplicated top section in Elementor.
 * Optional: hide legacy Elementor section by data-id (replace with your section id).
 */
/**
 * Checkout place-order button label (Hebrew).
 *
 * @param string $text Default button text.
 * @return string
 */
function nova_checkout_place_order_button_text( $text ) {
	if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_order_received_page() ) {
		/**
		 * Checkout submit button label (do not hook woocommerce_order_button_text here).
		 *
		 * @param string $label Button text.
		 */
		return (string) apply_filters( 'nova_checkout_place_order_label', 'לביצוע תשלום' );
	}
	return $text;
}
add_filter( 'woocommerce_order_button_text', 'nova_checkout_place_order_button_text', 20 );

/**
 * Card brand icons below the payment section.
 *
 * @return void
 */
function nova_render_checkout_payment_icons() {
	$icons = apply_filters(
		'nova_checkout_payment_icon_list',
		array(
			array(
				'src' => NOVA_CHILD_URI . '/assets/images/checkout/Discover.png',
				'alt' => 'Discover',
			),
			array(
				'src' => NOVA_CHILD_URI . '/assets/images/checkout/Amex.png',
				'alt' => 'American Express',
			),
			array(
				'src' => NOVA_CHILD_URI . '/assets/images/checkout/Mastercard.png',
				'alt' => 'Mastercard',
			),
			array(
				'src' => NOVA_CHILD_URI . '/assets/images/checkout/Visa.png',
				'alt' => 'Visa',
			),

		)
	);

	if ( ! is_array( $icons ) || empty( $icons ) ) {
		return;
	}

	echo '<div class="nova-checkout__payment-icons" role="group" aria-label="' . esc_attr__( 'Accepted payment methods', 'woocommerce' ) . '">';
	foreach ( $icons as $icon ) {
		if ( ! is_array( $icon ) ) {
			continue;
		}
		$src = isset( $icon['src'] ) ? (string) $icon['src'] : '';
		$alt = isset( $icon['alt'] ) ? (string) $icon['alt'] : '';
		if ( '' === $src ) {
			continue;
		}
		$path = ( 0 === strpos( $src, NOVA_CHILD_URI ) ) ? str_replace( NOVA_CHILD_URI, NOVA_CHILD_DIR, $src ) : '';
		if ( '' !== $path && ! is_file( $path ) ) {
			continue;
		}
		printf(
			'<img src="%s" alt="%s" width="48" height="24" loading="lazy" decoding="async" />',
			esc_url( $src ),
			esc_attr( $alt )
		);
	}
	echo '</div>';
}
add_action( 'nova_checkout_payment_icons', 'nova_render_checkout_payment_icons', 10 );

/**
 * Fallback card icons strip when individual SVGs are not in the theme.
 *
 * @param array $icons Icon list from nova_checkout_payment_icon_list filter.
 * @return array
 */
function nova_checkout_payment_icons_fallback( $icons ) {
	if ( ! is_array( $icons ) ) {
		$icons = array();
	}
	$has_file = false;
	foreach ( $icons as $icon ) {
		if ( ! is_array( $icon ) || empty( $icon['src'] ) ) {
			continue;
		}
		$path = ( 0 === strpos( $icon['src'], NOVA_CHILD_URI ) ) ? str_replace( NOVA_CHILD_URI, NOVA_CHILD_DIR, $icon['src'] ) : '';
		if ( '' !== $path && is_file( $path ) ) {
			$has_file = true;
			break;
		}
	}
	if ( $has_file ) {
		return $icons;
	}
	$plugin_main = WP_PLUGIN_DIR . '/woo-payment-gateway-officeguy/officeguy-woo.php';
	$cards_png   = is_file( $plugin_main ) ? plugins_url( 'includes/images/cards.png', $plugin_main ) : '';
	if ( '' === $cards_png ) {
		return $icons;
	}
	return array(
		array(
			'src' => $cards_png,
			'alt' => __( 'Visa, Mastercard, American Express, Discover', 'woocommerce' ),
		),
	);
}
add_filter( 'nova_checkout_payment_icon_list', 'nova_checkout_payment_icons_fallback', 99 );

/**
 * Use visible label above input; clear CartFlows placeholder-as-label.
 *
 * @param array<string, mixed> $field Field config (by reference).
 * @return void
 */
function nova_checkout_apply_label_above_input( &$field ) {
	if ( ! is_array( $field ) ) {
		return;
	}

	$placeholder = isset( $field['placeholder'] ) ? (string) $field['placeholder'] : '';
	if ( '' !== trim( wp_strip_all_tags( $placeholder, true ) ) ) {
		$label_text = trim(
			(string) preg_replace(
				'/\s*(&nbsp;)?\*+\s*$/u',
				'',
				wp_strip_all_tags( $placeholder, true )
			)
		);
		if ( '' !== $label_text ) {
			$field['label'] = $label_text;
		}
	}

	$field['placeholder'] = '';

	if ( ! isset( $field['class'] ) || ! is_array( $field['class'] ) ) {
		$field['class'] = array();
	}

	$field['class'] = array_diff( (array) $field['class'], array( 'screen-reader-text' ) );
	$field['class'][] = 'wcf-anim-hidden-label';
	$field['class'][] = 'nova-checkout__label-top';
}

/**
 * Personal checkout fields: order, two-column pairs, marketing checkbox after order notes.
 *
 * @param array<string, array<string, array<string, mixed>>> $fields Checkout fields.
 * @return array<string, array<string, array<string, mixed>>>
 */
function nova_checkout_personal_fields_layout( $fields ) {
	if ( ! is_array( $fields ) || ! function_exists( 'is_checkout' ) || ! is_checkout() || is_order_received_page() ) {
		return $fields;
	}

	$wide  = array( 'form-row-wide', 'wcf-anim-hidden-label', 'nova-checkout__label-top' );
	$first = array( 'form-row-first', 'wcf-anim-hidden-label', 'nova-checkout__label-top' );
	$last  = array( 'form-row-last', 'wcf-anim-hidden-label', 'nova-checkout__label-top' );

	$billing_layout = array(
		'billing_email'             => array( 'priority' => 10, 'class' => $wide ),
		'billing_first_name'        => array( 'priority' => 20, 'class' => $first ),
		'billing_last_name'         => array( 'priority' => 30, 'class' => $last ),
		'billing_country_display'   => array( 'priority' => 40, 'class' => $wide ),
		'billing_address_1'         => array( 'priority' => 50, 'class' => $first ),
		'billing_address_2'         => array( 'priority' => 60, 'class' => $last ),
		'billing_city'              => array( 'priority' => 70, 'class' => $first ),
		'billing_postcode'          => array( 'priority' => 80, 'class' => $last ),
		'billing_phone'             => array( 'priority' => 90, 'class' => $wide ),
	);

	if ( isset( $fields['billing'] ) && is_array( $fields['billing'] ) ) {
		if ( isset( $fields['billing']['billing_country'] ) ) {
			$country_field = $fields['billing']['billing_country'];
			nova_checkout_apply_label_above_input( $country_field );

			$country_label = ! empty( $country_field['label'] )
				? $country_field['label']
				: __( 'Country / Region', 'woocommerce' );

			$fields['billing']['billing_country'] = array_merge(
				$country_field,
				array(
					'type'        => 'hidden',
					'default'     => 'IL',
					'required'    => false,
					'placeholder' => '',
					'class'       => array( 'nova-checkout__field--hidden' ),
				)
			);

			$fields['billing']['billing_country_display'] = array(
				'type'              => 'text',
				'label'             => $country_label,
				'default'           => (string) apply_filters( 'nova_checkout_country_display_value', 'ישראל' ),
				'required'          => false,
				'placeholder'     => '',
				'priority'          => 40,
				'class'             => $wide,
				'custom_attributes' => array(
					'readonly'     => 'readonly',
					'tabindex'     => '-1',
					'autocomplete' => 'off',
				),
			);
		}

		foreach ( $billing_layout as $key => $layout ) {
			if ( ! isset( $fields['billing'][ $key ] ) ) {
				continue;
			}
			$fields['billing'][ $key ]['priority'] = $layout['priority'];
			$fields['billing'][ $key ]['class']    = $layout['class'];
			nova_checkout_apply_label_above_input( $fields['billing'][ $key ] );
		}

		foreach ( array( 'billing_company', 'billing_state' ) as $hidden_key ) {
			if ( isset( $fields['billing'][ $hidden_key ] ) ) {
				$fields['billing'][ $hidden_key ]['class'][] = 'nova-checkout__field--hidden';
			}
		}

		if ( isset( $fields['billing']['vuelve_marketing_consent'] ) ) {
			$consent = $fields['billing']['vuelve_marketing_consent'];
			unset( $fields['billing']['vuelve_marketing_consent'] );

			if ( ! isset( $fields['order'] ) || ! is_array( $fields['order'] ) ) {
				$fields['order'] = array();
			}

			$consent['priority'] = 20;
			$consent['class']    = array_merge(
				array( 'form-row-wide', 'update_totals_on_change', 'nova-checkout__vuelve-consent' ),
				(array) $consent['class']
			);
			$fields['order']['vuelve_marketing_consent'] = $consent;
		}
	}

	if ( isset( $fields['order'] ) && is_array( $fields['order'] ) ) {
		foreach ( $fields['order'] as $key => $order_field ) {
			if ( 'vuelve_marketing_consent' === $key ) {
				continue;
			}
			if ( 'order_comments' === $key ) {
				$fields['order'][ $key ]['priority'] = 10;
				$fields['order'][ $key ]['class']    = $wide;
			}
			nova_checkout_apply_label_above_input( $fields['order'][ $key ] );
		}
	}

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'nova_checkout_personal_fields_layout', 10000000000000001 );

/**
 * Remove "(אופציונלי)" from billing_address_2 label.
 *
 * Some plugins append the optional text as a separate span, so keeping it in the
 * raw label creates a duplicate.
 *
 * @param array<string, array<string, array<string, mixed>>> $fields Checkout fields.
 * @return array<string, array<string, array<string, mixed>>>
 */
function nova_checkout_strip_optional_from_billing_address_2_label( $fields ) {
	if ( ! is_array( $fields ) || ! isset( $fields['billing']['billing_address_2']['label'] ) ) {
		return $fields;
	}

	$label = (string) $fields['billing']['billing_address_2']['label'];
	$label = trim( str_replace( '(אופציונלי)', '', $label ) );

	$fields['billing']['billing_address_2']['label'] = $label;

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'nova_checkout_strip_optional_from_billing_address_2_label', 10000000000000002 );

/**
 * Drop display-only country field from POST; keep billing country as IL.
 *
 * @param array<string, mixed> $data Posted checkout data.
 * @return array<string, mixed>
 */
function nova_checkout_posted_data_country( $data ) {
	if ( ! is_array( $data ) ) {
		return $data;
	}

	unset( $data['billing_country_display'] );

	if ( empty( $data['billing_country'] ) ) {
		$data['billing_country'] = 'IL';
	}

	return $data;
}
add_filter( 'woocommerce_checkout_posted_data', 'nova_checkout_posted_data_country', 10 );

/**
 * Default billing country to Israel on checkout.
 *
 * @param mixed  $value Field value.
 * @param string $input Field key.
 * @return mixed
 */
function nova_checkout_default_billing_country( $value, $input ) {
	if ( 'billing_country' === $input && ( '' === $value || null === $value ) ) {
		return 'IL';
	}
	return $value;
}
add_filter( 'woocommerce_checkout_get_value', 'nova_checkout_default_billing_country', 10, 2 );

/**
 * Remove quantity from product-name column on checkout.
 *
 * @param string $quantity_html Default quantity HTML.
 * @param array  $cart_item     Cart item.
 * @param string $cart_item_key Cart item key.
 * @return string
 */
function nova_checkout_hide_quantity_in_product_name( $quantity_html, $cart_item, $cart_item_key ) {
	unset( $cart_item, $cart_item_key );

	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_order_received_page() ) {
		return $quantity_html;
	}

	return '';
}
add_filter( 'woocommerce_checkout_cart_item_quantity', 'nova_checkout_hide_quantity_in_product_name', 20, 3 );

/**
 * Print quantity next to subtotal in product-total column on checkout.
 *
 * @param string $subtotal_html Item subtotal HTML.
 * @param array  $cart_item     Cart item.
 * @param string $cart_item_key Cart item key.
 * @return string
 */
function nova_checkout_move_quantity_to_product_total( $subtotal_html, $cart_item, $cart_item_key ) {
	unset( $cart_item_key );

	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_order_received_page() ) {
		return $subtotal_html;
	}

	$qty = isset( $cart_item['quantity'] ) ? (int) $cart_item['quantity'] : 0;
	if ( $qty <= 0 ) {
		return $subtotal_html;
	}

	$qty_html = '<strong class="product-quantity">&times;&nbsp;' . esc_html( (string) $qty ) . '</strong>';
	return $qty_html . ' ' . $subtotal_html;
}
add_filter( 'woocommerce_cart_item_subtotal', 'nova_checkout_move_quantity_to_product_total', 20, 3 );

/**
 * Persist ?cf-redirect=1 for checkout AJAX (update_order_review has no query string).
 */
function nova_checkout_sync_cf_redirect_flag() {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_order_received_page() ) {
		return;
	}

	if ( ! function_exists( 'WC' ) || ! WC()->session ) {
		return;
	}

	if ( isset( $_GET['cf-redirect'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		WC()->session->set( 'nova_cf_redirect', '1' );
		return;
	}

	if ( ! wp_doing_ajax() ) {
		WC()->session->set( 'nova_cf_redirect', null );
	}
}
add_action( 'template_redirect', 'nova_checkout_sync_cf_redirect_flag', 5 );

/**
 * CartFlows flow redirect (?cf-redirect=1), including WC checkout fragments AJAX.
 */
function nova_checkout_has_cf_redirect() {
	if ( isset( $_GET['cf-redirect'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return true;
	}

	if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
		$referer = wp_unslash( $_SERVER['HTTP_REFERER'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( false !== strpos( $referer, 'cf-redirect' ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Checkout page context (WC checkout or CartFlows checkout step).
 */
function nova_checkout_is_checkout_context() {
	if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
		return false;
	}

	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		return true;
	}

	return function_exists( '_is_wcf_checkout_type' ) && _is_wcf_checkout_type();
}

/**
 * Checkout page with ?cf-redirect (CartFlows flow) — wp_enqueue often does not print CSS on this template.
 */
function nova_should_print_checkout_flow_style() {
	if ( ! nova_checkout_has_cf_redirect() ) {
		return false;
	}

	if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
		return false;
	}

	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		return true;
	}

	return function_exists( '_is_wcf_checkout_type' ) && _is_wcf_checkout_type();
}

/**
 * Print flow checkout CSS in footer (bypasses WP Styles API on CartFlows checkout-page).
 */
function nova_print_checkout_flow_style_in_footer() {
	if ( ! nova_should_print_checkout_flow_style() ) {
		return;
	}

	$flow_css = NOVA_CHILD_DIR . '/assets/css/nova-checkout-flow.css';
	if ( ! is_file( $flow_css ) ) {
		return;
	}

	$href = add_query_arg(
		'ver',
		(string) filemtime( $flow_css ),
		NOVA_CHILD_URI . '/assets/css/nova-checkout-flow.css'
	);

	printf(
		'<link rel="stylesheet" id="enjoy-nova-checkout-flow-css" href="%s" media="all" />' . "\n",
		esc_url( $href )
	);
}
add_action( 'wp_footer', 'nova_print_checkout_flow_style_in_footer', 5 );

/**
 * Whether CartFlows "remove product" is enabled for the current checkout step.
 */
function nova_checkout_is_remove_product_enabled() {
	if ( nova_checkout_has_cf_redirect() ) {
		return false;
	}

	$checkout_id = 0;

	if ( function_exists( '_get_wcf_checkout_id' ) ) {
		$checkout_id = (int) _get_wcf_checkout_id();
	}

	if ( ! $checkout_id ) {
		$checkout_id = (int) get_the_ID();
	}

	if ( $checkout_id && function_exists( 'wcf' ) && is_object( wcf()->options ) ) {
		$option = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-remove-product-field' );
		// CartFlows default is empty; only hide when explicitly turned off.
		return 'no' !== $option;
	}

	return true;
}

/**
 * CartFlows-compatible remove link for checkout order review (GTM may extend via filter).
 *
 * @param array  $cart_item     Cart item.
 * @param string $cart_item_key Cart item key.
 * @return string
 */
function nova_checkout_get_remove_product_link( $cart_item, $cart_item_key ) {
	if ( ! nova_checkout_is_remove_product_enabled() ) {
		return '';
	}

	$product_id = isset( $cart_item['product_id'] ) ? (int) $cart_item['product_id'] : 0;
	if ( $product_id <= 0 ) {
		return '';
	}

	$link = sprintf(
		'<a href="javascript:void(0)" role="button" rel="nofollow" class="wcf-remove-product cartflows-icon cartflows-circle-cross" data-id="%s" data-item-key="%s" aria-label="%s"><span class="screen-reader-text">%s</span></a>',
		esc_attr( (string) $product_id ),
		esc_attr( $cart_item_key ),
		esc_attr__( 'Remove product', 'astra-child' ),
		esc_html__( 'Remove product', 'astra-child' )
	);

	/**
	 * CartFlows + GTM may add data-* attributes; keep markup out of wp_kses_post().
	 */
	return apply_filters( 'woocommerce_cart_item_remove_link', $link, $cart_item_key );
}

/**
 * Strip CartFlows order-review wrapper and return inner product title HTML.
 *
 * @param string $product_name Product column HTML.
 * @return string
 */
function nova_checkout_unwrap_cartflows_product_name( $product_name ) {
	if ( false === strpos( $product_name, 'wcf-product-name' ) ) {
		return $product_name;
	}

	if ( preg_match( '/<div class="wcf-product-name">(.*)<\/div>\s*<\/div>\s*$/s', $product_name, $matches ) ) {
		return $matches[1];
	}

	return wp_strip_all_tags( $product_name );
}

/**
 * Product thumbnail in checkout order review table.
 *
 * @param string $product_name Product title HTML.
 * @param array  $cart_item    Cart item.
 * @param string $cart_item_key Cart item key.
 * @return string
 */
function nova_checkout_order_item_name_with_image( $product_name, $cart_item, $cart_item_key ) {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_order_received_page() ) {
		return $product_name;
	}

	if ( nova_checkout_has_cf_redirect() ) {
		return $product_name;
	}

	if ( false !== strpos( $product_name, 'nova-checkout__order-product' ) ) {
		return $product_name;
	}

	if ( false !== strpos( $product_name, 'wcf-product-image' ) ) {
		$product_name = nova_checkout_unwrap_cartflows_product_name( $product_name );
	}

	$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
	if ( ! $_product || ! $_product->exists() ) {
		return $product_name;
	}

	$thumbnail = $_product->get_image( array( 56, 56 ) );
	if ( ! $thumbnail ) {
		return $product_name;
	}

	$remove_link = nova_checkout_get_remove_product_link( $cart_item, $cart_item_key );

	return '<span class="nova-checkout__order-product">' .
		'<span class="nova-checkout__order-product-thumb">' . $thumbnail . $remove_link . '</span>' .
		'<span class="nova-checkout__order-product-name">' . wp_kses_post( $product_name ) . '</span>' .
		'</span>';
}
add_filter( 'woocommerce_cart_item_name', 'nova_checkout_order_item_name_with_image', 25, 3 );

/**
 * Checkout: use child theme form-checkout (payment inside #customer_details).
 */
add_filter(
	'woocommerce_locate_template',
	function ( $template, $template_name ) {
		$templates = array(
			'checkout/form-checkout.php' => NOVA_CHILD_DIR . '/woocommerce/checkout/form-checkout.php',
			'checkout/review-order.php'  => NOVA_CHILD_DIR . '/woocommerce/checkout/review-order.php',
		);
		if ( ! isset( $templates[ $template_name ] ) ) {
			return $template;
		}
		$child_template = $templates[ $template_name ];
		return is_file( $child_template ) ? $child_template : $template;
	},
	25,
	2
);

/**
 * Checkout: keep #payment out of #order_review (rendered in form-checkout.php).
 */
add_action(
	'wp',
	function () {
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_order_received_page() ) {
			return;
		}

		remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
		remove_action( 'cartflows_checkout_after_instant_shipping', 'woocommerce_checkout_payment', 21 );
		remove_action( 'cartflows_checkout_after_modern_checkout_layout', 'woocommerce_checkout_payment', 21 );
		remove_action( 'woocommerce_checkout_after_customer_details', 'woocommerce_checkout_additional_fields', 10 );
	},
	100
);

/**
 * Unhook CartFlows plain .wcf-customer-shipping (registered on shortcode init, after wp:100).
 */
function nova_checkout_remove_cartflows_default_shipping() {
	if ( ! class_exists( 'Cartflows_Checkout_Markup' ) ) {
		return;
	}

	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_order_received_page() ) {
		return;
	}

	remove_action(
		'woocommerce_checkout_after_customer_details',
		array( Cartflows_Checkout_Markup::get_instance(), 'add_custom_shipping_section' ),
		10
	);
}
add_action( 'cartflows_checkout_before_shortcode', 'nova_checkout_remove_cartflows_default_shipping', 20 );
add_action( 'cartflows_elementor_before_checkout_shortcode', 'nova_checkout_remove_cartflows_default_shipping', 20 );
add_action( 'cartflows_gutenberg_before_checkout_shortcode', 'nova_checkout_remove_cartflows_default_shipping', 20 );
add_action( 'cartflows_bb_before_checkout_shortcode', 'nova_checkout_remove_cartflows_default_shipping', 20 );
add_action( 'wp', 'nova_checkout_remove_cartflows_default_shipping', 999 );

/**
 * CartFlows checkout markup for shipping (child cartflows template + fragment selectors).
 */
function nova_checkout_uses_cartflows_shipping_markup() {
	if ( function_exists( '_is_wcf_checkout_type' ) && ( _is_wcf_checkout_type() || ( function_exists( '_is_wcf_doing_checkout_ajax' ) && _is_wcf_doing_checkout_ajax() ) ) ) {
		return true;
	}

	return nova_checkout_has_cf_redirect();
}

/**
 * Whether to show Nova numbered shipping section (standard WC or CartFlows).
 */
function nova_checkout_should_display_shipping_section() {
	if ( ! nova_checkout_is_checkout_context() ) {
		return false;
	}

	if ( ! function_exists( 'WC' ) || ! WC()->cart || ! WC()->cart->needs_shipping() || ! WC()->cart->show_shipping() ) {
		return false;
	}

	if ( nova_checkout_uses_cartflows_shipping_markup() ) {
		return (bool) apply_filters( 'cartflows_should_render_custom_shipping', true );
	}

	return true;
}

/**
 * @deprecated Use nova_checkout_should_display_shipping_section().
 */
function nova_checkout_should_show_shipping_section() {
	return nova_checkout_should_display_shipping_section();
}

/**
 * Section step numbers: payment is 2 when shipping is hidden, otherwise 3.
 *
 * @return array{personal:string,shipping:string,payment:string}
 */
function nova_checkout_get_section_numbers() {
	static $numbers = null;

	if ( null !== $numbers ) {
		return $numbers;
	}

	$has_shipping = nova_checkout_should_display_shipping_section();

	$numbers = array(
		'personal' => '1',
		'shipping' => $has_shipping ? '2' : '',
		'payment'  => $has_shipping ? '3' : '2',
	);

	return apply_filters( 'nova_checkout_section_numbers', $numbers );
}

/**
 * Hebrew shipping section title (CartFlows package label fallback).
 *
 * @param string $package_name Default package name.
 * @param int    $index        Package index.
 * @return string
 */
function nova_checkout_shipping_package_name_he( $package_name, $index ) {
	if ( ! nova_checkout_is_checkout_context() ) {
		return $package_name;
	}

	$title = (string) apply_filters( 'nova_checkout_shipping_section_title', 'משלוח' );

	if ( $index > 0 ) {
		return $title . ' ' . ( $index + 1 );
	}

	return $title;
}
add_filter( 'woocommerce_shipping_package_name', 'nova_checkout_shipping_package_name_he', 20, 2 );

/**
 * Render standard WooCommerce shipping methods (child checkout template).
 */
function nova_checkout_render_standard_shipping_html() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return;
	}

	$packages = WC()->shipping()->get_packages();
	if ( empty( $packages ) ) {
		WC()->cart->calculate_totals();
		$packages = WC()->shipping()->get_packages();
	}

	$first = true;
	foreach ( $packages as $i => $package ) {
		$chosen_method = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
		$product_names = array();

		if ( count( $packages ) > 1 ) {
			foreach ( $package['contents'] as $item_id => $values ) {
				$product_names[ $item_id ] = $values['data']->get_name() . ' &times;' . $values['quantity'];
			}
			$product_names = apply_filters( 'woocommerce_shipping_package_details_array', $product_names, $package );
		}

		wc_get_template(
			'checkout/nova-shipping-methods.php',
			array(
				'package'                  => $package,
				'available_methods'        => $package['rates'],
				'show_package_details'     => count( $packages ) > 1,
				'show_shipping_calculator' => is_cart() && apply_filters( 'woocommerce_shipping_show_shipping_calculator', $first, $i, $package ),
				'package_details'          => implode( ', ', $product_names ),
				'package_name'             => apply_filters( 'woocommerce_shipping_package_name', ( ( $i + 1 ) > 1 ) ? sprintf( _x( 'Shipping %d', 'shipping packages', 'woocommerce' ), ( $i + 1 ) ) : _x( 'Shipping', 'shipping packages', 'woocommerce' ), $i, $package ),
				'index'                    => $i,
				'chosen_method'            => $chosen_method,
				'formatted_destination'    => WC()->countries->get_formatted_address( $package['destination'], ', ' ),
				'has_calculated_shipping'  => WC()->customer->has_calculated_shipping(),
			)
		);

		do_action( 'woocommerce_review_order_after_shipping' );

		$first = false;
	}
}

/**
 * Render CartFlows shipping methods HTML (child theme template).
 */
function nova_checkout_render_shipping_methods_html() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return;
	}

	$template = NOVA_CHILD_DIR . '/cartflows/checkout/shipping-methods.php';
	if ( ! is_file( $template ) ) {
		return;
	}

	$packages = WC()->shipping()->get_packages();
	if ( empty( $packages ) ) {
		WC()->cart->calculate_totals();
		$packages = WC()->shipping()->get_packages();
	}

	$first = true;
	foreach ( $packages as $i => $package ) {
		$chosen_method = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
		$product_names = array();

		if ( count( $packages ) > 1 ) {
			foreach ( $package['contents'] as $item_id => $values ) {
				$product_names[ $item_id ] = $values['data']->get_name() . ' &times;' . $values['quantity'];
			}
			$product_names = apply_filters( 'woocommerce_shipping_package_details_array', $product_names, $package );
		}

		include $template;

		do_action( 'woocommerce_review_order_after_shipping' );

		$first = false;
	}
}

/**
 * CartFlows AJAX replaces .wcf-customer-shipping with plugin markup (h3 title) — keep Nova header intact.
 *
 * @param array $fragments Checkout fragments.
 * @return array
 */
function nova_checkout_filter_shipping_fragments( $fragments ) {
	if ( ! nova_checkout_should_display_shipping_section() ) {
		return $fragments;
	}

	if ( nova_checkout_uses_cartflows_shipping_markup() ) {
		unset( $fragments['.wcf-embed-checkout-form .wcf-customer-shipping'] );

		ob_start();
		nova_checkout_render_shipping_methods_html();
		$shipping_html = ob_get_clean();

		if ( $shipping_html ) {
			$fragments['.wcf-embed-checkout-form .wcf-shipping-methods-wrapper'] = $shipping_html;
		}

		return $fragments;
	}

	ob_start();
	nova_checkout_render_standard_shipping_html();
	$shipping_html = ob_get_clean();

	if ( $shipping_html ) {
		$fragments['.nova-checkout__section--shipping .nova-checkout__shipping-inner'] = $shipping_html;
	}

	return $fragments;
}
add_filter( 'woocommerce_update_order_review_fragments', 'nova_checkout_filter_shipping_fragments', 99 );

/**
 * Shipping section with step badge (between personal details and payment).
 */
function nova_checkout_render_shipping_section() {
	if ( ! nova_checkout_should_display_shipping_section() ) {
		return;
	}

	$section_nums = nova_checkout_get_section_numbers();
	$section_num  = (string) apply_filters( 'nova_checkout_shipping_section_num', $section_nums['shipping'] );
	$title        = (string) apply_filters( 'nova_checkout_shipping_section_title', 'משלוח' );
	$use_wcf      = nova_checkout_uses_cartflows_shipping_markup();
	?>
	<section class="nova-checkout__section nova-checkout__section--shipping" aria-labelledby="nova-checkout-shipping-heading">
		<header class="nova-checkout__section-head">
			<span class="nova-checkout__section-num" aria-hidden="true"><?php echo esc_html( $section_num ); ?></span>
			<h2 id="nova-checkout-shipping-heading" class="nova-checkout__section-title">
				<?php echo esc_html( $title ); ?>
			</h2>
		</header>
		<?php if ( $use_wcf ) : ?>
		<div class="nova-checkout__section-body wcf-customer-shipping">
			<?php nova_checkout_render_shipping_methods_html(); ?>
		</div>
		<?php else : ?>
		<div class="nova-checkout__section-body nova-checkout__woocommerce-shipping">
			<div class="nova-checkout__shipping-inner">
				<?php nova_checkout_render_standard_shipping_html(); ?>
			</div>
		</div>
		<?php endif; ?>
	</section>
	<?php
}
add_action( 'nova_checkout_before_payment_section', 'nova_checkout_render_shipping_section', 10 );

/**
 * Whether the current CartFlows checkout step uses the instant layout.
 */
function nova_checkout_is_instant_layout() {
	if ( ! function_exists( '_is_wcf_checkout_type' ) || ! _is_wcf_checkout_type() ) {
		return false;
	}

	if ( ! class_exists( 'Cartflows_Helper' ) || ! method_exists( 'Cartflows_Helper', 'is_instant_layout_enabled' ) ) {
		return false;
	}

	$flow_id = 0;
	if ( function_exists( 'wcf' ) && is_object( wcf()->utils ) && wcf()->utils->is_step_post_type() ) {
		$flow_id = (int) wcf()->utils->get_flow_id();
	}

	return Cartflows_Helper::is_instant_layout_enabled( $flow_id );
}

/**
 * Checkout: CartFlows custom coupon at the end of #order_review; hide default WC coupon UI.
 */
function nova_checkout_coupon_field_setup() {
	if ( nova_checkout_has_cf_redirect() ) {
		return;
	}

	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_order_received_page() ) {
		return;
	}

	remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );

	if ( ! class_exists( 'Cartflows_Checkout_Markup' ) || nova_checkout_is_instant_layout() ) {
		return;
	}

	$markup = Cartflows_Checkout_Markup::get_instance();

	remove_action( 'woocommerce_checkout_order_review', array( $markup, 'display_custom_coupon_field' ), 10 );
	add_action( 'woocommerce_checkout_order_review', array( $markup, 'display_custom_coupon_field' ), 99 );
}
add_action( 'wp', 'nova_checkout_coupon_field_setup', 110 );

add_action(
	'wp_head',
	function () {
		if ( ! is_product() ) {
			return;
		}
		/**
		 * CSS selector for the duplicated Elementor top section, e.g. `.elementor-element-444a329`.
		 * Leave empty once the section is removed in Elementor.
		 *
		 * @param string $selector Full selector (one rule).
		 */
		$selector = trim( (string) apply_filters( 'nova_hide_elementor_hero_section_selector', '' ) );
		if ( ! $selector || ! preg_match( '/^[\w\s.#:\-,\[\]="\']+$/', $selector ) ) {
			return;
		}
		printf(
			'<style id="nova-hide-elementor-duplicate">%s{display:none!important;}</style>',
			esc_html( $selector )
		);
	},
	99
);
