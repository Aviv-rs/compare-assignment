<?php
/**
 * Plugin Name: Compare Assignment
 * Description: Displays a searchable, paginated product table from DummyJSON.
 * Version:     1.0.0
 * Author:      Aviv
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'CA_PLUGIN_FILE', __FILE__ );
define( 'CA_PLUGIN_VERSION', '1.0.0' );

require_once __DIR__ . '/includes/class-product.php';
require_once __DIR__ . '/includes/class-product-query.php';
require_once __DIR__ . '/includes/class-product-query-result.php';
require_once __DIR__ . '/includes/interface-product-repository.php';
require_once __DIR__ . '/includes/class-dummyjson-repository.php';
require_once __DIR__ . '/includes/class-product-table-renderer.php';
require_once __DIR__ . '/includes/class-plugin.php';

// --- Activation / Deactivation ---

register_activation_hook( __FILE__, 'ca_activate' );
register_deactivation_hook( __FILE__, 'ca_deactivate' );

function ca_activate(): void {
    // Create the "Compare Assignment" page with our shortcode if it doesn't exist.
    if ( ca_get_page_id() ) {
        return;
    }

    $page_id = wp_insert_post( array(
        'post_title'   => 'Compare Assignment',
        'post_content' => '[compare_assignment]',
        'post_status'  => 'publish',
        'post_type'    => 'page',
    ) );

    if ( ! is_wp_error( $page_id ) ) {
        update_option( 'ca_page_id', $page_id );
    }
}

function ca_deactivate(): void {
    $page_id = ca_get_page_id();

    if ( $page_id ) {
        wp_trash_post( $page_id );
        delete_option( 'ca_page_id' );
    }
}

function ca_get_page_id(): int {
    return (int) get_option( 'ca_page_id', 0 );
}

// --- Bootstrap ---

$plugin = new CompareAssignmentPlugin( new DummyJsonProductRepository() );
$plugin->register();
