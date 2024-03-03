<?php
/**
 * Plugin Name: WooCommerce Order Proposal | Cart Mod
 * Description: Modifies WooCommerce's cart behaviour
 * Version:     1.1.0
 * Author:      Stephan Troyer
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Include admin settings file
require_once plugin_dir_path( __FILE__ ) . 'admin-settings.php';

/**
 * Register settings, sections, and fields.
 */
function wc_modifications_register_settings() {
    add_option( 'wc_modifications_payment_method', '' );
    register_setting( 'wc_modifications_options_group', 'wc_modifications_payment_method' );
    add_option( 'wc_modifications_custom_shipping_method_title', '' );
    register_setting( 'wc_modifications_options_group', 'wc_modifications_custom_shipping_method_title' );
    add_option( 'wc_modifications_show_order_proposal_payment_method_with_others_available', 'yes' );
    register_setting( 'wc_modifications_options_group', 'wc_modifications_show_order_proposal_payment_method_with_others_available' );
}
add_action( 'admin_init', 'wc_modifications_register_settings' );


$wc_modifications_custom_shipping_method_id = 'order_proposal';
function wc_modifications_hide_specific_shipping_method($rates, $package) {
    global $wc_modifications_custom_shipping_method_id;
    // Retrieve the admin-specified shipping method to potentially hide
    $custom_method_title = get_option('wc_modifications_custom_shipping_method_title');

    // Check if there are other shipping methods available
    if (empty($rates) && !empty($custom_method_title)) {
        $rates[$wc_modifications_custom_shipping_method_id] = new WC_Shipping_Rate(
            $wc_modifications_custom_shipping_method_id,
            $custom_method_title,
            0.0, // Cost
            array(), // Taxes (empty array for none)
            $wc_modifications_custom_shipping_method_id // Method ID
        );
    }

    return $rates;
}
add_filter('woocommerce_package_rates', 'wc_modifications_hide_specific_shipping_method', 100, 2);


function wc_modifications_filter_payment_methods_based_on_shipping($available_gateways) {
    global $wc_modifications_custom_shipping_method_id;
    if (is_admin() || !is_checkout()) {
        // Do nothing in the admin or if not on the checkout page
        return $available_gateways;
    }

    // Retrieve the admin-specified payment method to show when the specified shipping method is in use
    $specified_payment_method = get_option('wc_modifications_payment_method');
    if (empty($specified_payment_method)) {
        return $available_gateways;
    }

    $show_order_proposal_with_others_available = get_option('wc_modifications_show_order_proposal_payment_method_with_others_available') === 'yes';

    // Check the session for chosen shipping methods
    $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');
    $chosen_shipping_method = !empty($chosen_shipping_methods) ? $chosen_shipping_methods[0] : '';

    if (strpos($chosen_shipping_method, $wc_modifications_custom_shipping_method_id) !== false) {
        // If the specified shipping method is in use, filter out other payment methods
        if (isset($available_gateways[$specified_payment_method])) {
            $available_gateways = array($specified_payment_method => $available_gateways[$specified_payment_method]);
        } else {
            $available_gateways = array();
        }
    } else if (isset($available_gateways[$specified_payment_method]) && !$show_order_proposal_with_others_available) {
        unset($available_gateways[$specified_payment_method]);
    }
    return $available_gateways;
}
add_filter('woocommerce_available_payment_gateways', 'wc_modifications_filter_payment_methods_based_on_shipping', 10, 1);
