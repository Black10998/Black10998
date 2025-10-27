<?php
/**
 * Installation hooks.
 *
 * @package PAX_Support_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function pax_sup_activate() {
    pax_sup_update_options( array() );
    pax_sup_ensure_ticket_tables();

    if ( function_exists( 'pax_sup_updater' ) ) {
        pax_sup_updater()->maybe_schedule_checks();
    }

    wp_clear_scheduled_hook( 'pax_sup_schedule_reminders' );

    if ( ! wp_next_scheduled( 'pax_sup_schedule_reminders' ) ) {
        wp_schedule_event( time() + MINUTE_IN_SECONDS, 'hourly', 'pax_sup_schedule_reminders' );
    }
}

register_activation_hook( PAX_SUP_FILE, 'pax_sup_activate' );

add_action( 'plugins_loaded', 'pax_sup_ensure_ticket_tables' );

function pax_sup_deactivate() {
    wp_clear_scheduled_hook( 'pax_sup_schedule_reminders' );
}

register_deactivation_hook( PAX_SUP_FILE, 'pax_sup_deactivate' );

add_action( 'pax_sup_schedule_reminders', 'pax_sup_handle_schedule_reminders' );