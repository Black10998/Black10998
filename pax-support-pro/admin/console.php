<?php
/**
 * Admin console view.
 *
 * @package PAX_Support_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function pax_sup_render_console() {
    if ( ! current_user_can( pax_sup_get_console_capability() ) ) {
        return;
    }

    pax_sup_ticket_prepare_tables();

    global $wpdb;
    $table = pax_sup_get_ticket_table();

    $search   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $ticket_id = isset( $_GET['ticket'] ) ? absint( $_GET['ticket'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    if ( $search ) {
        $like = '%' . $wpdb->esc_like( $search ) . '%';
        $sql  = $wpdb->prepare(
            "SELECT id, subject, status, created_at, updated_at FROM {$table} WHERE subject LIKE %s OR message LIKE %s ORDER BY updated_at DESC LIMIT 50",
            $like,
            $like
        );
    } else {
        $sql = "SELECT id, subject, status, created_at, updated_at FROM {$table} ORDER BY updated_at DESC LIMIT 50";
    }

    $rows    = $wpdb->get_results( $sql );
    $metrics = pax_sup_get_server_metrics();

    $active_ticket = $ticket_id ? pax_sup_ticket_get( $ticket_id ) : null;
    $messages      = $active_ticket ? pax_sup_ticket_get_messages( $ticket_id ) : array();

    $notice = get_transient( 'pax_sup_admin_notice' );
    if ( $notice ) {
        delete_transient( 'pax_sup_admin_notice' );
    }
    ?>
    <div class="wrap pax-console">
        <h1><?php esc_html_e( 'PAX Support Console', 'pax-support-pro' ); ?></h1>
        <?php if ( $notice ) : ?>
            <div class="notice notice-<?php echo 'error' === $notice['type'] ? 'error' : 'success'; ?>"><p><?php echo esc_html( $notice['message'] ); ?></p></div>
        <?php endif; ?>
        <form method="get" class="pax-console__search" style="margin:12px 0; display:flex; gap:8px; align-items:center;">
            <input type="hidden" name="page" value="pax-support-console" />
            <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Search tickets…', 'pax-support-pro' ); ?>" />
            <?php submit_button( __( 'Search', 'pax-support-pro' ), 'secondary', '', false ); ?>
            <a class="button" href="<?php echo esc_url( remove_query_arg( array( 'ticket', 's' ) ) ); ?>"><?php esc_html_e( 'Reset', 'pax-support-pro' ); ?></a>
        </form>
        <div class="pax-console__status" style="margin:20px 0;display:flex;flex-wrap:wrap;gap:16px;">
            <div class="card" style="background:#fff;border:1px solid #dcdcdc;border-radius:8px;padding:16px;min-width:240px;">
                <h2 style="margin-top:0;"><?php esc_html_e( 'System monitor', 'pax-support-pro' ); ?></h2>
                <ul style="margin:0;padding-left:18px;font-size:13px;line-height:1.6;">
                    <li><?php esc_html_e( 'PHP version', 'pax-support-pro' ); ?>: <strong><?php echo esc_html( $metrics['php_version'] ); ?></strong></li>
                    <li><?php esc_html_e( 'WordPress', 'pax-support-pro' ); ?>: <strong><?php echo esc_html( $metrics['wordpress'] ); ?></strong></li>
                    <li><?php esc_html_e( 'Memory usage', 'pax-support-pro' ); ?>: <strong><?php echo esc_html( $metrics['memory_usage'] ); ?></strong> / <?php echo esc_html( $metrics['memory_limit'] ); ?></li>
                    <li><?php esc_html_e( 'Server load', 'pax-support-pro' ); ?>: <strong><?php echo esc_html( $metrics['server_load'] ); ?></strong></li>
                    <li><?php esc_html_e( 'Server time', 'pax-support-pro' ); ?>: <strong><?php echo esc_html( $metrics['server_time'] ); ?></strong></li>
                </ul>
            </div>
        </div>
        <div class="pax-console__grid" style="display:flex; gap:20px; align-items:flex-start; flex-wrap:wrap;">
            <div class="pax-console__list" style="flex:1 1 360px; min-width:320px;">
                <h2 style="margin-top:0;"><?php esc_html_e( 'Recent tickets', 'pax-support-pro' ); ?></h2>
                <?php if ( $rows ) : ?>
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'ID', 'pax-support-pro' ); ?></th>
                                <th><?php esc_html_e( 'Subject', 'pax-support-pro' ); ?></th>
                                <th><?php esc_html_e( 'Status', 'pax-support-pro' ); ?></th>
                                <th><?php esc_html_e( 'Updated', 'pax-support-pro' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $rows as $row ) : ?>
                                <tr class="<?php echo $ticket_id === (int) $row->id ? 'active' : ''; ?>">
                                    <td><a href="<?php echo esc_url( add_query_arg( array( 'ticket' => (int) $row->id ), remove_query_arg( 'paged' ) ) ); ?>">#<?php echo esc_html( $row->id ); ?></a></td>
                                    <td><strong><?php echo esc_html( $row->subject ); ?></strong></td>
                                    <td><?php echo esc_html( pax_sup_format_ticket_status( $row->status ) ); ?></td>
                                    <td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $row->updated_at, false ) ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p><?php esc_html_e( 'No tickets found.', 'pax-support-pro' ); ?></p>
                <?php endif; ?>
            </div>
            <div class="pax-console__detail" style="flex:1 1 420px; min-width:320px;">
                <?php if ( $active_ticket ) : ?>
                    <div class="card" style="background:#fff;border:1px solid #dcdcdc;border-radius:8px;padding:20px;">
                        <h2 style="margin-top:0; display:flex; justify-content:space-between; align-items:center;">
                            <span><?php echo esc_html( $active_ticket->subject ); ?></span>
                            <span class="status" style="font-size:12px;padding:4px 8px;border-radius:999px;background:#f0f4f7;"><?php echo esc_html( pax_sup_format_ticket_status( $active_ticket->status ) ); ?></span>
                        </h2>
                        <p style="margin:4px 0;"><strong><?php esc_html_e( 'Ticket ID:', 'pax-support-pro' ); ?></strong> #<?php echo esc_html( $active_ticket->id ); ?></p>
                        <p style="margin:4px 0;"><strong><?php esc_html_e( 'Created:', 'pax-support-pro' ); ?></strong> <?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $active_ticket->created_at, false ) ); ?></p>
                        <p style="margin:4px 0;"><strong><?php esc_html_e( 'Last update:', 'pax-support-pro' ); ?></strong> <?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $active_ticket->updated_at, false ) ); ?></p>
                        <hr />
                        <h3><?php esc_html_e( 'Conversation', 'pax-support-pro' ); ?></h3>
                        <div class="pax-thread" style="max-height:260px; overflow:auto; border:1px solid #e3e6eb; padding:12px; border-radius:6px; background:#fafbfc;">
                            <?php foreach ( (array) $messages as $message ) : ?>
                                <div class="pax-thread__item" style="margin-bottom:12px;">
                                    <div style="font-size:12px;color:#506070;">
                                        <?php echo esc_html( strtoupper( $message->sender ) ); ?> · <?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $message->created_at, false ) ); ?>
                                    </div>
                                    <div><?php echo wpautop( wp_kses_post( $message->note ) ); ?></div>
                                </div>
                            <?php endforeach; ?>
                            <?php if ( empty( $messages ) ) : ?>
                                <p><?php esc_html_e( 'No messages yet.', 'pax-support-pro' ); ?></p>
                            <?php endif; ?>
                        </div>
                        <hr />
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom:16px;">
                            <h3><?php esc_html_e( 'Reply to user', 'pax-support-pro' ); ?></h3>
                            <textarea name="reply_message" rows="5" class="large-text" required></textarea>
                            <input type="hidden" name="action" value="pax_sup_ticket_action" />
                            <input type="hidden" name="ticket_id" value="<?php echo esc_attr( $active_ticket->id ); ?>" />
                            <input type="hidden" name="ticket_action" value="reply" />
                            <?php wp_nonce_field( 'pax_sup_ticket_action_' . $active_ticket->id ); ?>
                            <?php submit_button( __( 'Send reply', 'pax-support-pro' ), 'primary', 'submit', false, array( 'style' => 'margin-top:8px;' ) ); ?>
                        </form>
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                                <input type="hidden" name="action" value="pax_sup_ticket_action" />
                                <input type="hidden" name="ticket_id" value="<?php echo esc_attr( $active_ticket->id ); ?>" />
                                <input type="hidden" name="ticket_action" value="<?php echo 'frozen' === $active_ticket->status ? 'unfreeze' : 'freeze'; ?>" />
                                <?php wp_nonce_field( 'pax_sup_ticket_action_' . $active_ticket->id ); ?>
                                <?php submit_button( 'frozen' === $active_ticket->status ? __( 'Unfreeze', 'pax-support-pro' ) : __( 'Freeze', 'pax-support-pro' ), 'secondary', 'submit', false ); ?>
                            </form>
                            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="pax-delete-form">
                                <input type="hidden" name="action" value="pax_sup_ticket_action" />
                                <input type="hidden" name="ticket_id" value="<?php echo esc_attr( $active_ticket->id ); ?>" />
                                <input type="hidden" name="ticket_action" value="delete" />
                                <?php wp_nonce_field( 'pax_sup_ticket_action_' . $active_ticket->id ); ?>
                                <?php submit_button( __( 'Delete ticket', 'pax-support-pro' ), 'delete', 'submit', false, array( 'class' => 'pax-delete-button' ) ); ?>
                            </form>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="card" style="background:#fff;border:1px solid #dcdcdc;border-radius:8px;padding:20px;">
                        <h2 style="margin-top:0;"><?php esc_html_e( 'Select a ticket', 'pax-support-pro' ); ?></h2>
                        <p><?php esc_html_e( 'Choose a ticket from the list to view the conversation and manage status, replies, or deletion.', 'pax-support-pro' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var deleteForms = document.querySelectorAll('.pax-delete-form');
        deleteForms.forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!window.confirm('<?php echo esc_js( __( 'Are you sure you want to delete this ticket? This action cannot be undone.', 'pax-support-pro' ) ); ?>')) {
                    event.preventDefault();
                }
            });
        });
    });
    </script>
    <?php
}