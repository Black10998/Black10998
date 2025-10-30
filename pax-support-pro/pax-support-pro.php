<?php
/**
 * Plugin Name: PAX Support Pro
 * Description: Unified chat/tickets UI + console + settings. Clean, secure UX.
 * Version: 1.1.2
 * Author: Ahmad AlKhalaf
 * Author URI: https://github.com/Black10998
 * Update URI: https://github.com/Black10998/Black10998
 * Text Domain: pax-support-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'PAX_SUP_FILE' ) ) {
    define( 'PAX_SUP_FILE', __FILE__ );
}

if ( ! defined( 'PAX_SUP_DIR' ) ) {
    define( 'PAX_SUP_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'PAX_SUP_URL' ) ) {
    define( 'PAX_SUP_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'PAX_SUP_NS' ) ) {
    define( 'PAX_SUP_NS', 'pax_support' );
}

if ( ! defined( 'PAX_SUP_VER' ) ) {
    define( 'PAX_SUP_VER', '1.1.2' );
}

if ( ! defined( 'PAX_SUP_OPT_KEY' ) ) {
    define( 'PAX_SUP_OPT_KEY', 'pax_support_options_v2' );
}

if ( ! defined( 'PAX_SUP_REST_NS' ) ) {
    define( 'PAX_SUP_REST_NS', 'pax/v1' );
}

require_once PAX_SUP_DIR . 'includes/helpers.php';
require_once PAX_SUP_DIR . 'includes/attachments.php';
require_once PAX_SUP_DIR . 'includes/install.php';
require_once PAX_SUP_DIR . 'includes/quickpanel.php';
require_once PAX_SUP_DIR . 'includes/updater.php';
require_once PAX_SUP_DIR . 'admin/settings.php';
require_once PAX_SUP_DIR . 'admin/console.php';
require_once PAX_SUP_DIR . 'admin/tickets.php';
require_once PAX_SUP_DIR . 'admin/scheduler.php';
require_once PAX_SUP_DIR . 'admin/dashboard-analytics-ui.php';

// Load test page only in development
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    require_once PAX_SUP_DIR . 'admin/test-update-modals.php';
}
require_once PAX_SUP_DIR . 'public/chat.php';
require_once PAX_SUP_DIR . 'rest/chat.php';
require_once PAX_SUP_DIR . 'rest/ticket.php';
require_once PAX_SUP_DIR . 'rest/agent.php';
require_once PAX_SUP_DIR . 'rest/callback.php';
require_once PAX_SUP_DIR . 'rest/support.php';
require_once PAX_SUP_DIR . 'rest/scheduler.php';
require_once PAX_SUP_DIR . 'rest/attachment.php';
require_once PAX_SUP_DIR . 'rest/system-health.php';