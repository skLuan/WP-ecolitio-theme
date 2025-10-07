<?php
/**
 * The header for Ecolitio-theme
 *
 * This template overrides Storefront’s header.php, but calls all parent hooks
 * to preserve markup, then injects the footer image into the header.
 *
 * @package Ecolitio-theme
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
/**
 * Fires immediately after the opening <body> tag.
 *
 * @hooked storefront_skip_links – 0
 */
do_action( 'storefront_before_site' );
?>

<div id="page" class="hfeed site">
	<header id="masthead" class="site-header" role="banner" style="<?php storefront_header_styles(); ?>">
		<?php do_action( 'storefront_skip_links' ); ?>

		<div class="header-top">
			<div class="col-full">
				<div class="header-top-right">
					<!-- Language selector -->
					<?php if ( function_exists( 'pll_the_languages' ) ) { pll_the_languages( array( 'show_flags' => 1, 'show_names' => 0 ) ); } else { ?>
						<a href="#" class="language-selector"><?php esc_html_e( 'EN', 'ecolitio-theme' ); ?></a>
					<?php } ?>

					<!-- Shopping cart -->
					<?php storefront_header_cart(); ?>

					<!-- User account -->
					<?php if ( is_user_logged_in() ) { ?>
						<a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ); ?>" class="account-link"><?php esc_html_e( 'My Account', 'ecolitio-theme' ); ?></a>
					<?php } else { ?>
						<a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ); ?>" class="account-link"><?php esc_html_e( 'Login', 'ecolitio-theme' ); ?></a>
					<?php } ?>

					<!-- Zona Sabway -->
					<a href="#" class="zona-sabway"><?php esc_html_e( 'Zona Sabway', 'ecolitio-theme' ); ?></a>
				</div>
			</div>
		</div>

		<div class="header-bottom">
			<div class="col-full">
				<div class="header-layout">
					<div class="site-branding">
						<?php storefront_site_branding(); ?>
					</div>
					<div class="site-navigation">
						<?php storefront_primary_navigation_wrapper(); ?>
						<?php storefront_primary_navigation(); ?>
						<?php storefront_primary_navigation_wrapper_close(); ?>
					</div>
					<div class="site-search">
						<?php storefront_product_search(); ?>
					</div>
				</div>
			</div>
		</div>

		<?php storefront_secondary_navigation(); ?>
	</header>

	<?php
	/*
	 * Inject Ecolitio footer image into header.
	 * Priority: use child theme option first, then fall back to parent’s footer logo.
	 */
	$ecolitio_footer_image = get_theme_mod( 'ecolitio_footer_image' );
	if ( ! $ecolitio_footer_image ) {
		$ecolitio_footer_image = get_theme_mod( 'storefront_footer_logo' );
	}

	if ( $ecolitio_footer_image ) : ?>
		<div class="ecolitio-header-footer-image">
			<img src="<?php echo esc_url( $ecolitio_footer_image ); ?>"
			     alt="<?php esc_attr_e( 'Footer Logo', 'ecolitio-theme' ); ?>">
		</div>
	<?php endif; ?>

	<?php
	/**
	 * Fires after the header but before the main content.
	 *
	 * @hooked storefront_content_top – 10
	 */
	do_action( 'storefront_after_header' );
	?>

	<div id="content" class="site-content" tabindex="-1">
		<div class="col-full">
