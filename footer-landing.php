<?php
/**
 * Landing footer wrapper — closes content; Elementor Theme Builder renders via astra_footer().
 *
 * @package Astra Child
 */

defined( 'ABSPATH' ) || exit;

?>
<?php astra_content_bottom(); ?>
	</div><!-- .ast-container -->
	</div><!-- #content -->
<?php
	astra_content_after();

	astra_footer_before();

	astra_footer();

	astra_footer_after();
?>
	</div><!-- #page -->
<?php
	astra_body_bottom();
	wp_footer();
?>
	</body>
</html>
