<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Remove the auto-created page permanently.
$page_id = (int) get_option( 'ca_page_id', 0 );
if ( $page_id ) {
    wp_delete_post( $page_id, true );
}

delete_option( 'ca_page_id' );

// Clean up cached transients.
global $wpdb;
$wpdb->query(
    "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ca_products_%' OR option_name LIKE '_transient_timeout_ca_products_%'"
);
