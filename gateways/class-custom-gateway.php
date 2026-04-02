<?php
defined('ABSPATH') || exit;

class WC_Custom_Gateway extends WC_Payment_Gateway {
    public function __construct() {
        $this->id = 'custom_gateway';
        $this->icon = '';
        $this->has_fields = true;
        $this->method_title = __('Custom Payment', 'wc-advanced');
        $this->method_description = __('Accept payments via custom gateway with tokenization', 'wc-advanced');
        $this->supports = ['products', 'refunds', 'tokenization'];

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->testmode = 'yes' === $this->get_option('testmode');
        $this->api_key = $this->testmode ? $this->get_option('test_api_key') : $this->get_option('live_api_key');
        $this->api_secret = $this->testmode ? $this->get_option('test_api_secret') : $this->get_option('live_api_secret');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
    }

    public function init_form_fields(): void {
        $this->form_fields = [
            'enabled' => ['title' => __('Enable/Disable', 'wc-advanced'), 'type' => 'checkbox', 'label' => __('Enable Custom Payment', 'wc-advanced'), 'default' => 'no'],
            'title' => ['title' => __('Title', 'wc-advanced'), 'type' => 'text', 'default' => __('Custom Payment', 'wc-advanced')],
            'description' => ['title' => __('Description', 'wc-advanced'), 'type' => 'textarea', 'default' => __('Pay securely via our custom gateway.', 'wc-advanced')],
            'testmode' => ['title' => __('Test Mode', 'wc-advanced'), 'type' => 'checkbox', 'label' => __('Enable test mode', 'wc-advanced'), 'default' => 'yes'],
            'test_api_key' => ['title' => __('Test API Key', 'wc-advanced'), 'type' => 'text'],
            'test_api_secret' => ['title' => __('Test API Secret', 'wc-advanced'), 'type' => 'password'],
            'live_api_key' => ['title' => __('Live API Key', 'wc-advanced'), 'type' => 'text'],
            'live_api_secret' => ['title' => __('Live API Secret', 'wc-advanced'), 'type' => 'password'],
        ];
    }

    public function payment_fields(): void {
        if ($this->description) echo wpautop(wptexturize($this->description));
        echo '<fieldset id="wc-' . esc_attr($this->id) . '-form" class="wc-payment-form">';
        echo '<p class="form-row"><label>' . __('Card Number', 'wc-advanced') . ' <span class="required">*</span></label><input type="text" name="custom_card_number" placeholder="4242 4242 4242 4242" autocomplete="cc-number"></p>';
        echo '<p class="form-row form-row-first"><label>' . __('Expiry', 'wc-advanced') . ' <span class="required">*</span></label><input type="text" name="custom_card_expiry" placeholder="MM/YY" autocomplete="cc-exp"></p>';
        echo '<p class="form-row form-row-last"><label>' . __('CVC', 'wc-advanced') . ' <span class="required">*</span></label><input type="text" name="custom_card_cvc" placeholder="123" autocomplete="cc-csc"></p>';
        echo '<div class="clear"></div></fieldset>';
    }

    public function validate_fields(): bool {
        if (empty($_POST['custom_card_number'])) { wc_add_notice(__('Card number is required', 'wc-advanced'), 'error'); return false; }
        if (empty($_POST['custom_card_expiry'])) { wc_add_notice(__('Expiry date is required', 'wc-advanced'), 'error'); return false; }
        if (empty($_POST['custom_card_cvc'])) { wc_add_notice(__('CVC is required', 'wc-advanced'), 'error'); return false; }
        return true;
    }

    public function process_payment($order_id): array {
        $order = wc_get_order($order_id);

        // Simulate API call to payment processor
        $response = $this->charge_card($order);

        if ($response['success']) {
            $order->payment_complete($response['transaction_id']);
            $order->add_order_note(sprintf(__('Payment completed via Custom Gateway. Transaction ID: %s', 'wc-advanced'), $response['transaction_id']));
            WC()->cart->empty_cart();
            return ['result' => 'success', 'redirect' => $this->get_return_url($order)];
        }

        wc_add_notice($response['message'] ?? __('Payment failed', 'wc-advanced'), 'error');
        return ['result' => 'fail'];
    }

    public function process_refund($order_id, $amount = null, $reason = ''): bool|\WP_Error {
        $order = wc_get_order($order_id);
        $transaction_id = $order->get_transaction_id();
        if (!$transaction_id) return new \WP_Error('no_transaction', __('No transaction ID found', 'wc-advanced'));

        // Simulate refund API call
        $order->add_order_note(sprintf(__('Refunded %s via Custom Gateway. Reason: %s', 'wc-advanced'), wc_price($amount), $reason));
        return true;
    }

    private function charge_card(\WC_Order $order): array {
        // In production: call actual payment API
        return ['success' => true, 'transaction_id' => 'txn_' . wp_generate_password(16, false)];
    }
}
