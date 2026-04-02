# wp-woocommerce-advanced

WooCommerce extensions plugin with custom payment gateway (card fields, refunds, test mode), weight-based shipping method (zone support, free shipping threshold), custom checkout fields (delivery date, instructions, gift wrap, VAT), custom order status, and admin column customization.

## Components

### Custom Payment Gateway (`gateways/class-custom-gateway.php`)
- Card number/expiry/CVC fields with validation
- Test mode with separate API keys
- Tokenization support
- Refund processing
- Transaction ID tracking with order notes

### Custom Shipping Method (`shipping/class-custom-shipping.php`)
- Weight-based calculation (base cost + per-kg rate)
- Free shipping threshold
- Maximum weight limit
- Handling fee
- Shipping zone support with instance settings

### Custom Checkout Fields (`checkout/class-custom-fields.php`)
- Preferred delivery date (future date validation)
- Delivery instructions textarea
- Gift wrap checkbox (+$4.99 fee auto-applied)
- VAT number field
- Displayed in admin, order emails

### Order Processing (`includes/class-order-processing.php`)
- Custom order status: "Awaiting Pickup"
- Processing/completed hooks with order notes
- Custom admin column for delivery date

## Setup
```bash
git clone https://github.com/Shaisolaris/wp-woocommerce-advanced.git
# Copy to wp-content/plugins/wp-woocommerce-advanced
# Activate in WordPress admin (requires WooCommerce)
# Configure gateway in WooCommerce > Settings > Payments
# Configure shipping in WooCommerce > Settings > Shipping > Add method
```

## License
MIT
