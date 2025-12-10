<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor form Reparacion action.
 *
 * Custom Elementor form action which adds repair service to cart after form submission.
 *
 * @since 1.0.0
 */
class ReparacionesAddtoCart extends \ElementorPro\Modules\Forms\Classes\Action_Base {

	/**
	 * Get action name.
	 *
	 * Retrieve Reparacion action name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string
	 */
	public function get_name(): string {
		return 'reparacion_form';
	}

	/**
	 * Get action label.
	 *
	 * Retrieve Reparacion action label.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string
	 */
	public function get_label(): string {
		return esc_html__( 'Reparación - Agregar al Carrito', 'ecolitio-theme' );
	}

	/**
	 * Register action controls.
	 *
	 * Add input fields to allow the user to customize the action settings.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param \Elementor\Widget_Base $widget
	 */
	public function register_settings_section( $widget ): void {

		$widget->start_controls_section(
			'section_reparacion',
			[
				'label' => esc_html__( 'Reparación', 'ecolitio-theme' ),
				'condition' => [
					'submit_actions' => $this->get_name(),
				],
			]
		);

		$widget->add_control(
			'reparacion_product_id',
			[
				'label' => esc_html__( 'Product ID', 'ecolitio-theme' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 2845,
				'description' => esc_html__( 'The WooCommerce product ID to add to cart when form is submitted.', 'ecolitio-theme' ),
			]
		);

		$widget->add_control(
			'reparacion_notes_field_id',
			[
				'label' => esc_html__( 'Repair Notes Field ID', 'ecolitio-theme' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'form_field_0',
				'description' => esc_html__( 'The form field ID that contains the repair notes (e.g., form_field_0).', 'ecolitio-theme' ),
			]
		);

		$widget->end_controls_section();

	}

	/**
	 * Run action.
	 *
	 * Runs the Reparacion action after form submission.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param \ElementorPro\Modules\Forms\Classes\Form_Record  $record
	 * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler
	 */
	public function run( $record, $ajax_handler ): void {

		$settings = $record->get( 'form_settings' );

		// Make sure that there is a product ID configured.
		if ( empty( $settings['reparacion_product_id'] ) ) {
			return;
		}

		// Make sure that there is a repair notes field ID configured.
		if ( empty( $settings['reparacion_notes_field_id'] ) ) {
			return;
		}

		// Get submitted form data.
		$raw_fields = $record->get( 'fields' );

		// Get the repair notes from the configured field ID.
		$notes_field_id = $settings['reparacion_notes_field_id'];
		$repair_notes = isset( $raw_fields[ $notes_field_id ]['value'] ) ? sanitize_textarea_field( $raw_fields[ $notes_field_id ]['value'] ) : '';

		// Get product ID and quantity from settings.
		$product_id = intval( $settings['reparacion_product_id'] );
		$quantity = 1;

		// Make sure WooCommerce is active and cart exists.
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return;
		}

		// Prepare cart item data with repair notes.
		$cart_item_data = array();

		if ( ! empty( $repair_notes ) ) {
			$cart_item_data['reparacion_nota'] = $repair_notes;
		}

		// Add product to cart with custom metadata.
		WC()->cart->add_to_cart( $product_id, $quantity, 0, array(), $cart_item_data );

		// Redirect to cart.
		$redirect_url = 'https://ecolitio.com/carrito/';
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * On export.
	 *
	 * Clears Reparacion form settings/fields when exporting.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $element
	 */
	public function on_export( $element ): array {

		unset(
			$element['reparacion_product_id'],
			$element['reparacion_notes_field_id']
		);

		return $element;

	}

}
