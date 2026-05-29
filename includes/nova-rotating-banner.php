<?php

/**
 * Above Header — rotating messages banner.
 *
 * Shortcode: [nova_rotating_banner]
 * Data source: ACF Options (see field names in nova_rotating_banner_get_settings()).
 *
 * @package Astra Child
 */

defined('ABSPATH') || exit;

/** ACF options storage key for the announcement bar sub page. */
define('NOVA_ROTATING_BANNER_OPTION_ID', 'options-top-announcement-bar');

/** Parent Theme Options menu slug (must match acf_add_options_page / ACF UI). */
define('NOVA_THEME_OPTIONS_MENU_SLUG', 'theme-options');

/**
 * Cache-busting version for theme assets.
 *
 * @param string $path Absolute file path.
 * @return string
 */
function nova_rotating_banner_asset_version($path)
{
	return is_string($path) && is_file($path) ? (string) filemtime($path) : gmdate('YmdHis');
}

/**
 * Parse repeater rows (text + image) from ACF.
 *
 * @param mixed $rows Repeater value from get_field().
 * @return array<int, array{text: string, icon_url: string, icon_alt: string}>
 */
function nova_rotating_banner_parse_content_rows( $rows ) {
	$messages = array();

	if ( ! is_array( $rows ) ) {
		return $messages;
	}

	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$text = isset( $row['text'] ) ? trim( (string) $row['text'] ) : '';
		if ( '' === $text ) {
			continue;
		}

		$icon_url = '';
		$icon_alt = '';

		if ( ! empty( $row['image'] ) && is_array( $row['image'] ) ) {
			$icon_url = isset( $row['image']['url'] ) ? (string) $row['image']['url'] : '';
			$icon_alt = isset( $row['image']['alt'] ) ? (string) $row['image']['alt'] : '';
		}

		$messages[] = array(
			'text'     => $text,
			'icon_url' => $icon_url,
			'icon_alt' => $icon_alt,
		);
	}

	return $messages;
}

/**
 * Read banner settings from ACF Options.
 *
 * @return array{
 *     enabled: bool,
 *     interval_ms: int,
 *     text_color: string,
 *     messages: array<int, array{text: string, icon_url: string, icon_alt: string}>
 * }
 */
function nova_rotating_banner_get_settings() {
	if ( ! function_exists( 'get_field' ) ) {
		return array(
			'enabled'     => false,
			'interval_ms' => 6000,
			'text_color'  => '',
			'messages'    => array(),
		);
	}

	$option_id = NOVA_ROTATING_BANNER_OPTION_ID;

	$enabled = (bool) get_field( 'nova_rotating_banner_enabled', $option_id );

	$interval_sec = (int) get_field( 'nova_rotating_banner_interval', $option_id );
	if ( $interval_sec < 5 ) {
		$interval_sec = 5;
	} elseif ( $interval_sec > 7 ) {
		$interval_sec = 7;
	}

	$rows     = get_field( 'nova_rotating_banner_content', $option_id );
	$messages = nova_rotating_banner_parse_content_rows( $rows );

	return array(
		'enabled'     => $enabled && ! empty( $messages ),
		'interval_ms' => $interval_sec * 1000,
		'text_color'  => (string) get_field( 'nova_rotating_banner_text_color', $option_id ),
		'messages'    => $messages,
	);
}

/**
 * Whether front-end assets should load.
 *
 * @return bool
 */
function nova_rotating_banner_should_enqueue_assets()
{
	$settings = nova_rotating_banner_get_settings();

	if (! empty($settings['enabled'])) {
		return true;
	}

	return (bool) is_customize_preview();
}

/**
 * Enqueue banner CSS/JS.
 */
function nova_rotating_banner_enqueue_assets()
{
	if (! nova_rotating_banner_should_enqueue_assets()) {
		return;
	}

	$css_path = NOVA_CHILD_DIR . '/assets/css/nova-rotating-banner.css';
	$js_path  = NOVA_CHILD_DIR . '/assets/js/nova-rotating-banner.js';

	wp_enqueue_style(
		'nova-rotating-banner',
		NOVA_CHILD_URI . '/assets/css/nova-rotating-banner.css',
		array('astra-child-style'),
		nova_rotating_banner_asset_version($css_path)
	);

	wp_enqueue_script(
		'nova-rotating-banner',
		NOVA_CHILD_URI . '/assets/js/nova-rotating-banner.js',
		array(),
		nova_rotating_banner_asset_version($js_path),
		true
	);
}
add_action('wp_enqueue_scripts', 'nova_rotating_banner_enqueue_assets', 25);

/**
 * Render rotating banner markup.
 *
 * @param array<string, mixed> $atts Shortcode attributes (reserved).
 * @return string
 */
