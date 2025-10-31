<?php
/**
 * Front-end chat output and assets.
 *
 * @package PAX_Support_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function pax_sup_register_shortcode() {
    add_shortcode(
        'pax_support',
        function () {
            return '<div id="pax-support-root" style="display:contents"></div>';
        }
    );
}
add_action( 'init', 'pax_sup_register_shortcode' );

function pax_sup_enqueue_public_assets() {
    if ( is_admin() ) {
        return;
    }

    $options = pax_sup_get_options();

    if ( empty( $options['enabled'] ) || empty( $options['enable_chat'] ) ) {
        return;
    }

    wp_enqueue_style( 'pax-support-pro', PAX_SUP_URL . 'public/assets.css', array(), PAX_SUP_VER );
    wp_enqueue_script( 'pax-support-pro', PAX_SUP_URL . 'public/assets.js', array(), PAX_SUP_VER, true );

    $position = $options['launcher_position'];
    $reaction_color = ! empty( $options['reaction_btn_color'] ) ? $options['reaction_btn_color'] : '#e53935';
    $reaction_rgb = sscanf( $reaction_color, '#%02x%02x%02x' );
    
    $css      = ':root{' .
        '--pax-bg:' . esc_html( $options['color_bg'] ) . ';' .
        '--pax-panel:' . esc_html( $options['color_panel'] ) . ';' .
        '--pax-border:' . esc_html( $options['color_border'] ) . ';' .
        '--pax-text:' . esc_html( $options['color_text'] ) . ';' .
        '--pax-sub:' . esc_html( $options['color_sub'] ) . ';' .
        '--pax-accent:' . esc_html( $options['color_accent'] ) . ';' .
        '--pax-reaction-bg:rgba(' . implode( ',', $reaction_rgb ) . ',0.9);' .
        '--pax-reaction-bg-hover:' . esc_html( $reaction_color ) . ';' .
        '--pax-reaction-border:rgba(' . implode( ',', $reaction_rgb ) . ',0.5);' .
        '--pax-reaction-border-hover:rgba(' . implode( ',', $reaction_rgb ) . ',0.7);';

    switch ( $position ) {
        case 'bottom-left':
            $css .= '--pax-launcher-left:16px;--pax-launcher-right:auto;--pax-launcher-top:auto;--pax-launcher-bottom:16px;';
            $css .= '--pax-chat-left:14px;--pax-chat-right:auto;--pax-chat-top:auto;--pax-chat-bottom:90px;';
            break;
        case 'top-left':
            $css .= '--pax-launcher-left:16px;--pax-launcher-right:auto;--pax-launcher-top:16px;--pax-launcher-bottom:auto;';
            $css .= '--pax-chat-left:14px;--pax-chat-right:auto;--pax-chat-top:90px;--pax-chat-bottom:auto;';
            break;
        case 'top-right':
            $css .= '--pax-launcher-left:auto;--pax-launcher-right:16px;--pax-launcher-top:16px;--pax-launcher-bottom:auto;';
            $css .= '--pax-chat-left:auto;--pax-chat-right:14px;--pax-chat-top:90px;--pax-chat-bottom:auto;';
            break;
        default:
            $css .= '--pax-launcher-left:auto;--pax-launcher-right:16px;--pax-launcher-top:auto;--pax-launcher-bottom:16px;';
            $css .= '--pax-chat-left:auto;--pax-chat-right:14px;--pax-chat-top:auto;--pax-chat-bottom:90px;';
            break;
    }

    $css .= '}';

    wp_add_inline_style( 'pax-support-pro', $css );

    $current_path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '';
    $login_url    = wp_login_url( home_url( $current_path ?: '/' ) );

    $current_user = wp_get_current_user();
    $scheduler_settings = pax_sup_get_scheduler_settings();

    $menu_items = isset( $options['chat_menu_items'] ) && is_array( $options['chat_menu_items'] )
        ? $options['chat_menu_items']
        : pax_sup_default_menu_items();

    $localize = array(
        'options' => array(
            'enable_speed'         => ! empty( $options['enable_speed'] ),
            'toggle_on_click'      => ! empty( $options['toggle_on_click'] ),
            'enable_offline_guard' => ! empty( $options['enable_offline_guard'] ),
            'allow_guest_chat'     => ! empty( $options['allow_guest_chat'] ),
        ),
        'menuItems' => $menu_items,
        'rest'    => array(
            'chat'     => esc_url_raw( rest_url( PAX_SUP_REST_NS . '/chat' ) ),
            'ai'       => esc_url_raw( rest_url( 'pax-support/v1/ai-chat' ) ),
            'cooldown' => esc_url_raw( rest_url( PAX_SUP_REST_NS . '/ticket-cooldown' ) ),
            'agent'    => esc_url_raw( rest_url( PAX_SUP_REST_NS . '/live-agent' ) ),
            'callback' => esc_url_raw( rest_url( PAX_SUP_REST_NS . '/callback' ) ),
            'schedule' => esc_url_raw( rest_url( PAX_SUP_REST_NS . '/schedule' ) ),
            'scheduleBase' => esc_url_raw( trailingslashit( rest_url( PAX_SUP_REST_NS . '/schedule' ) ) ),
            'ticket'   => esc_url_raw( rest_url( PAX_SUP_REST_NS . '/ticket' ) ),
            'tickets'  => esc_url_raw( rest_url( PAX_SUP_REST_NS . '/tickets' ) ),
            'help'     => esc_url_raw( rest_url( PAX_SUP_REST_NS . '/help-center' ) ),
            'knowledge'=> esc_url_raw( rest_url( PAX_SUP_REST_NS . '/help-center' ) ),
            'trouble'  => esc_url_raw( rest_url( PAX_SUP_REST_NS . '/troubleshooter' ) ),
            'diagnostics' => esc_url_raw( rest_url( PAX_SUP_REST_NS . '/diagnostics' ) ),
            'order'    => esc_url_raw( rest_url( PAX_SUP_REST_NS . '/order-lookup' ) ),
            'my_request' => esc_url_raw( rest_url( PAX_SUP_REST_NS . '/my-request' ) ),
            'feedback' => esc_url_raw( rest_url( PAX_SUP_REST_NS . '/feedback' ) ),
            'donate'   => esc_url_raw( rest_url( PAX_SUP_REST_NS . '/donate' ) ),
        ),
        'links'   => array(
            'help'     => $options['help_center_url'],
            'donate'   => 'https://www.paypal.me/AhmadAlkhalaf29',
            'feedback' => admin_url( 'admin.php?page=pax-support-feedback' ),
        ),
        'strings' => array(
            'welcome' => __( '👋 Welcome! How can I help you today?', 'pax-support-pro' ),
            'loginRequired' => __( 'Please log in to use support tools.', 'pax-support-pro' ),
            'comingSoon' => __( 'Coming soon!', 'pax-support-pro' ),
        ),
        'nonce'  => wp_create_nonce( 'wp_rest' ),
        'isLoggedIn' => is_user_logged_in(),
        'loginUrl'   => esc_url_raw( $login_url ),
        'aiEnabled'  => ! empty( $options['ai_assistant_enabled'] ),
        'locale'     => determine_locale(),
        'siteLocale' => get_locale(),
        'user'       => array(
            'name'  => $current_user instanceof WP_User ? $current_user->display_name : '',
            'email' => $current_user instanceof WP_User ? $current_user->user_email : '',
        ),
        'scheduler'  => array(
            'timezone'      => $scheduler_settings['timezone'],
            'hours'         => array(
                'start' => $scheduler_settings['hours']['start'],
                'end'   => $scheduler_settings['hours']['end'],
            ),
            'slots_per_hour' => (int) $scheduler_settings['slots_per_hour'],
            'reminder_lead'  => (int) $scheduler_settings['reminder_lead'],
        ),
    );

    wp_localize_script( 'pax-support-pro', 'paxSupportPro', $localize );
}
add_action( 'wp_enqueue_scripts', 'pax_sup_enqueue_public_assets' );

function pax_sup_render_frontend_markup() {
    if ( is_admin() ) {
        return;
    }

    $options = pax_sup_get_options();
    if ( empty( $options['enabled'] ) || empty( $options['enable_chat'] ) ) {
        return;
    }

    // prepare default menu items if not set
    $menu_items = isset( $options['chat_menu_items'] ) && is_array( $options['chat_menu_items'] )
        ? $options['chat_menu_items']
        : pax_sup_default_menu_items();
    ?>
    <div id="pax-chat-overlay"></div>

    <div id="pax-launcher" title="<?php echo esc_attr__( 'Support', 'pax-support-pro' ); ?>">
        <?php if ( ! empty( $options['custom_launcher_icon'] ) ) : ?>
            <img src="<?php echo esc_url( $options['custom_launcher_icon'] ); ?>" alt="<?php esc_attr_e( 'Support', 'pax-support-pro' ); ?>" style="width: 100%; height: 100%; object-fit: contain;">
        <?php else : ?>
            <svg viewBox="0 0 24 24"><path d="M4 3h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-6.6l-4.2 3.5a1 1 0 0 1-1.6-.8V16H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/></svg>
        <?php endif; ?>
    </div>

    <div id="pax-chat" role="dialog" aria-modal="true" class="modal-mode">
        <div class="pax-header" id="pax-head">
            <span class="pax-led"></span>
            <div>
                <div class="pax-title"><?php echo esc_html( $options['brand_name'] ); ?></div>
                <div class="pax-sub"><?php esc_html_e( 'Assistant', 'pax-support-pro' ); ?></div>
            </div>
            <span class="pax-offline" id="pax-offline"><?php esc_html_e( 'Offline', 'pax-support-pro' ); ?></span>
            <div class="pax-spacer"></div>
            <button class="pax-iconbtn" id="pax-head-more" type="button"><svg viewBox="0 0 24 24"><path d="M12 7a2 2 0 110-4 2 2 0 010 4zm0 7a2 2 0 110-4 2 2 0 010 4zm0 7a2 2 0 110-4 2 2 0 010 4z"/></svg></button>
            <button class="pax-iconbtn" id="pax-close" type="button"><svg viewBox="0 0 24 24"><path d="M6.7 5.3 5.3 6.7 10.6 12l-5.3 5.3 1.4 1.4L12 13.4l5.3 5.3 1.4-1.4L13.4 12l5.3-5.3-1.4-1.4L12 10.6z"/></svg></button>

            <div id="pax-head-menu" role="menu">
                <?php
                $menu_icons = array(
                    'chat' => '<path d="M4 3h16a2 2 0 0 1 2 2v9H4z"/>',
                    'ticket' => '<path d="M3 7a2 2 0 0 1 2-2h14v14H5z"/>',
                    'help' => '<path d="M12 2a10 10 0 1 0 10 10z"/>',
                    'speed' => '<path d="M3 13a9 9 0 1 1 18 0z"/>',
                    'agent' => '<path d="M12 12a5 5 0 1 0-5-5z"/>',
                    'callback' => '<path d="M6 2h4l2 6-3 2z"/>',
                    'order' => '<path d="M6 2h9l5 5v15H6z"/>',
                    'myreq' => '<path d="M4 4h16v16H4z"/>',
                    'feedback' => '<path d="M12 2a10 10 0 1 0 10 10z"/>',
                    'donate' => '<path d="M12 21.35 10.55 20C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 18 8.5z"/>',
                );

                if ( empty( $menu_items ) || ! is_array( $menu_items ) ) {
                    $menu_items = pax_sup_default_menu_items();
                }

                foreach ( $menu_items as $key => $item ) :
                    if ( empty( $item['visible'] ) ) {
                        continue;
                    }
                    $label = isset( $item['label'] ) ? $item['label'] : ucfirst( $key );
                    $icon = isset( $menu_icons[ $key ] ) ? $menu_icons[ $key ] : '<path d="M12 2a10 10 0 1 0 10 10z"/>';
                ?>
                <div class="pax-item" data-act="<?php echo esc_attr( $key ); ?>">
                    <svg viewBox="0 0 24 24"><?php echo $icon; ?></svg>
                    <span><?php echo esc_html( $label ); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="pax-log" class="pax-log"></div>
        <div id="pax-input" class="pax-input">
            <input id="pax-in" type="text" placeholder="<?php esc_attr_e( 'Type your message...', 'pax-support-pro' ); ?>">
            <button id="pax-send" type="button">
                <?php if ( ! empty( $options['custom_send_icon'] ) ) : ?>
                    <img src="<?php echo esc_url( $options['custom_send_icon'] ); ?>" alt="Send" style="width: 18px; height: 18px; object-fit: contain;">
                <?php else : ?>
                    <svg viewBox="0 0 24 24"><path d="M2.01 21 23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                <?php endif; ?>
            </button>
        </div>
    </div>
    <?php
}
add_action( 'wp_footer', 'pax_sup_render_frontend_markup' );
