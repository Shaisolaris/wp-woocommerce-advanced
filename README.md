# wp-woocommerce-advanced

## Quick Start

1. Requires WooCommerce 8.0+
2. Copy to `wp-content/plugins/wp-woocommerce-advanced/`
3. Activate in WordPress admin

### After Activation

- **Payment Gateway:** WooCommerce → Settings → Payments → Custom Payment → Enable
- **Shipping Method:** WooCommerce → Settings → Shipping → [Zone] → Add Method → Custom Shipping
- **Checkout Fields:** Automatically added (delivery date, instructions, gift wrap, VAT number)
- **Order Status:** "Awaiting Pickup" status available in order management

Test mode enabled by default — toggle to live in gateway settings.


![CI](https://github.com/Shaisolaris/wp-woocommerce-advanced/actions/workflows/ci.yml/badge.svg)

WooCommerce extensions plugin with a custom payment gateway (card fields, test/live modes, refunds, tokenization), weight-based shipping method (zone support, free shipping threshold, max weight), custom checkout fields (delivery date, instructions, gift wrap with auto-applied fee, VAT number), custom order status, and admin order column customization.

## Stack

- **Platform:** WordPress 6.0+ / WooCommerce 8.0+
- **Language:** PHP 8.1+
- **Hooks:** WooCommerce payment, shipping, checkout, and order action/filter hooks

## Components

### Custom Payment Gateway (`gateways/class-custom-gateway.php`)

Extends `WC_Payment_Gateway` with full card-present payment flow.

| Feature | Description |
|---|---|
| Card fields | Number, expiry, CVC with HTML form rendering |
| Field validation | Required field checks with `wc_add_notice` errors |
| Test mode | Toggle between test and live API keys |
| Tokenization | `supports` array includes tokenization capability |
| Payment processing | Simulated charge with transaction ID tracking |
| Refund processing | `process_refund()` with amount and reason logging |
| Order notes | Automatic notes on payment completion and refund |
| Admin settings | 8 configurable fields via WooCommerce Settings > Payments |

### Custom Shipping Method (`shipping/class-custom-shipping.php`)

Extends `WC_Shipping_Method` with weight-based rate calculation.

| Setting | Type | Default | Description |
|---|---|---|---|
| Base cost | price | $5.99 | Flat rate per shipment |
| Cost per kg | price | $1.50 | Additional rate per kilogram |
| Free shipping threshold | price | $75.00 | Cart total for free shipping (0 to disable) |
| Max weight | number | 30 kg | Maximum weight limit (method hidden if exceeded) |
| Handling fee | price | $0.00 | Additional flat handling fee |

Supports shipping zones with instance settings (configure different rates per zone).

### Custom Checkout Fields (`checkout/class-custom-fields.php`)

Adds 4 custom fields to the WooCommerce checkout form.

| Field | Type | Validation | Notes |
|---|---|---|---|
| Delivery date | date | Must be future date | Displayed in admin and emails |
| Delivery instructions | textarea | None | Placeholder text, stored as post meta |
| Gift wrap | checkbox | None | Auto-applies $4.99 fee as `WC_Order_Item_Fee` |
| VAT number | text | None | Optional, stored as post meta |

All fields are displayed in the admin order view and in order confirmation emails via hooks.

### Order Processing (`includes/class-order-processing.php`)

| Feature | Description |
|---|---|
| Custom status | "Awaiting Pickup" with admin status list integration |
| Processing hook | Auto-note on order status change to processing |
| Completed hook | Auto-note on order completion |
| Admin column | Custom "Delivery Date" column in orders list |
| Column rendering | Formatted date display from `_delivery_date` post meta |

## Plugin Architecture

```
wp-woocommerce-advanced/
├── wp-woocommerce-advanced.php            # Main plugin file, hooks, dependency check
├── gateways/
│   └── class-custom-gateway.php           # WC_Payment_Gateway: card fields, charge, refund
├── shipping/
│   └── class-custom-shipping.php          # WC_Shipping_Method: weight-based, zones, free threshold
├── checkout/
│   └── class-custom-fields.php            # Checkout fields: date, instructions, gift wrap, VAT
└── includes/
    └── class-order-processing.php         # Custom status, hooks, admin columns
```

## WooCommerce Hooks Used

| Hook | Type | Purpose |
|---|---|---|
| `woocommerce_payment_gateways` | filter | Register custom gateway |
| `woocommerce_shipping_methods` | filter | Register custom shipping |
| `woocommerce_after_order_notes` | action | Add checkout fields |
| `woocommerce_checkout_process` | action | Validate custom fields |
| `woocommerce_checkout_update_order_meta` | action | Save custom fields + add fee |
| `woocommerce_admin_order_data_after_billing_address` | action | Display in admin |
| `woocommerce_email_after_order_table` | action | Display in emails |
| `woocommerce_order_status_processing` | action | Processing note |
| `woocommerce_order_status_completed` | action | Completion note |
| `woocommerce_register_shop_order_post_statuses` | filter | Register custom status |
| `wc_order_statuses` | filter | Add to status dropdown |
| `manage_edit-shop_order_columns` | filter | Add admin column |

## Setup

```bash
git clone https://github.com/Shaisolaris/wp-woocommerce-advanced.git
# Copy to wp-content/plugins/wp-woocommerce-advanced/
# Activate in WordPress admin (requires WooCommerce)

# Configure payment gateway:
# WooCommerce > Settings > Payments > Custom Payment

# Configure shipping method:
# WooCommerce > Settings > Shipping > [Zone] > Add method > Custom Shipping
```

## Key Design Decisions

**Separate classes per concern.** Gateway, shipping, checkout, and order processing are isolated classes loaded conditionally after WooCommerce is confirmed active. This prevents fatal errors if WooCommerce is deactivated.

**Gift wrap as WC_Order_Item_Fee.** The gift wrap surcharge is added as a fee item on the order, not as a cart modification. This ensures it appears correctly on invoices, in refund calculations, and in tax computations.

**Weight-based shipping with zone awareness.** The shipping method uses `instance_form_fields` instead of global `form_fields`, enabling different rate configurations per shipping zone. A store can charge $5.99 base domestically but $12.99 for international.

**Custom order status via WordPress post statuses.** "Awaiting Pickup" is registered as a post status and added to WooCommerce's status list. This integrates with WooCommerce's order workflow, email triggers, and admin filtering.

**Validation in checkout_process hook.** Field validation happens before order creation, not after. This prevents invalid orders from being created and ensures the user sees errors on the checkout page rather than after payment.

## License

MIT
