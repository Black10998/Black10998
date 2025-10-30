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
    $css      = ':root{' .
        '--pax-bg:' . esc_html( $options['color_bg'] ) . ';' .
        '--pax-panel:' . esc_html( $options['color_panel'] ) . ';' .
        '--pax-border:' . esc_html( $options['color_border'] ) . ';' .
        '--pax-text:' . esc_html( $options['color_text'] ) . ';' .
        '--pax-sub:' . esc_html( $options['color_sub'] ) . ';' .
        '--pax-accent:' . esc_html( $options['color_accent'] ) . ';';

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
            'welcome'          => __( '👋 Welcome! How can I help you today?', 'pax-support-pro' ),
            'networkError'     => __( 'Network error.', 'pax-support-pro' ),
            'noResponse'       => __( 'No response.', 'pax-support-pro' ),
            'aiThinking'       => __( 'Assistant is thinking…', 'pax-support-pro' ),
            'aiResponding'     => __( 'Assistant is responding.', 'pax-support-pro' ),
            'aiOffline'        => __( 'AI assistant is offline.', 'pax-support-pro' ),
            'aiSuggestions'    => __( 'Suggested knowledge base articles', 'pax-support-pro' ),
            'speed'            => __( 'Super Speed', 'pax-support-pro' ),
            'speedOn'          => __( 'Super Speed ON', 'pax-support-pro' ),
            'cooldown'         => __( 'Cooldown', 'pax-support-pro' ),
            'agentTitle'       => __( 'Live Agent', 'pax-support-pro' ),
            'agentName'        => __( 'Your name *', 'pax-support-pro' ),
            'agentEmail'       => __( 'Your email *', 'pax-support-pro' ),
            'agentIssue'       => __( 'Briefly describe your issue *', 'pax-support-pro' ),
            'agentSubmit'      => __( 'Send', 'pax-support-pro' ),
            'agentSuccess'     => __( 'Sent to live agent', 'pax-support-pro' ),
            'agentError'       => __( 'Failed: ', 'pax-support-pro' ),
            'callbackTitle'    => __( 'Request a Callback', 'pax-support-pro' ),
            'callbackName'     => __( 'Your name *', 'pax-support-pro' ),
            'callbackPhone'    => __( 'Phone/WhatsApp *', 'pax-support-pro' ),
            'callbackNote'     => __( 'Note (optional)', 'pax-support-pro' ),
            'callbackSubmit'   => __( 'Request', 'pax-support-pro' ),
            'callbackSuccess'  => __( 'Callback requested', 'pax-support-pro' ),
            'callbackError'    => __( 'Failed: ', 'pax-support-pro' ),
            'callbackRequired' => __( 'Name & phone required.', 'pax-support-pro' ),
            'scheduleTitle'    => __( 'Schedule a Callback', 'pax-support-pro' ),
            'scheduleDate'     => __( 'Preferred date *', 'pax-support-pro' ),
            'scheduleTime'     => __( 'Preferred time *', 'pax-support-pro' ),
            'scheduleNote'     => __( 'Add a short note (optional)', 'pax-support-pro' ),
            'scheduleTimezone' => __( 'Detected timezone: %s', 'pax-support-pro' ),
            'scheduleWorkingHours' => __( 'Available hours: %1$s – %2$s', 'pax-support-pro' ),
            'scheduleSubmit'   => __( 'Book callback', 'pax-support-pro' ),
            'scheduleSuccess'  => __( 'Callback booked successfully.', 'pax-support-pro' ),
            'scheduleError'    => __( 'Unable to schedule callback.', 'pax-support-pro' ),
            'scheduleListTitle'=> __( 'Your scheduled callbacks', 'pax-support-pro' ),
            'scheduleListEmpty'=> __( 'No callbacks scheduled yet.', 'pax-support-pro' ),
            'scheduleCancel'   => __( 'Cancel', 'pax-support-pro' ),
            'scheduleCancelConfirm' => __( 'Cancel this callback?', 'pax-support-pro' ),
            'scheduleCancelSuccess' => __( 'Callback canceled.', 'pax-support-pro' ),
            'fillAll'          => __( 'Fill all fields.', 'pax-support-pro' ),
            'sending'          => __( 'Sending…', 'pax-support-pro' ),
            'confirmTitle'    => __( 'Please confirm', 'pax-support-pro' ),
            'cancel'           => __( 'Cancel', 'pax-support-pro' ),
            'close'            => __( 'Close', 'pax-support-pro' ),
            'loading'          => __( 'Loading…', 'pax-support-pro' ),
            'ticketTitle'      => __( 'Create Support Ticket', 'pax-support-pro' ),
            'ticketName'       => __( 'Your name *', 'pax-support-pro' ),
            'ticketEmail'      => __( 'Your email *', 'pax-support-pro' ),
            'ticketSubject'    => __( 'Subject *', 'pax-support-pro' ),
            'ticketMessage'    => __( 'Describe your request *', 'pax-support-pro' ),
            'ticketSubmit'     => __( 'Submit Ticket', 'pax-support-pro' ),
            'ticketSuccess'    => __( 'Ticket submitted successfully.', 'pax-support-pro' ),
            'ticketError'      => __( 'Unable to submit ticket.', 'pax-support-pro' ),
            'ticketDetailTitle'=> __( 'Ticket details', 'pax-support-pro' ),
            'ticketStatusLabel' => __( 'Status', 'pax-support-pro' ),
            'ticketUpdatedLabel'=> __( 'Updated', 'pax-support-pro' ),
            'ticketNoMessages'  => __( 'No messages yet.', 'pax-support-pro' ),
            'ticketDeleteConfirm' => __( 'Delete this ticket? This action cannot be undone.', 'pax-support-pro' ),
            'ticketDeleteSuccess' => __( 'Ticket deleted successfully.', 'pax-support-pro' ),
            'ticketDeleteError'   => __( 'Unable to delete ticket.', 'pax-support-pro' ),
            'ticketCooldownActive'=> __( 'Cooldown active. Please wait before submitting another ticket.', 'pax-support-pro' ),
            'ticketCooldownNotice'=> __( 'You can create a new ticket once the cooldown expires.', 'pax-support-pro' ),
            'ticketViewButton'    => __( 'View', 'pax-support-pro' ),
            'ticketDeleteButton'  => __( 'Delete', 'pax-support-pro' ),
            'ticketEmptyList'     => __( 'No tickets yet.', 'pax-support-pro' ),
            'ticketViewError'     => __( 'Unable to load ticket details.', 'pax-support-pro' ),
            'helpTitle'        => __( 'Help Center', 'pax-support-pro' ),
            'helpEmpty'        => __( 'No help articles were found.', 'pax-support-pro' ),
            'kbTitle'          => __( 'Knowledge Base', 'pax-support-pro' ),
            'kbSearchPlaceholder' => __( 'Search articles…', 'pax-support-pro' ),
            'kbEmpty'          => __( 'No matching knowledge base articles.', 'pax-support-pro' ),
            'kbLoading'        => __( 'Searching knowledge base…', 'pax-support-pro' ),
            'kbView'           => __( 'Open article', 'pax-support-pro' ),
            'troubleTitle'     => __( 'Troubleshooter', 'pax-support-pro' ),
            'troubleSelect'    => __( 'Select an issue *', 'pax-support-pro' ),
            'troublePerformance' => __( 'Performance issues', 'pax-support-pro' ),
            'troubleErrors'    => __( 'Errors & crashes', 'pax-support-pro' ),
            'troubleBilling'   => __( 'Billing & payments', 'pax-support-pro' ),
            'troubleOther'     => __( 'Other', 'pax-support-pro' ),
            'troublePrompt'    => __( 'Describe the issue you are facing *', 'pax-support-pro' ),
            'troubleSubmit'    => __( 'Run Troubleshooter', 'pax-support-pro' ),
            'troubleError'     => __( 'No steps available.', 'pax-support-pro' ),
            'diagTitle'        => __( 'Diagnostics', 'pax-support-pro' ),
            'orderTitle'       => __( 'Order Lookup', 'pax-support-pro' ),
            'orderId'          => __( 'Order ID *', 'pax-support-pro' ),
            'orderEmail'       => __( 'Billing email *', 'pax-support-pro' ),
            'orderSubmit'      => __( 'Check Status', 'pax-support-pro' ),
            'orderMissing'     => __( 'Order not found. Please verify the details.', 'pax-support-pro' ),
            'myRequestTitle'   => __( 'My Requests', 'pax-support-pro' ),
            'myRequestEmpty'   => __( 'No recent support requests found.', 'pax-support-pro' ),
            'feedbackTitle'    => __( 'Send Feedback', 'pax-support-pro' ),
            'feedbackPlaceholder' => __( 'Share your feedback or suggestions *', 'pax-support-pro' ),
            'feedbackSubmit'   => __( 'Send Feedback', 'pax-support-pro' ),
            'feedbackThanks'   => __( 'Thank you for your feedback!', 'pax-support-pro' ),
            'feedbackError'    => __( 'Unable to send feedback.', 'pax-support-pro' ),
            'donateTitle'      => __( 'Support the Developer', 'pax-support-pro' ),
            'donateDescription'=> __( 'Thank you for considering a donation. Your support keeps the project alive!', 'pax-support-pro' ),
            'donateButton'     => __( 'Open Donation Page', 'pax-support-pro' ),
            'donateThanks'     => __( 'Donation link opened in a new tab.', 'pax-support-pro' ),
            'loginRequired'    => __( 'Please log in to use support tools.', 'pax-support-pro' ),
            'comingSoon'       => __( 'Coming soon!', 'pax-support-pro' ),
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
    ?>
    <div id="pax-chat-overlay"></div>
    
    <div id="pax-launcher" title="<?php echo esc_attr__( 'Support', 'pax-support-pro' ); ?>" aria-label="<?php echo esc_attr__( 'Open support', 'pax-support-pro' ); ?>">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 3h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-6.6l-4.2 3.5a1 1 0 0 1-1.6-.8V16H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/></svg>
    </div>

    <div id="pax-chat" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr__( 'PAX Support Chat', 'pax-support-pro' ); ?>" class="modal-mode">
        <div class="pax-header" id="pax-head">
            <span class="pax-led"></span>
            <div>
                <div class="pax-title"><?php echo esc_html( $options['brand_name'] ); ?></div>
                <div class="pax-sub"><?php esc_html_e( 'Assistant', 'pax-support-pro' ); ?></div>
            </div>
            <span class="pax-offline" id="pax-offline"><?php esc_html_e( 'Offline', 'pax-support-pro' ); ?></span>
            <div class="pax-spacer"></div>
            <button class="pax-iconbtn" id="pax-head-more" type="button" aria-label="<?php echo esc_attr__( 'Menu', 'pax-support-pro' ); ?>"><svg viewBox="0 0 24 24"><path d="M12 7a2 2 0 110-4 2 2 0 010 4zm0 7a2 2 0 110-4 2 2 0 010 4zm0 7a2 2 0 110-4 2 2 0 010 4z"/></svg></button>
            <button class="pax-iconbtn" id="pax-close" type="button" aria-label="<?php echo esc_attr__( 'Close', 'pax-support-pro' ); ?>"><svg viewBox="0 0 24 24"><path d="M6.7 5.3 5.3 6.7 10.6 12l-5.3 5.3 1.4 1.4L12 13.4l5.3 5.3 1.4-1.4L13.4 12l5.3-5.3-1.4-1.4L12 10.6z"/></svg></button>

            <div id="pax-head-menu" role="menu" aria-label="<?php echo esc_attr__( 'Chat menu', 'pax-support-pro' ); ?>">
                <?php
                $menu_icons = array(
                    'chat'          => '<path d="M4 3h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-6.6l-4.2 3.5a1 1 0 0 1-1.6-.8V16H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/>',
                    'ticket'        => '<path d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v3a2 2 0 1 0 0 4v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-3a2 2 0 1 0 0-4V7z"/>',
                    'help'          => '<path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm1 15h-2v-2h2Zm2.07-7.75-.9.92A3.49 3.49 0 0 0 13 12h-2v-.5a4.5 4.5 0 0 1 1.33-3.17l1.24-1.26a1.5 1.5 0 1 0-2.12-2.12A4.49 4.49 0 0 0 9 7.5H7a6.5 6.5 0 1 1 11.07 4.61A6.48 6.48 0 0 1 15 14v-1a3.49 3.49 0 0 0 .93-2.33 3.47 3.47 0 0 0-.86-2.42Z"/>',
                    'speed'         => '<path d="M3 13a9 9 0 1 1 18 0 9 9 0 0 1-18 0Zm9-6a1 1 0 0 0-1 1v4.59l-2.3 2.3 1.4 1.42 2.6-2.6V8a1 1 0 0 0-1-1Z"/>',
                    'agent'         => '<path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 2.5-9 5.5V22h18v-2.5C21 16.5 17 14 12 14Z"/>',
                    'whatsnew'      => '<path d="M4 4h16v4H4zM4 10h10v4H4zM4 16h16v4H4z"/>',
                    'troubleshooter'=> '<path d="M4 4h16v4H4zM4 10h12v10H4zM18 10h2v10h-2z"/>',
                    'diag'          => '<path d="M3 13h4l3 7 4-14 3 7h4"/>',
                    'callback'      => '<path d="M6 2h4l2 6-3 2a13 13 0 0 0 6 6l2-3 6 2v4a2 2 0 0 1-2 2A18 18 0 0 1 2 8a2 2 0 0 1 2-2h2z"/>',
                    'order'         => '<path d="M6 2h9l5 5v15H6zM8 8h8M8 12h8M8 16h8"/>',
                    'myreq'         => '<path d="M4 4h16v16H4zM8 8h8v8H8z"/>',
                    'feedback'      => '<path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm1 15h-2v-2h2Zm0-4h-2V7h2z"/>',
                    'donate'        => '<path d="M12 21.35 10.55 20C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 0 1 6.5 4a3.7 3.7 0 0 1 3.5 2.44A3.7 3.7 0 0 1 13.5 4 4.5 4.5 0 0 1 18 8.5c0 3.78-3.4 6.86-8.55 11.54Z"/>',
                );

                foreach ( $menu_items as $key => $item ) :
                    if ( empty( $item['visible'] ) ) {
                        continue;
                    }
                    $label = isset( $item['label'] ) ? $item['label'] : $key;
                    $icon = isset( $menu_icons[ $key ] ) ? $menu_icons[ $key ] : '<path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Z"/>';
                    $extra_id = ( 'speed' === $key ) ? ' id="pax-speed-label"' : '';
                    $badge = ( 'ticket' === $key ) ? '<span class="pax-badge" id="pax-ticket-cd" style="display:none"></span>' : '';
                ?>
                <div class="pax-item" data-act="<?php echo esc_attr( $key ); ?>">
                    <svg viewBox="0 0 24 24"><?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></svg>
                    <span<?php echo $extra_id; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $label ); ?></span>
                    <?php echo $badge; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
                <?php endforeach; ?>
            </div>
            </div>
        </div>

        <div id="pax-log" class="pax-log" aria-live="polite"></div>
        <div id="pax-suggestions" class="pax-suggestions" aria-live="polite"></div>

        <div id="pax-attachment-preview" class="pax-attachment-preview" style="display:none;"></div>
        
        <div class="pax-input">
            <button id="pax-attach" class="pax-attach" type="button" aria-label="<?php echo esc_attr__( 'Attach file', 'pax-support-pro' ); ?>" title="<?php echo esc_attr__( 'Attach file', 'pax-support-pro' ); ?>">
                <svg viewBox="0 0 24 24"><path d="M16.5 6v11.5c0 2.21-1.79 4-4 4s-4-1.79-4-4V5a2.5 2.5 0 0 1 5 0v10.5c0 .55-.45 1-1 1s-1-.45-1-1V6H10v9.5a2.5 2.5 0 0 0 5 0V5c0-2.21-1.79-4-4-4S7 2.79 7 5v12.5c0 3.04 2.46 5.5 5.5 5.5s5.5-2.46 5.5-5.5V6h-1.5z"/></svg>
            </button>
            <input id="pax-file-input" type="file" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip" style="display:none;" aria-label="<?php echo esc_attr__( 'Select file', 'pax-support-pro' ); ?>">
            <input id="pax-in" type="text" placeholder="<?php echo esc_attr__( 'Type your message...', 'pax-support-pro' ); ?>" maxlength="1200" aria-label="<?php echo esc_attr__( 'Message', 'pax-support-pro' ); ?>">
            <button id="pax-send" class="pax-send" type="button" aria-label="<?php echo esc_attr__( 'Send', 'pax-support-pro' ); ?>"><svg viewBox="0 0 24 24"><path d="M2.01 21 23 12 2.01 3 2 10l15 2-15 2z"/></svg></button>
        </div>
        
        <div id="pax-drop-zone" class="pax-drop-zone" style="display:none;">
            <div class="pax-drop-zone-content">
                <svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                <p><?php esc_html_e( 'Drop file here to attach', 'pax-support-pro' ); ?></p>
            </div>
        </div>
    </div>
    <?php
}
add_action( 'wp_footer', 'pax_sup_render_frontend_markup' );