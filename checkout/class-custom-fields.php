<?php
defined('ABSPATH') || exit;

class WC_Custom_Checkout_Fields {
    public static function init(): void {
        add_action('woocommerce_after_order_notes', [__CLASS__, 'add_custom_fields']);
        add_action('woocommerce_checkout_process', [__CLASS__, 'validate_fields']);
        add_action('woocommerce_checkout_update_order_meta', [__CLASS__, 'save_fields']);
        add_action('woocommerce_admin_order_data_after_billing_address', [__CLASS__, 'display_in_admin']);
        add_action('woocommerce_email_after_order_table', [__CLASS__, 'display_in_email'], 10, 1);
    }

    public static function add_custom_fields(\WC_Checkout $checkout): void {
        echo '<div id="wc-advanced-custom-fields"><h3>' . __('Additional Information', 'wc-advanced') . '</h3>';

        woocommerce_form_field('delivery_date', [
            'type' => 'date', 'class' => ['form-row-wide'], 'label' => __('Preferred Delivery Date', 'wc-advanced'), 'required' => false,
        ], $checkout->get_value('delivery_date'));

        woocommerce_form_field('delivery_instructions', [
            'type' => 'textarea', 'class' => ['form-row-wide'], 'label' => __('Delivery Instructions', 'wc-advanced'), 'placeholder' => __('e.g., Leave at front door', 'wc-advanced'),
        ], $checkout->get_value('delivery_instructions'));

        woocommerce_form_field('gift_wrap', [
            'type' => 'checkbox', 'class' => ['form-row-wide'], 'label' => __('Gift wrap this order (+$4.99)', 'wc-advanced'),
        ], $checkout->get_value('gift_wrap'));

        woocommerce_form_field('company_vat', [
            'type' => 'text', 'class' => ['form-row-wide'], 'label' => __('VAT Number (optional)', 'wc-advanced'),
        ], $checkout->get_value('company_vat'));

        echo '</div>';
    }

    public static function validate_fields(): void {
        if (!empty($_POST['delivery_date'])) {
            $date = strtotime(sanitize_text_field($_POST['delivery_date']));
            if ($date && $date < strtotime('tomorrow')) {
                wc_add_notice(__('Delivery date must be in the future.', 'wc-advanced'), 'error');
            }
        }
    }

    public static function save_fields(int $order_id): void {
        $fields = ['delivery_date', 'delivery_instructions', 'gift_wrap', 'company_vat'];
        foreach ($fields as $field) {
            if (!empty($_POST[$field])) {
                update_post_meta($order_id, '_' . $field, sanitize_text_field($_POST[$field]));
            }
        }

        // Add gift wrap fee
        if (!empty($_POST['gift_wrap'])) {
            $order = wc_get_order($order_id);
            $fee = new WC_Order_Item_Fee();
            $fee->set_name(__('Gift Wrap', 'wc-advanced'));
            $fee->set_amount('4.99');
            $fee->set_total('4.99');
            $order->add_item($fee);
            $order->calculate_totals();
        }
    }

    public static function display_in_admin(\WC_Order $order): void {
        $fields = ['delivery_date' => 'Delivery Date', 'delivery_instructions' => 'Delivery Instructions', 'gift_wrap' => 'Gift Wrap', 'company_vat' => 'VAT Number'];
        foreach ($fields as $key => $label) {
            $value = get_post_meta($order->get_id(), '_' . $key, true);
            if ($value) echo '<p><strong>' . esc_html($label) . ':</strong> ' . esc_html($value) . '</p>';
        }
    }

    public static function display_in_email(\WC_Order $order): void {
        self::display_in_admin($order);
    }
}
