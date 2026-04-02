<?php
defined('ABSPATH') || exit;

class WC_Advanced_Order_Processing {
    public static function init(): void {
        add_action('woocommerce_order_status_processing', [__CLASS__, 'on_processing']);
        add_action('woocommerce_order_status_completed', [__CLASS__, 'on_completed']);
        add_filter('woocommerce_register_shop_order_post_statuses', [__CLASS__, 'register_custom_statuses']);
        add_filter('wc_order_statuses', [__CLASS__, 'add_custom_statuses']);
        add_filter('manage_edit-shop_order_columns', [__CLASS__, 'add_admin_columns']);
        add_action('manage_shop_order_posts_custom_column', [__CLASS__, 'render_admin_columns'], 10, 2);
    }

    public static function on_processing(int $order_id): void {
        $order = wc_get_order($order_id);
        $order->add_order_note(__('Order moved to processing — inventory reserved.', 'wc-advanced'));
    }

    public static function on_completed(int $order_id): void {
        $order = wc_get_order($order_id);
        $order->add_order_note(__('Order completed — fulfillment confirmed.', 'wc-advanced'));
    }

    public static function register_custom_statuses(array $statuses): array {
        $statuses['wc-awaiting-pickup'] = [
            'label' => _x('Awaiting Pickup', 'Order status', 'wc-advanced'),
            'public' => false, 'show_in_admin_status_list' => true, 'show_in_admin_all_list' => true,
            'label_count' => _n_noop('Awaiting Pickup <span class="count">(%s)</span>', 'Awaiting Pickup <span class="count">(%s)</span>', 'wc-advanced'),
        ];
        return $statuses;
    }

    public static function add_custom_statuses(array $statuses): array {
        $statuses['wc-awaiting-pickup'] = _x('Awaiting Pickup', 'Order status', 'wc-advanced');
        return $statuses;
    }

    public static function add_admin_columns(array $columns): array {
        $columns['delivery_date'] = __('Delivery Date', 'wc-advanced');
        return $columns;
    }

    public static function render_admin_columns(string $column, int $post_id): void {
        if ($column === 'delivery_date') {
            $date = get_post_meta($post_id, '_delivery_date', true);
            echo $date ? esc_html(date_i18n(get_option('date_format'), strtotime($date))) : '—';
        }
    }
}
