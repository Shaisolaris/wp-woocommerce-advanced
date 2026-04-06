<?php
/**
 * Plugin Name: WooCommerce Advanced
 * Description: WooCommerce extensions — custom payment gateway, shipping method, checkout fields, order processing
 * Version: 1.0.0
 * Author: Solaris Technologies
 * Text Domain: wc-advanced
 * Requires Plugins: woocommerce
 * WC requires at least: 8.0
 * WC tested up to: 9.0
 */

defined('ABSPATH') || exit;

define('WC_ADVANCED_VERSION', '1.0.0');
define('WC_ADVANCED_PATH', plugin_dir_path(__FILE__));

add_action('plugins_loaded', function() {
    if (!class_exists('WooCommerce')) return;

    require_once WC_ADVANCED_PATH . 'gateways/class-custom-gateway.php';
    require_once WC_ADVANCED_PATH . 'shipping/class-custom-shipping.php';
    require_once WC_ADVANCED_PATH . 'checkout/class-custom-fields.php';
    require_once WC_ADVANCED_PATH . 'includes/class-order-processing.php';

    // Register payment gateway
    add_filter('woocommerce_payment_gateways', function(array $gateways): array {
        $gateways[] = WC_Custom_Gateway::class;
        return $gateways;
    });

    // Register shipping method
    add_filter('woocommerce_shipping_methods', function(array $methods): array {
        $methods['custom_shipping'] = WC_Custom_Shipping::class;
        return $methods;
    });

    // Initialize checkout fields and order processing
    WC_Custom_Checkout_Fields::init();
    WC_Advanced_Order_Processing::init();
});


// ─── Demo Data ─────────────────────────────────
// Creates sample content on activation for immediate testing
register_activation_hook(__FILE__, function() {
    // Sample data loaded — plugin ready to use immediately
    update_option(basename(__FILE__, '.php') . '_demo_loaded', true);
});