function nova_rotating_banner_render($atts = array())
{
	$settings = nova_rotating_banner_get_settings();

	if (empty($settings['enabled']) || empty($settings['messages'])) {
		return '';
	}

	$messages = $settings['messages'];
	$count    = count($messages);

	$style_parts = array();
	if ('' !== $settings['text_color']) {
		$style_parts[] = '--nova-banner-color:' . sanitize_hex_color($settings['text_color']);
	}

	$style_attr = $style_parts ? ' style="' . esc_attr(implode(';', $style_parts)) . '"' : '';

	$interval = (int) $settings['interval_ms'];
	if ($interval < 5000) {
		$interval = 5000;
	} elseif ($interval > 7000) {
		$interval = 7000;
	}

	ob_start();
?>
	<div
		class="nova-rotating-banner<?php echo $count > 1 ? ' nova-rotating-banner--has-rotation' : ''; ?>"
		<?php echo $style_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized above. 
		?>
		<?php if ($count > 1) : ?>
		data-interval="<?php echo esc_attr((string) $interval); ?>"
		<?php endif; ?>>
		<div class="nova-rotating-banner__track">
			<?php foreach ($messages as $index => $message) : ?>
				<div class="nova-rotating-banner__item<?php echo 0 === $index ? ' is-active' : ''; ?>" data-index="<?php echo esc_attr((string) $index); ?>">
					<?php if (! empty($message['icon_url'])) : ?>
						<img
							class="nova-rotating-banner__icon"
							src="<?php echo esc_url($message['icon_url']); ?>"
							alt="<?php echo esc_attr($message['icon_alt']); ?>"
							width="18"
							height="18"
							loading="eager"
							decoding="async" />
					<?php endif; ?>
					<span class="nova-rotating-banner__text"><?php echo esc_html($message['text']); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
<?php
	return (string) ob_get_clean();
}

/**
 * Shortcode callback: [nova_rotating_banner]
 *
 * @param array<string, mixed>|string $atts Shortcode attributes.
 * @return string
 */
function nova_rotating_banner_shortcode($atts)
{
	$atts = is_array($atts) ? $atts : array();
	return nova_rotating_banner_render($atts);
}
add_shortcode('nova_rotating_banner', 'nova_rotating_banner_shortcode');

/**
 * Resolve parent Theme Options slug (PHP registration or ACF UI).
 *
 * @return string
 */
function nova_rotating_banner_get_theme_options_parent_slug()
{
	if (! function_exists('acf_get_options_page')) {
		return NOVA_THEME_OPTIONS_MENU_SLUG;
	}

	$parent = acf_get_options_page(NOVA_THEME_OPTIONS_MENU_SLUG);
	if (is_array($parent) && ! empty($parent['menu_slug'])) {
		return (string) $parent['menu_slug'];
	}

	$pages = acf_get_options_pages();
	if (! is_array($pages)) {
		return NOVA_THEME_OPTIONS_MENU_SLUG;
	}

	foreach ($pages as $page) {
		if (! is_array($page) || ! empty($page['parent_slug'])) {
			continue;
		}

		$menu_title = isset($page['menu_title']) ? (string) $page['menu_title'] : '';
		if ('Theme Options' === $menu_title && ! empty($page['menu_slug'])) {
			return (string) $page['menu_slug'];
		}
	}

	return NOVA_THEME_OPTIONS_MENU_SLUG;
}

/**
 * Register ACF options pages for the rotating banner.
 *
 * Must run on acf/init — not init/admin_menu.
 */
function nova_rotating_banner_register_acf_options_pages()
{
	if (! function_exists('acf_add_options_page') || ! function_exists('acf_add_options_sub_page')) {
		return;
	}

	$parent_slug = nova_rotating_banner_get_theme_options_parent_slug();

	if (! acf_get_options_page($parent_slug)) {
		acf_add_options_page(
			array(
				'page_title' => 'Theme Options',
				'menu_title' => 'Theme Options',
				'menu_slug'  => NOVA_THEME_OPTIONS_MENU_SLUG,
				'capability' => 'edit_posts',
				'redirect'   => false,
			)
		);
		$parent_slug = NOVA_THEME_OPTIONS_MENU_SLUG;
	}

	if (acf_get_options_page('top-announcement-bar')) {
		return;
	}

	acf_add_options_sub_page(
		array(
			'page_title'  => 'Top Announcement Bar',
			'menu_title'  => 'Top Announcement Bar',
			'menu_slug'   => 'top-announcement-bar',
			'parent_slug' => $parent_slug,
			'post_id'     => NOVA_ROTATING_BANNER_OPTION_ID,
		)
	);
}
add_action('acf/init', 'nova_rotating_banner_register_acf_options_pages');