<?php

/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined('ABSPATH') || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action('woocommerce_before_single_product');

if (post_password_required()) {
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
}

remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);


add_action('ecolitio_single_product_summary', 'woocommerce_template_single_title', 15);
add_action('ecolitio_single_product_summary', 'woocommerce_template_single_rating', 20);
add_action('ecolitio_single_product_prices', 'woocommerce_template_single_price', 20);
add_action('ecolitio_single_product_prices', 'woocommerce_template_single_add_to_cart', 20);
//----------------------------------------------------------------------------------------------------
$icons = [
	"step1" => [
		"icon" => "ix:electrical-energy-filled",
		"title" => "Parte eléctrica",
	],
	"step2" => [
		"icon" => "tabler:dimensions",
		"title" => "Dimensiones",
	],
	"step3" => [
		"icon" => "material-symbols:cable",
		"title" => "Conectores",
	],
	"step4" => [
		"icon" => "material-symbols:check-circle",
		"title" => "Confirmación",
	],
];

$getAttributes = $product->get_attributes();
$values = ['s', 'o', 'w'];

$distance = 30;
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class('!mt-[133px] md:max-w-10/12 mx-auto grid grid-cols-2 gap-3', $product); ?>>

	<div class="summary entry-summary ecolitio-item">
		<?php get_template_part('templates/info-header'); ?>

		<article class="my-6">
			<?php
			// Render Sabway Battery Form using shortcode
			echo do_shortcode('[sabway_battery_form]');
			?>
		</article>
		<?php
		/**
		 * Hook: woocommerce_single_product_summary.
		 *
		 * @hooked woocommerce_template_single_excerpt - 20
		 * @hooked woocommerce_template_single_meta - 40
		 * @hooked woocommerce_template_single_sharing - 50
		 * @hooked WC_Structured_Data::generate_product_data() - 60
		 */
		do_action('woocommerce_single_product_summary');
		?>
	</div>
	<div class="column2">

		<?php
		/**
		 * Hook: woocommerce_before_single_product_summary.
		 *
		 * @hooked woocommerce_show_product_sale_flash - 10
		 * @hooked woocommerce_show_product_images - 20
		 */
		do_action('woocommerce_before_single_product_summary');
		?>
	</div>
</div>
<?php
/**
 * Hook: woocommerce_after_single_product_summary.
 *
 * @hooked woocommerce_output_product_data_tabs - 10
 * @hooked woocommerce_upsell_display - 15
 * @hooked woocommerce_output_related_products - 20
 */
//do_action('woocommerce_after_single_product_summary');
?>

<?php do_action('woocommerce_after_single_product'); ?>