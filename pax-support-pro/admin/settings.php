<?php
/**
 * Admin settings and menu registration.
 *
 * @package PAX_Support_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function pax_sup_get_console_capability() {
    $options = pax_sup_get_options();

    return ! empty( $options['console_cap'] ) ? $options['console_cap'] : 'manage_options';
}

function pax_sup_enqueue_support_button_assets() {
    static $loaded = false;

    if ( $loaded ) {
        return;
    }

    $loaded = true;

    wp_register_script( 'pax-support-admin', '', array(), PAX_SUP_VER, true );
    wp_enqueue_script( 'pax-support-admin' );
    wp_localize_script(
        'pax-support-admin',
        'paxSupportAdmin',
        array(
            'ajax'  => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'pax_sup_support_click' ),
        )
    );

    $script = 'window.addEventListener("DOMContentLoaded",function(){var buttons=document.querySelectorAll("[data-pax-support-button]");buttons.forEach(function(btn){btn.addEventListener("click",function(){if(!window.paxSupportAdmin||!paxSupportAdmin.ajax){return;}var data=new window.FormData();data.append("action","pax_sup_support_click");data.append("nonce",paxSupportAdmin.nonce);if(navigator.sendBeacon){paxSupSendBeacon(paxSupportAdmin.ajax,data);}else{fetch(paxSupportAdmin.ajax,{method:"POST",credentials:"same-origin",body:data});}});});});function paxSupSendBeacon(url,form){var params=new URLSearchParams();form.forEach(function(value,key){params.append(key,value);});navigator.sendBeacon(url,params);}';
    wp_add_inline_script( 'pax-support-admin', $script );
}

function pax_sup_enqueue_admin_assets( $hook ) {
    if ( strpos( $hook, 'pax-support' ) === false ) {
        return;
    }

    pax_sup_enqueue_support_button_assets();
    wp_enqueue_style( 'wp-components' );

    // Enqueue modern settings UI assets
    if ( strpos( $hook, 'pax-support-settings' ) !== false ) {
        wp_enqueue_style(
            'pax-settings-modern',
            PAX_SUP_URL . 'admin/css/settings-modern.css',
            array(),
            PAX_SUP_VER
        );
        
        wp_enqueue_script(
            'pax-settings-modern',
            PAX_SUP_URL . 'admin/js/settings-modern.js',
            array(),
            PAX_SUP_VER,
            true
        );
    }

    $options = pax_sup_get_options();
    $accent  = sanitize_hex_color( $options['color_accent'] ?? '#e53935' );
    if ( empty( $accent ) ) {
        $accent = '#e53935';
    }

    $style = '.pax-support-dev-cta{position:absolute;top:16px;right:16px;z-index:10;text-align:right}.pax-support-dev-cta .pax-support-dev-button{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:999px;font-weight:600;text-decoration:none;color:#fff;background:linear-gradient(135deg,' . $accent . ',rgba(255,255,255,0.12));box-shadow:0 12px 32px rgba(0,0,0,0.35);border:1px solid rgba(255,255,255,0.2);backdrop-filter:blur(6px);transition:transform .2s ease, box-shadow .2s ease}.pax-support-dev-cta .pax-support-dev-button:hover{transform:translateY(-2px);box-shadow:0 16px 40px rgba(0,0,0,0.4)}.pax-support-dev-cta .pax-support-dev-button svg{width:18px;height:18px;fill:#fff}@media(max-width:782px){.pax-support-dev-cta{position:static;margin-bottom:12px;text-align:left}}';
    wp_add_inline_style( 'wp-components', $style );
}
add_action( 'admin_enqueue_scripts', 'pax_sup_enqueue_admin_assets' );

function pax_sup_enqueue_support_button_front() {
    if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) || ! is_admin_bar_showing() ) {
        return;
    }

    pax_sup_enqueue_support_button_assets();
}
add_action( 'wp_enqueue_scripts', 'pax_sup_enqueue_support_button_front' );

function pax_sup_admin_button_styles() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $options = pax_sup_get_options();
    $accent  = sanitize_hex_color( $options['color_accent'] ?? '#e53935' );
    if ( empty( $accent ) ) {
        $accent = '#e53935';
    }

    echo '<style id="pax-support-admin-button">#wpadminbar #wp-admin-bar-pax-support-dev>.ab-item{background:linear-gradient(135deg,' . $accent . ',rgba(255,255,255,0.18));color:#fff !important;border-radius:999px;padding:0 12px;display:flex;align-items:center;gap:6px;box-shadow:0 6px 18px rgba(0,0,0,0.4)}#wpadminbar #wp-admin-bar-pax-support-dev .ab-icon{margin-right:4px}#wpadminbar #wp-admin-bar-pax-support-dev>.ab-item:hover{opacity:0.95}</style>';
}
add_action( 'admin_head', 'pax_sup_admin_button_styles' );
add_action( 'wp_head', 'pax_sup_admin_button_styles' );

function pax_sup_add_admin_bar_support_button( WP_Admin_Bar $admin_bar ) {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $admin_bar->add_node(
        array(
            'id'    => 'pax-support-dev',
            'title' => '<span class="ab-icon dashicons dashicons-heart"></span><span class="ab-label">' . esc_html__( 'Support Developer', 'pax-support-pro' ) . '</span>',
            'href'  => 'https://www.paypal.me/AhmadAlkhalaf29',
            'meta'  => array(
                'target' => '_blank',
                'rel'    => 'noopener noreferrer',
                'class'  => 'pax-support-dev-admin-bar',
                'data-pax-support-button' => '1',
            ),
        )
    );
}
add_action( 'admin_bar_menu', 'pax_sup_add_admin_bar_support_button', 90 );

function pax_sup_handle_support_click() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error();
    }

    check_ajax_referer( 'pax_sup_support_click', 'nonce' );

    pax_sup_log_event(
        'support_click',
        array(
            'user_id' => get_current_user_id(),
            'ip'      => pax_sup_ip(),
            'time'    => current_time( 'mysql' ),
        )
    );

    wp_send_json_success();
}
add_action( 'wp_ajax_pax_sup_support_click', 'pax_sup_handle_support_click' );

function pax_sup_handle_manual_backup_request() {
    if ( ! current_user_can( pax_sup_get_console_capability() ) ) {
        wp_die();
    }

    check_admin_referer( 'pax_sup_backup_now' );

    $result = pax_sup_run_backup( 'manual' );

    if ( is_wp_error( $result ) ) {
        pax_sup_store_admin_notice( $result->get_error_message(), 'error' );
    } else {
        pax_sup_store_admin_notice( sprintf( __( 'Backup created successfully (%s).', 'pax-support-pro' ), basename( $result ) ), 'success' );
    }

    wp_safe_redirect( admin_url( 'admin.php?page=pax-support-settings' ) );
    exit;
}
add_action( 'admin_post_pax_sup_backup_now', 'pax_sup_handle_manual_backup_request' );

function pax_sup_register_dashboard_widget() {
    if ( ! current_user_can( pax_sup_get_console_capability() ) ) {
        return;
    }

    wp_add_dashboard_widget( 'pax_sup_monitor', __( 'PAX Support Monitor', 'pax-support-pro' ), 'pax_sup_render_dashboard_widget' );
}
add_action( 'wp_dashboard_setup', 'pax_sup_register_dashboard_widget' );

function pax_sup_render_dashboard_widget() {
    pax_sup_ensure_ticket_tables();
    $metrics = pax_sup_get_server_metrics();

    global $wpdb;
    $table = pax_sup_get_ticket_table();
    $counts = array(
        'open'   => 0,
        'closed' => 0,
        'frozen' => 0,
    );

    if ( $table ) {
        $rows = $wpdb->get_results( "SELECT status, COUNT(*) AS total FROM {$table} GROUP BY status" );
        foreach ( (array) $rows as $row ) {
            $status = $row->status;
            if ( isset( $counts[ $status ] ) ) {
                $counts[ $status ] = (int) $row->total;
            }
        }
    }

    echo '<ul class="pax-monitor">';
    echo '<li><strong>' . esc_html__( 'PHP Version', 'pax-support-pro' ) . ':</strong> ' . esc_html( $metrics['php_version'] ) . '</li>';
    echo '<li><strong>' . esc_html__( 'WordPress', 'pax-support-pro' ) . ':</strong> ' . esc_html( $metrics['wordpress'] ) . '</li>';
    echo '<li><strong>' . esc_html__( 'Memory Usage', 'pax-support-pro' ) . ':</strong> ' . esc_html( $metrics['memory_usage'] ) . ' / ' . esc_html( $metrics['memory_limit'] ) . '</li>';
    echo '<li><strong>' . esc_html__( 'Server Load', 'pax-support-pro' ) . ':</strong> ' . esc_html( $metrics['server_load'] ) . '</li>';
    echo '<li><strong>' . esc_html__( 'Open Tickets', 'pax-support-pro' ) . ':</strong> ' . esc_html( $counts['open'] ) . '</li>';
    echo '<li><strong>' . esc_html__( 'Frozen Tickets', 'pax-support-pro' ) . ':</strong> ' . esc_html( $counts['frozen'] ) . '</li>';
    echo '<li><strong>' . esc_html__( 'Closed Tickets', 'pax-support-pro' ) . ':</strong> ' . esc_html( $counts['closed'] ) . '</li>';
    echo '</ul>';
}

function pax_sup_register_admin_menu() {
    $cap = pax_sup_get_console_capability();

    add_menu_page(
        __( 'PAX Support Pro', 'pax-support-pro' ),
        __( 'PAX Support', 'pax-support-pro' ),
        $cap,
        'pax-support-dashboard',
        'pax_sup_render_dashboard',
        'dashicons-format-chat',
        58
    );

    add_submenu_page( 'pax-support-dashboard', __( 'Dashboard', 'pax-support-pro' ), __( 'Dashboard', 'pax-support-pro' ), $cap, 'pax-support-dashboard', 'pax_sup_render_dashboard' );
    add_submenu_page( 'pax-support-dashboard', __( 'Settings', 'pax-support-pro' ), __( 'Settings', 'pax-support-pro' ), $cap, 'pax-support-settings', 'pax_sup_render_settings' );
    add_submenu_page( 'pax-support-dashboard', __( 'Console', 'pax-support-pro' ), __( 'Console', 'pax-support-pro' ), $cap, 'pax-support-console', 'pax_sup_render_console' );
    add_submenu_page( 'pax-support-dashboard', __( 'Tickets', 'pax-support-pro' ), __( 'Tickets', 'pax-support-pro' ), $cap, 'pax-support-tickets', 'pax_sup_render_tickets' );
    add_submenu_page( 'pax-support-dashboard', __( 'Scheduler', 'pax-support-pro' ), __( 'Scheduler', 'pax-support-pro' ), $cap, 'pax-support-scheduler', 'pax_sup_render_scheduler_page' );
    add_submenu_page( 'pax-support-dashboard', __( 'Feedback', 'pax-support-pro' ), __( 'Feedback', 'pax-support-pro' ), $cap, 'pax-support-feedback', 'pax_sup_render_feedback_page' );
}
add_action( 'admin_menu', 'pax_sup_register_admin_menu' );

function pax_sup_render_feedback_page() {
    $url = 'https://www.paypal.me/AhmadAlkhalaf29';
    wp_safe_redirect( $url );
    exit;
}

function pax_sup_render_dashboard() {
    if ( ! current_user_can( pax_sup_get_console_capability() ) ) {
        return;
    }

    $options = pax_sup_get_options();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'PAX Support Dashboard', 'pax-support-pro' ); ?></h1>
        <p><?php esc_html_e( 'Welcome to PAX Support Pro. Use the quick stats below to monitor chat and ticket activity.', 'pax-support-pro' ); ?></p>
        <div class="pax-cards" style="display:flex;gap:20px;flex-wrap:wrap;margin-top:20px;">
            <div class="card" style="background:#fff;border:1px solid #dcdcdc;border-radius:8px;padding:20px;min-width:220px;">
                <h2 style="margin-top:0;"><?php esc_html_e( 'Status', 'pax-support-pro' ); ?></h2>
                <p><?php echo esc_html( $options['enabled'] ? __( 'Chat launcher is active.', 'pax-support-pro' ) : __( 'Chat launcher is disabled.', 'pax-support-pro' ) ); ?></p>
                <p><?php echo esc_html( $options['enable_ticket'] ? __( 'Ticket intake enabled.', 'pax-support-pro' ) : __( 'Ticket intake disabled.', 'pax-support-pro' ) ); ?></p>
            </div>
            <div class="card" style="background:#fff;border:1px solid #dcdcdc;border-radius:8px;padding:20px;min-width:220px;">
                <h2 style="margin-top:0;"><?php esc_html_e( 'OpenAI', 'pax-support-pro' ); ?></h2>
                <p><?php echo esc_html( ( $options['ai_assistant_enabled'] && $options['openai_enabled'] ) ? __( 'AI assistant is online.', 'pax-support-pro' ) : __( 'AI assistant disabled.', 'pax-support-pro' ) ); ?></p>
                <?php if ( ! empty( $options['openai_model'] ) ) : ?>
                    <p><strong><?php esc_html_e( 'Model:', 'pax-support-pro' ); ?></strong> <?php echo esc_html( $options['openai_model'] ); ?></p>
                <?php endif; ?>
            </div>
            <div class="card" style="background:#fff;border:1px solid #dcdcdc;border-radius:8px;padding:20px;min-width:220px;">
                <h2 style="margin-top:0;"><?php esc_html_e( 'Quick Links', 'pax-support-pro' ); ?></h2>
                <ul style="margin:0;padding-left:18px;">
                    <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=pax-support-settings' ) ); ?>"><?php esc_html_e( 'Update settings', 'pax-support-pro' ); ?></a></li>
                    <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=pax-support-console' ) ); ?>"><?php esc_html_e( 'Open console', 'pax-support-pro' ); ?></a></li>
                    <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=pax-support-console' ) ); ?>"><?php esc_html_e( 'View tickets', 'pax-support-pro' ); ?></a></li>
                </ul>
            </div>
        </div>
    </div>
    <?php
}

function pax_sup_admin_notice( $message, $type = 'success' ) {
    printf(
        '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
        esc_attr( $type ),
        wp_kses_post( $message )
    );
}

function pax_sup_render_settings() {
    if ( ! current_user_can( pax_sup_get_console_capability() ) ) {
        return;
    }

    // Handle form submission
    if ( 'POST' === $_SERVER['REQUEST_METHOD'] && check_admin_referer( 'pax_sup_save_settings' ) ) {
        $input = wp_unslash( $_POST );

        // Process chat menu items
        $menu_items = array();
        $default_menu = pax_sup_default_menu_items();
        
        if ( ! empty( $input['menu_items'] ) && is_array( $input['menu_items'] ) ) {
            foreach ( $default_menu as $key => $default_item ) {
                if ( isset( $input['menu_items'][ $key ] ) ) {
                    $menu_items[ $key ] = array(
                        'label'   => sanitize_text_field( $input['menu_items'][ $key ]['label'] ?? $default_item['label'] ),
                        'visible' => ! empty( $input['menu_items'][ $key ]['visible'] ) ? 1 : 0,
                    );
                } else {
                    $menu_items[ $key ] = $default_item;
                }
            }
        } else {
            $menu_items = $default_menu;
        }

        $new = array(
            'enabled'              => ! empty( $input['enabled'] ) ? 1 : 0,
            'enable_chat'          => ! empty( $input['enable_chat'] ) ? 1 : 0,
            'enable_ticket'        => ! empty( $input['enable_ticket'] ) ? 1 : 0,
            'enable_console'       => ! empty( $input['enable_console'] ) ? 1 : 0,
            'enable_speed'         => ! empty( $input['enable_speed'] ) ? 1 : 0,
            'enable_offline_guard' => ! empty( $input['enable_offline_guard'] ) ? 1 : 0,
            'ai_assistant_enabled' => ! empty( $input['ai_assistant_enabled'] ) ? 1 : 0,
            'openai_enabled'       => ! empty( $input['openai_enabled'] ) ? 1 : 0,
            'openai_key'           => sanitize_text_field( $input['openai_key'] ?? '' ),
            'openai_model'         => sanitize_text_field( $input['openai_model'] ?? 'gpt-4o-mini' ),
            'openai_temperature'   => min( 1, max( 0, floatval( $input['openai_temperature'] ?? 0.35 ) ) ),
            'launcher_position'    => in_array( $input['launcher_position'] ?? 'bottom-left', array( 'bottom-left', 'bottom-right', 'top-left', 'top-right' ), true ) ? $input['launcher_position'] : 'bottom-left',
            'launcher_auto_open'   => ! empty( $input['launcher_auto_open'] ) ? 1 : 0,
            'toggle_on_click'      => ! empty( $input['toggle_on_click'] ) ? 1 : 0,
            'brand_name'           => sanitize_text_field( $input['brand_name'] ?? 'PAX SUPPORT' ),
            'color_accent'         => sanitize_hex_color( $input['color_accent'] ?? '#e53935' ),
            'color_bg'             => sanitize_hex_color( $input['color_bg'] ?? '#0d0f12' ),
            'color_panel'          => sanitize_hex_color( $input['color_panel'] ?? '#121418' ),
            'color_border'         => sanitize_hex_color( $input['color_border'] ?? '#2a2d33' ),
            'color_text'           => sanitize_hex_color( $input['color_text'] ?? '#e8eaf0' ),
            'color_sub'            => sanitize_hex_color( $input['color_sub'] ?? '#9aa0a8' ),
            'live_agent_email'     => sanitize_email( $input['live_agent_email'] ?? get_option( 'admin_email' ) ),
            'callback_enabled'     => ! empty( $input['callback_enabled'] ) ? 1 : 0,
            'help_center_url'      => esc_url_raw( $input['help_center_url'] ?? home_url( '/help/' ) ),
            'console_cap'          => sanitize_text_field( $input['console_cap'] ?? 'manage_options' ),
            'ticket_cooldown_days' => max( 0, intval( $input['ticket_cooldown_days'] ?? 0 ) ),
            'auto_update_enabled'    => ! empty( $input['auto_update_enabled'] ) ? 1 : 0,
            'update_check_frequency' => in_array( $input['update_check_frequency'] ?? 'daily', array( 'daily', 'weekly' ), true ) ? $input['update_check_frequency'] : 'daily',
            'backup_local_enabled'   => ! empty( $input['backup_local_enabled'] ) ? 1 : 0,
            'backup_google_drive'    => ! empty( $input['backup_google_drive'] ) ? 1 : 0,
            'backup_dropbox'         => ! empty( $input['backup_dropbox'] ) ? 1 : 0,
            'chat_menu_items'        => $menu_items,
        );

        pax_sup_update_options( $new );
        if ( function_exists( 'pax_sup_updater' ) ) {
            pax_sup_updater()->maybe_schedule_checks();
        }
        pax_sup_admin_notice( __( 'Settings saved.', 'pax-support-pro' ) );
    }

    $options = pax_sup_get_options();
    $stored_notice = pax_sup_pull_admin_notice();
    
    // Include modern UI rendering
    require_once plugin_dir_path( __FILE__ ) . 'settings-modern-ui.php';
    pax_sup_render_modern_settings( $options, $stored_notice );
}