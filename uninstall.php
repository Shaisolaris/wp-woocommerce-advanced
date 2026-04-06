<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit;
// Remove custom order meta
delete_post_meta_by_key('_delivery_date');
delete_post_meta_by_key('_delivery_instructions');
delete_post_meta_by_key('_gift_wrap');
delete_post_meta_by_key('_company_vat');
wp_cache_flush();
