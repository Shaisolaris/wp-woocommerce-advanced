<?php
defined('ABSPATH') || exit;

class WC_Custom_Shipping extends WC_Shipping_Method {
    public function __construct($instance_id = 0) {
        $this->id = 'custom_shipping';
        $this->instance_id = absint($instance_id);
        $this->method_title = __('Custom Shipping', 'wc-advanced');
        $this->method_description = __('Weight-based shipping with zone support', 'wc-advanced');
        $this->supports = ['shipping-zones', 'instance-settings', 'instance-settings-modal'];

        $this->init();
    }

    public function init(): void {
        $this->instance_form_fields = [
            'title' => ['title' => __('Method Title', 'wc-advanced'), 'type' => 'text', 'default' => __('Custom Shipping', 'wc-advanced')],
            'base_cost' => ['title' => __('Base Cost', 'wc-advanced'), 'type' => 'price', 'default' => '5.99'],
            'per_kg_cost' => ['title' => __('Cost per kg', 'wc-advanced'), 'type' => 'price', 'default' => '1.50'],
            'free_shipping_threshold' => ['title' => __('Free shipping above', 'wc-advanced'), 'type' => 'price', 'default' => '75.00', 'description' => __('Set to 0 to disable', 'wc-advanced')],
            'max_weight' => ['title' => __('Max weight (kg)', 'wc-advanced'), 'type' => 'number', 'default' => '30'],
            'handling_fee' => ['title' => __('Handling fee', 'wc-advanced'), 'type' => 'price', 'default' => '0'],
        ];

        $this->title = $this->get_option('title');
        add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
    }

    public function calculate_shipping($package = []): void {
        $base = (float) $this->get_option('base_cost', 5.99);
        $per_kg = (float) $this->get_option('per_kg_cost', 1.50);
        $free_threshold = (float) $this->get_option('free_shipping_threshold', 75);
        $max_weight = (float) $this->get_option('max_weight', 30);
        $handling = (float) $this->get_option('handling_fee', 0);

        $total_weight = 0;
        $cart_total = 0;
        foreach ($package['contents'] as $item) {
            if ($item['data']->get_weight()) $total_weight += (float) $item['data']->get_weight() * $item['quantity'];
            $cart_total += $item['line_total'];
        }

        if ($total_weight > $max_weight) return; // Exceeds max weight

        if ($free_threshold > 0 && $cart_total >= $free_threshold) {
            $this->add_rate(['id' => $this->get_rate_id(), 'label' => $this->title . ' (Free)', 'cost' => 0, 'package' => $package]);
            return;
        }

        $cost = $base + ($total_weight * $per_kg) + $handling;
        $this->add_rate(['id' => $this->get_rate_id(), 'label' => $this->title, 'cost' => $cost, 'package' => $package]);
    }
}
