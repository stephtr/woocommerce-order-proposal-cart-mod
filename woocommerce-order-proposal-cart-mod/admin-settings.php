<?php

function wc_modifications_settings() {
    $payment_gateways = array();
    foreach ( WC()->payment_gateways->payment_gateways() as $gateway ) {
        if ( $gateway->enabled == 'yes' ) {
            $payment_gateways[ $gateway->id ] = $gateway->get_title();
        }
    }

    $settings = array(
        array(
            'title' => __( 'Order Proposal | Cart Mod Settings', 'woocommerce-cart-modifications' ),
            'type'  => 'title',
            'desc'  => '',
            'id'    => 'wc_modifications_settings_title'
        ),
        array(
            'title'    => __( 'Order Proposal Shipping Method Title', 'woocommerce-cart-modifications' ),
            'desc'     => __( 'Enter the title for the order proposal shipping method.', 'woocommerce-cart-modifications' ),
            'id'       => 'wc_modifications_custom_shipping_method_title',
            'type'     => 'text',
            'default'  => '',
            'desc_tip' => true,
        ),
        array(
            'title'    => __( 'Associated Payment Method', 'woocommerce-cart-modifications' ),
            'desc'     => __( 'Select the payment method associated with the order proposal shipping method.', 'woocommerce-cart-modifications' ),
            'id'       => 'wc_modifications_payment_method',
            'type'     => 'select',
            'default'  => '',
            'desc_tip' => true,
            'options'  => $payment_gateways,
        ),
        array(
            'title'    => __( 'Show Order Proposal Payment Method when other methods are avilable', 'woocommerce-cart-modifications' ),
            'desc'     => __( 'Disable to hide the order proposal payment method when other payment methods are available for a given order.', 'woocommerce-cart-modifications' ),
            'id'       => 'wc_modifications_show_order_proposal_payment_method_with_others_available',
            'type'     => 'checkbox',
            'default'  => 'yes',
            'desc_tip' => true,
        ),
        array(
            'type' => 'sectionend',
            'id'   => 'wc_modifications_settings_end'
        )
    );

    return apply_filters( 'wc_modifications_settings', $settings );
}

/**
 * Add a settings tab into WooCommerce
 */

function wc_modifications_add_settings_tab( $settings_tabs ) {
    $settings_tabs['wc_modifications'] = __( 'Order Proposal | Cart Mod', 'woocommerce-cart-modifications' );
    return $settings_tabs;
}
add_filter( 'woocommerce_settings_tabs_array', 'wc_modifications_add_settings_tab', 200 );

// Display settings
function wc_modifications_settings_tab() {
    woocommerce_admin_fields( wc_modifications_settings() );
}
add_action( 'woocommerce_settings_tabs_wc_modifications', 'wc_modifications_settings_tab' );

// Save settings
function wc_modifications_save_settings() {
    woocommerce_update_options( wc_modifications_settings() );
}
add_action( 'woocommerce_update_options_wc_modifications', 'wc_modifications_save_settings' );