<?php
/**
 * Template Name: Template 7 reasons
 *
 * Minimal header (no navigation) + Elementor Theme Builder footer.
 *
 * @package Astra Child
 */

defined( 'ABSPATH' ) || exit;

get_header( 'landing' );

	get_template_part('template-parts/7-reason/banner_with_fixed_button', null);
	get_template_part('template-parts/7-reason/two_columns', null);
	get_template_part('template-parts/7-reason/text_with_image', null);
	get_template_part('template-parts/7-reason/repeater', null);
	get_template_part('template-parts/7-reason/button_with_image', null);

get_footer();
