<?php
/**
 * Plugin Name: PAX Support Pro
 * Plugin URI: https://github.com/Black10998/Black10998
 * Description: Professional support ticket system with modern admin UI, real-time chat, AJAX-powered scheduler, and comprehensive callback management. Features include live chat reactions, inline editing, drag & drop, advanced analytics, and auto-update system.
 * Version: 4.0.4
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Author: Ahmad AlKhalaf
 * Author URI: https://github.com/Black10998
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: https://github.com/Black10998/Black10998
 * Text Domain: pax-support-pro
 * Domain Path: /languages
 * 
 * @package PAX_Support_Pro
 * @version 4.0.4
 * @since 1.0.0
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
    define( 'PAX_SUP_VER', '4.0.4' );
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

/**
 * Force-load the latest JavaScript version to bypass cache
 */
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script(
        'pax-support-pro',
        plugin_dir_url(__FILE__) . 'public/assets.js',
        [],
        time(), // ← يجبر المتصفح على تحميل النسخة الأحدث
        true
    );
});
