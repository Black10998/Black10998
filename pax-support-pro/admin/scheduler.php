<?php
/**
 * Scheduler administration.
 *
 * @package PAX_Support_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function pax_sup_render_scheduler_page() {
    if ( ! current_user_can( pax_sup_get_console_capability() ) ) {
        return;
    }

    $settings   = pax_sup_get_scheduler_settings();
    $all_users  = get_users(
        array(
            'fields'  => array( 'ID', 'display_name', 'user_email' ),
            'orderby' => 'display_name',
            'order'   => 'ASC',
        )
    );
    $timezone   = isset( $settings['timezone'] ) ? $settings['timezone'] : wp_timezone_string();
    $start      = isset( $settings['hours']['start'] ) ? $settings['hours']['start'] : '09:00';
    $end        = isset( $settings['hours']['end'] ) ? $settings['hours']['end'] : '17:00';
    $slots      = isset( $settings['slots_per_hour'] ) ? (int) $settings['slots_per_hour'] : 1;
    $reminder   = isset( $settings['reminder_lead'] ) ? (int) $settings['reminder_lead'] : 60;
    $agents     = isset( $settings['agents'] ) ? (array) $settings['agents'] : array();
    $table      = pax_sup_get_schedules_table();
    global $wpdb;

    $schedules = $wpdb->get_results(
        "SELECT * FROM {$table} ORDER BY schedule_date DESC, schedule_time DESC LIMIT 100",
        ARRAY_A
    );

    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Callback Scheduler', 'pax-support-pro' ); ?></h1>

        <?php if ( isset( $_GET['pax_scheduler_saved'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
            <?php pax_sup_admin_notice( __( 'Scheduler settings updated.', 'pax-support-pro' ) ); ?>
        <?php endif; ?>

        <?php if ( isset( $_GET['pax_scheduler_status'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
            <?php pax_sup_admin_notice( __( 'Schedule updated successfully.', 'pax-support-pro' ) ); ?>
        <?php endif; ?>

        <?php if ( isset( $_GET['pax_scheduler_error'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
            <?php pax_sup_admin_notice( __( 'Unable to update the schedule.', 'pax-support-pro' ), 'error' ); ?>
        <?php endif; ?>

        <h2><?php esc_html_e( 'Working Hours & Agents', 'pax-support-pro' ); ?></h2>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'pax_sup_save_scheduler' ); ?>
            <input type="hidden" name="action" value="pax_sup_save_scheduler_settings">
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Default timezone', 'pax-support-pro' ); ?></th>
                    <td>
                        <input type="text" class="regular-text" name="timezone" value="<?php echo esc_attr( $timezone ); ?>" required>
                        <p class="description"><?php esc_html_e( 'Used when visitors do not share a timezone.', 'pax-support-pro' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Working hours', 'pax-support-pro' ); ?></th>
                    <td>
                        <label>
                            <?php esc_html_e( 'Start', 'pax-support-pro' ); ?>
                            <input type="time" name="hours[start]" value="<?php echo esc_attr( $start ); ?>">
                        </label>
                        &nbsp;
                        <label>
                            <?php esc_html_e( 'End', 'pax-support-pro' ); ?>
                            <input type="time" name="hours[end]" value="<?php echo esc_attr( $end ); ?>">
                        </label>
                        <p class="description"><?php esc_html_e( 'Visitors can only pick time slots within this range.', 'pax-support-pro' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Slots per hour', 'pax-support-pro' ); ?></th>
                    <td>
                        <input type="number" name="slots_per_hour" value="<?php echo esc_attr( $slots ); ?>" min="1" max="12">
                        <p class="description"><?php esc_html_e( 'Maximum simultaneous callbacks per hour.', 'pax-support-pro' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Reminder lead time (minutes)', 'pax-support-pro' ); ?></th>
                    <td>
                        <input type="number" name="reminder_lead" value="<?php echo esc_attr( $reminder ); ?>" min="15" step="5">
                        <p class="description"><?php esc_html_e( 'Send reminder emails this many minutes before the scheduled slot.', 'pax-support-pro' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Assign to agents', 'pax-support-pro' ); ?></th>
                    <td>
                        <select name="agents[]" multiple size="6" style="min-width:260px;">
                            <?php foreach ( $all_users as $user ) : ?>
                                <option value="<?php echo esc_attr( $user->ID ); ?>" <?php selected( in_array( $user->ID, $agents, true ) ); ?>>
                                    <?php echo esc_html( $user->display_name . ( $user->user_email ? ' (' . $user->user_email . ')' : '' ) ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e( 'Selected users receive callback notifications and appear in analytics.', 'pax-support-pro' ); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button( __( 'Save scheduler settings', 'pax-support-pro' ) ); ?>
        </form>

        <h2><?php esc_html_e( 'Upcoming callbacks', 'pax-support-pro' ); ?></h2>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Date', 'pax-support-pro' ); ?></th>
                    <th><?php esc_html_e( 'Time', 'pax-support-pro' ); ?></th>
                    <th><?php esc_html_e( 'Timezone', 'pax-support-pro' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'pax-support-pro' ); ?></th>
                    <th><?php esc_html_e( 'Contact', 'pax-support-pro' ); ?></th>
                    <th><?php esc_html_e( 'Note', 'pax-support-pro' ); ?></th>
                    <th><?php esc_html_e( 'Agent', 'pax-support-pro' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'pax-support-pro' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $schedules ) ) : ?>
                    <tr>
                        <td colspan="8"><?php esc_html_e( 'No scheduled callbacks found.', 'pax-support-pro' ); ?></td>
                    </tr>
                <?php else : ?>
                    <?php foreach ( $schedules as $schedule ) :
                        $schedule = pax_sup_prepare_schedule_row( $schedule );
                        $agent    = $schedule['agent_id'] ? get_userdata( $schedule['agent_id'] ) : null;
                        ?>
                        <tr>
                            <td><?php echo esc_html( $schedule['schedule_date'] ); ?></td>
                            <td><?php echo esc_html( $schedule['schedule_time'] ); ?></td>
                            <td><?php echo esc_html( $schedule['timezone'] ); ?></td>
                            <td><?php echo esc_html( ucfirst( $schedule['status'] ) ); ?></td>
                            <td><?php echo esc_html( $schedule['contact'] ); ?></td>
                            <td><?php echo esc_html( wp_trim_words( $schedule['note'], 16, '…' ) ); ?></td>
                            <td><?php echo esc_html( $agent instanceof WP_User ? $agent->display_name : __( 'Unassigned', 'pax-support-pro' ) ); ?></td>
                            <td>
                                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;margin-right:6px;">
                                    <?php wp_nonce_field( 'pax_sup_update_schedule_status' ); ?>
                                    <input type="hidden" name="action" value="pax_sup_update_schedule_status">
                                    <input type="hidden" name="schedule_id" value="<?php echo esc_attr( $schedule['id'] ); ?>">
                                    <select name="status">
                                        <?php foreach ( array( 'pending', 'confirmed', 'done', 'canceled' ) as $status ) : ?>
                                            <option value="<?php echo esc_attr( $status ); ?>" <?php selected( $schedule['status'], $status ); ?>><?php echo esc_html( ucfirst( $status ) ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php submit_button( __( 'Update', 'pax-support-pro' ), 'secondary', 'submit', false ); ?>
                                </form>
                                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;">
                                    <?php wp_nonce_field( 'pax_sup_delete_schedule' ); ?>
                                    <input type="hidden" name="action" value="pax_sup_delete_schedule">
                                    <input type="hidden" name="schedule_id" value="<?php echo esc_attr( $schedule['id'] ); ?>">
                                    <?php submit_button( __( 'Delete', 'pax-support-pro' ), 'link-delete', 'submit', false, array( 'onclick' => 'return confirm("' . esc_js( __( 'Delete this schedule?', 'pax-support-pro' ) ) . '");' ) ); ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

function pax_sup_handle_scheduler_save() {
    if ( ! current_user_can( pax_sup_get_console_capability() ) ) {
        wp_die( esc_html__( 'You do not have permission to manage the scheduler.', 'pax-support-pro' ) );
    }

    check_admin_referer( 'pax_sup_save_scheduler' );

    $timezone = sanitize_text_field( wp_unslash( $_POST['timezone'] ?? '' ) );
    $hours    = isset( $_POST['hours'] ) && is_array( $_POST['hours'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['hours'] ) ) : array();
    $slots    = isset( $_POST['slots_per_hour'] ) ? (int) wp_unslash( $_POST['slots_per_hour'] ) : 1;
    $reminder = isset( $_POST['reminder_lead'] ) ? (int) wp_unslash( $_POST['reminder_lead'] ) : 60;
    $agents   = isset( $_POST['agents'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['agents'] ) ) : array();

    pax_sup_save_scheduler_settings(
        array(
            'timezone'       => $timezone,
            'hours'          => $hours,
            'slots_per_hour' => $slots,
            'agents'         => $agents,
            'reminder_lead'  => $reminder,
        )
    );

    wp_safe_redirect( add_query_arg( 'pax_scheduler_saved', '1', admin_url( 'admin.php?page=pax-support-scheduler' ) ) );
    exit;
}
add_action( 'admin_post_pax_sup_save_scheduler_settings', 'pax_sup_handle_scheduler_save' );

function pax_sup_handle_schedule_status_update() {
    if ( ! current_user_can( pax_sup_get_console_capability() ) ) {
        wp_die( esc_html__( 'You do not have permission to manage schedules.', 'pax-support-pro' ) );
    }

    check_admin_referer( 'pax_sup_update_schedule_status' );

    $schedule_id = isset( $_POST['schedule_id'] ) ? (int) wp_unslash( $_POST['schedule_id'] ) : 0;
    $status      = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
    $allowed     = array( 'pending', 'confirmed', 'done', 'canceled' );

    if ( ! $schedule_id || ! in_array( $status, $allowed, true ) ) {
        wp_safe_redirect( add_query_arg( 'pax_scheduler_error', '1', admin_url( 'admin.php?page=pax-support-scheduler' ) ) );
        exit;
    }

    global $wpdb;
    $table    = pax_sup_get_schedules_table();
    $schedule = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $schedule_id ), ARRAY_A );

    if ( empty( $schedule ) ) {
        wp_safe_redirect( add_query_arg( 'pax_scheduler_error', '1', admin_url( 'admin.php?page=pax-support-scheduler' ) ) );
        exit;
    }

    $wpdb->update(
        $table,
        array(
            'status'      => $status,
            'updated_at'  => current_time( 'mysql' ),
            'reminder_sent' => ( 'pending' === $status || 'confirmed' === $status ) ? (int) $schedule['reminder_sent'] : 1,
        ),
        array( 'id' => $schedule_id ),
        array( '%s', '%s', '%d' ),
        array( '%d' )
    );

    $schedule['status'] = $status;

    pax_sup_notify_schedule_event( $schedule, 'canceled' === $status ? 'canceled' : 'updated' );

    wp_safe_redirect( add_query_arg( 'pax_scheduler_status', '1', admin_url( 'admin.php?page=pax-support-scheduler' ) ) );
    exit;
}
add_action( 'admin_post_pax_sup_update_schedule_status', 'pax_sup_handle_schedule_status_update' );

function pax_sup_handle_schedule_delete() {
    if ( ! current_user_can( pax_sup_get_console_capability() ) ) {
        wp_die( esc_html__( 'You do not have permission to manage schedules.', 'pax-support-pro' ) );
    }

    check_admin_referer( 'pax_sup_delete_schedule' );

    $schedule_id = isset( $_POST['schedule_id'] ) ? (int) wp_unslash( $_POST['schedule_id'] ) : 0;

    if ( ! $schedule_id ) {
        wp_safe_redirect( add_query_arg( 'pax_scheduler_error', '1', admin_url( 'admin.php?page=pax-support-scheduler' ) ) );
        exit;
    }

    global $wpdb;
    $table    = pax_sup_get_schedules_table();
    $schedule = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $schedule_id ), ARRAY_A );

    if ( $schedule ) {
        $wpdb->delete( $table, array( 'id' => $schedule_id ), array( '%d' ) );
        $schedule['status'] = 'canceled';
        pax_sup_notify_schedule_event( $schedule, 'canceled' );
    }

    wp_safe_redirect( add_query_arg( 'pax_scheduler_status', '1', admin_url( 'admin.php?page=pax-support-scheduler' ) ) );
    exit;
}
add_action( 'admin_post_pax_sup_delete_schedule', 'pax_sup_handle_schedule_delete' );
