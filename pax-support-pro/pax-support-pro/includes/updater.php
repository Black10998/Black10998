<?php
/**
 * GitHub updater and backup integration.
 *
 * @package PAX_Support_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'PAX_SUP_UPDATE_PUBLIC_KEY' ) ) {
    define( 'PAX_SUP_UPDATE_PUBLIC_KEY', '' );
}

class PAX_Support_Pro_Updater {

    private static $instance = null;

    private $plugin_basename;

    private $release_meta = null;

    private $last_backup_path = '';

    private $github_repo = 'Black10998/Black10998';
    
    private $cache_dir = '';

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct() {
        $this->plugin_basename = plugin_basename( PAX_SUP_FILE );
        $this->cache_dir = PAX_SUP_DIR . 'CheckOptData';
        
        // Ensure cache directory exists
        $this->ensure_cache_directory();

        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'filter_plugin_updates' ) );
        add_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 3 );
        add_action( 'admin_init', array( $this, 'maybe_schedule_checks' ) );
        add_action( 'pax_sup_check_updates', array( $this, 'cron_check_updates' ) );
        add_filter( 'upgrader_pre_download', array( $this, 'before_download' ), 10, 4 );
        add_filter( 'upgrader_install_package_result', array( $this, 'maybe_restore_on_failure' ), 10, 2 );
        add_filter( 'upgrader_post_install', array( $this, 'cleanup_after_install' ), 10, 3 );
        add_action( 'upgrader_process_complete', array( $this, 'after_update_complete' ), 10, 2 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_update_modal_assets' ) );
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
    }
    
    /**
     * Ensure cache directory exists with proper permissions
     */
    private function ensure_cache_directory() {
        if ( ! file_exists( $this->cache_dir ) ) {
            wp_mkdir_p( $this->cache_dir );
            
            // Add .htaccess to protect directory
            $htaccess_file = $this->cache_dir . '/.htaccess';
            if ( ! file_exists( $htaccess_file ) ) {
                file_put_contents( $htaccess_file, "Deny from all\n" );
            }
            
            // Add index.php to prevent directory listing
            $index_file = $this->cache_dir . '/index.php';
            if ( ! file_exists( $index_file ) ) {
                file_put_contents( $index_file, "<?php\n// Silence is golden.\n" );
            }
        }
        
        // Ensure directory is writable
        if ( file_exists( $this->cache_dir ) && ! is_writable( $this->cache_dir ) ) {
            @chmod( $this->cache_dir, 0755 );
        }
    }
    
    /**
     * Get cache file path
     */
    private function get_cache_file( $name = 'status.json' ) {
        return $this->cache_dir . '/' . $name;
    }
    
    /**
     * Save update status to cache file
     */
    private function save_update_cache( $data ) {
        $cache_file = $this->get_cache_file();
        $data['cached_at'] = time();
        file_put_contents( $cache_file, json_encode( $data, JSON_PRETTY_PRINT ) );
    }
    
    /**
     * Load update status from cache file
     */
    private function load_update_cache() {
        $cache_file = $this->get_cache_file();
        if ( file_exists( $cache_file ) ) {
            $content = file_get_contents( $cache_file );
            $data = json_decode( $content, true );
            
            // Check if cache is still valid (6 hours)
            if ( isset( $data['cached_at'] ) && ( time() - $data['cached_at'] ) < 6 * HOUR_IN_SECONDS ) {
                return $data;
            }
        }
        return null;
    }

    /**
     * Register REST API routes for manual update checks
     */
    public function register_rest_routes() {
        register_rest_route(
            PAX_SUP_REST_NS,
            '/check-updates',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'rest_check_updates' ),
                'permission_callback' => function() {
                    return current_user_can( 'update_plugins' );
                },
            )
        );
        
        register_rest_route(
            PAX_SUP_REST_NS,
            '/update-diagnostics',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'rest_update_diagnostics' ),
                'permission_callback' => function() {
                    return current_user_can( 'update_plugins' );
                },
            )
        );
    }
    
    /**
     * REST endpoint for update system diagnostics
     */
    public function rest_update_diagnostics( $request ) {
        $options = pax_sup_get_options();
        $cache_file = $this->get_cache_file();
        
        $diagnostics = array(
            'cache_directory' => array(
                'path'     => $this->cache_dir,
                'exists'   => file_exists( $this->cache_dir ),
                'writable' => is_writable( $this->cache_dir ),
                'permissions' => file_exists( $this->cache_dir ) ? substr( sprintf( '%o', fileperms( $this->cache_dir ) ), -4 ) : 'N/A',
            ),
            'cache_file' => array(
                'path'     => $cache_file,
                'exists'   => file_exists( $cache_file ),
                'size'     => file_exists( $cache_file ) ? filesize( $cache_file ) : 0,
                'modified' => file_exists( $cache_file ) ? date( 'Y-m-d H:i:s', filemtime( $cache_file ) ) : 'N/A',
            ),
            'settings' => array(
                'auto_update_enabled' => ! empty( $options['auto_update_enabled'] ),
                'update_check_frequency' => $options['update_check_frequency'] ?? 'daily',
            ),
            'scheduled_checks' => array(
                'next_run' => wp_next_scheduled( 'pax_sup_check_updates' ) ? date( 'Y-m-d H:i:s', wp_next_scheduled( 'pax_sup_check_updates' ) ) : 'Not scheduled',
            ),
            'github_connection' => array(
                'repo' => $this->github_repo,
                'api_url' => 'https://api.github.com/repos/' . $this->github_repo . '/releases/latest',
            ),
            'current_version' => PAX_SUP_VER,
        );
        
        // Test GitHub connection
        $test_request = wp_remote_get(
            'https://api.github.com/repos/' . $this->github_repo . '/releases/latest',
            array(
                'timeout' => 10,
                'headers' => array(
                    'Accept'     => 'application/vnd.github+json',
                    'User-Agent' => 'PAX-Support-Pro/' . PAX_SUP_VER,
                ),
            )
        );
        
        if ( is_wp_error( $test_request ) ) {
            $diagnostics['github_connection']['status'] = 'error';
            $diagnostics['github_connection']['error'] = $test_request->get_error_message();
        } else {
            $diagnostics['github_connection']['status'] = 'success';
            $diagnostics['github_connection']['response_code'] = wp_remote_retrieve_response_code( $test_request );
        }
        
        return rest_ensure_response( array(
            'success' => true,
            'diagnostics' => $diagnostics,
        ) );
    }

    /**
     * REST endpoint to manually check for updates
     */
    public function rest_check_updates( $request ) {
        $release = $this->force_check_updates();
        
        if ( empty( $release['version'] ) ) {
            return new WP_Error(
                'no_update_info',
                __( 'Unable to retrieve update information from GitHub.', 'pax-support-pro' ),
                array( 'status' => 500 )
            );
        }

        $has_update = version_compare( PAX_SUP_VER, $release['version'], '<' );

        return rest_ensure_response( array(
            'success'        => true,
            'current_version' => PAX_SUP_VER,
            'latest_version'  => $release['version'],
            'has_update'      => $has_update,
            'release_url'     => $release['url'],
            'changelog'       => ! empty( $release['body'] ) ? $release['body'] : '',
            'message'         => $has_update 
                ? sprintf( 
                    __( 'Update available: %s → %s', 'pax-support-pro' ),
                    PAX_SUP_VER,
                    $release['version']
                )
                : __( 'You are running the latest version.', 'pax-support-pro' ),
        ) );
    }

    public function maybe_schedule_checks() {
        $options = pax_sup_get_options();

        if ( empty( $options['auto_update_enabled'] ) ) {
            if ( wp_next_scheduled( 'pax_sup_check_updates' ) ) {
                wp_clear_scheduled_hook( 'pax_sup_check_updates' );
            }

            return;
        }

        $frequency = 'daily';
        if ( isset( $options['update_check_frequency'] ) && in_array( $options['update_check_frequency'], array( 'daily', 'weekly' ), true ) ) {
            $frequency = $options['update_check_frequency'];
        }

        $hook       = 'pax_sup_check_updates';
        $recurrence = 'daily' === $frequency ? 'daily' : 'pax_sup_weekly';
        $schedule   = wp_get_schedule( $hook );

        if ( $schedule && $schedule !== $recurrence ) {
            wp_clear_scheduled_hook( $hook );
            $schedule = false;
        }

        if ( ! $schedule ) {
            wp_schedule_event( time() + HOUR_IN_SECONDS, $recurrence, $hook );
        }
    }

    public function cron_check_updates() {
        delete_site_transient( 'update_plugins' );
        require_once ABSPATH . 'wp-admin/includes/update.php';
        wp_update_plugins();
    }

    /**
     * Force check for updates (clears cache)
     */
    public function force_check_updates() {
        delete_site_transient( 'pax_sup_release_meta' );
        delete_site_transient( 'update_plugins' );
        $this->release_meta = null;
        
        // Clear file cache
        $cache_file = $this->get_cache_file();
        if ( file_exists( $cache_file ) ) {
            @unlink( $cache_file );
        }
        
        // Trigger update check
        require_once ABSPATH . 'wp-admin/includes/update.php';
        wp_update_plugins();
        
        return $this->get_release_meta();
    }

    public function filter_plugin_updates( $transient ) {
        if ( empty( $transient ) || ! is_object( $transient ) ) {
            $transient = new stdClass();
        }

        $release = $this->get_release_meta();

        if ( empty( $release['version'] ) || version_compare( PAX_SUP_VER, $release['version'], '>=' ) ) {
            return $transient;
        }

        $plugin_data = array(
            'id'          => 'pax-support-pro/pax-support-pro.php',
            'slug'        => dirname( $this->plugin_basename ),
            'plugin'      => $this->plugin_basename,
            'new_version' => $release['version'],
            'url'         => $release['url'],
            'package'     => $release['download_url'],
            'icons'       => array(),
            'banners'     => array(),
            'banners_rtl' => array(),
            'tested'      => get_bloginfo( 'version' ),
            'requires_php' => '7.4',
            'compatibility' => new stdClass(),
        );

        if ( empty( $transient->response ) ) {
            $transient->response = array();
        }

        $transient->response[ $this->plugin_basename ] = (object) $plugin_data;
        
        // Also set last_checked to ensure WordPress recognizes this as fresh data
        $transient->last_checked = time();

        return $transient;
    }

    public function plugins_api( $response, $action, $args ) {
        if ( 'plugin_information' !== $action || empty( $args->slug ) || dirname( $this->plugin_basename ) !== $args->slug ) {
            return $response;
        }

        $release = $this->get_release_meta();

        if ( empty( $release ) ) {
            return $response;
        }

        $info = new stdClass();
        $info->name          = 'PAX Support Pro';
        $info->slug          = dirname( $this->plugin_basename );
        $info->version       = $release['version'];
        $info->author        = '<a href="https://github.com/' . $this->github_repo . '">PAX Support</a>';
        $info->homepage      = 'https://github.com/' . $this->github_repo;
        $info->download_link = $release['download_url'];
        $info->requires      = '5.8';
        $info->tested        = get_bloginfo( 'version' );
        $info->requires_php  = '7.4';
        $info->last_updated  = ! empty( $release['published_at'] ) ? $release['published_at'] : current_time( 'mysql' );
        $info->sections      = array(
            'description' => ! empty( $release['description'] ) ? wp_kses_post( $release['description'] ) : __( 'Latest release details are available on GitHub.', 'pax-support-pro' ),
            'changelog'   => ! empty( $release['changelog'] ) ? wp_kses_post( $release['changelog'] ) : __( 'No changelog provided.', 'pax-support-pro' ),
        );
        $info->banners       = array();
        $info->icons         = array();

        return $info;
    }

    public function before_download( $reply, $package, $upgrader, $hook_extra ) {
        if ( empty( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_basename ) {
            return $reply;
        }

        $release = $this->get_release_meta();

        if ( empty( $release['download_url'] ) ) {
            return new WP_Error( 'pax_sup_no_package', __( 'Unable to locate update package.', 'pax-support-pro' ) );
        }

        $backup = pax_sup_run_backup( 'pre-update' );
        if ( ! is_wp_error( $backup ) ) {
            $this->last_backup_path = $backup;
            update_option( 'pax_sup_last_backup', $backup, false );
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        $package_path = download_url( $release['download_url'] );

        if ( is_wp_error( $package_path ) ) {
            return $package_path;
        }

        $signature_content = '';
        if ( ! empty( $release['signature_url'] ) ) {
            $sig_request = wp_remote_get( $release['signature_url'], array( 'timeout' => 20 ) );
            if ( ! is_wp_error( $sig_request ) && 200 === wp_remote_retrieve_response_code( $sig_request ) ) {
                $signature_content = wp_remote_retrieve_body( $sig_request );
            }
        }

        if ( $signature_content && PAX_SUP_UPDATE_PUBLIC_KEY && function_exists( 'openssl_verify' ) ) {
            $valid = $this->verify_signature( $package_path, $signature_content );
            if ( is_wp_error( $valid ) ) {
                @unlink( $package_path );

                return $valid;
            }
        }

        return $package_path;
    }

    public function maybe_restore_on_failure( $result, $hook_extra ) {
        if ( empty( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_basename ) {
            return $result;
        }

        if ( is_wp_error( $result ) && $this->last_backup_path ) {
            pax_sup_restore_backup( $this->last_backup_path );
            
            // Set transient for error modal
            set_transient(
                'pax_sup_update_failed',
                array(
                    'error'     => $result->get_error_message(),
                    'timestamp' => time()
                ),
                300 // 5 minutes
            );
            
            // Add redirect parameter
            add_filter( 'wp_redirect', array( $this, 'add_update_failure_param' ) );
        }

        return $result;
    }

    /**
     * Add update failure parameter to redirect URL
     */
    public function add_update_failure_param( $location ) {
        $transient = get_transient( 'pax_sup_update_failed' );
        if ( $transient ) {
            delete_transient( 'pax_sup_update_failed' );
            $location = add_query_arg(
                array(
                    'pax_update_status' => 'failed',
                    'pax_update_error'  => urlencode( $transient['error'] )
                ),
                $location
            );
        }
        return $location;
    }

    public function cleanup_after_install( $result, $hook_extra, $upgrader ) {
        if ( empty( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_basename ) {
            return $result;
        }

        if ( $this->last_backup_path ) {
            pax_sup_log_event(
                'update_complete',
                array(
                    'backup' => $this->last_backup_path,
                )
            );
        }

        return $result;
    }

    private function get_release_meta() {
        if ( null !== $this->release_meta ) {
            return $this->release_meta;
        }

        // Try file cache first
        $file_cached = $this->load_update_cache();
        if ( $file_cached && ! empty( $file_cached['version'] ) ) {
            $this->release_meta = $file_cached;
            return $this->release_meta;
        }

        // Try transient cache
        $cached = get_site_transient( 'pax_sup_release_meta' );
        if ( is_array( $cached ) && ! empty( $cached['version'] ) ) {
            $this->release_meta = $cached;
            // Also save to file cache
            $this->save_update_cache( $cached );
            return $this->release_meta;
        }

        // Try to get latest release first
        $release = $this->fetch_latest_release();
        
        // Fallback to latest commit if no release exists
        if ( empty( $release['version'] ) ) {
            $release = $this->fetch_latest_commit();
        }

        // Cache the result in both places
        if ( ! empty( $release['version'] ) ) {
            $this->release_meta = $release;
            set_site_transient( 'pax_sup_release_meta', $release, 6 * HOUR_IN_SECONDS );
            $this->save_update_cache( $release );
        }

        return $release;
    }

    /**
     * Fetch latest release from GitHub
     */
    private function fetch_latest_release() {
        $request = wp_remote_get(
            'https://api.github.com/repos/' . $this->github_repo . '/releases/latest',
            array(
                'timeout' => 15,
                'headers' => array(
                    'Accept'     => 'application/vnd.github+json',
                    'User-Agent' => 'PAX-Support-Pro/' . PAX_SUP_VER,
                ),
            )
        );

        if ( is_wp_error( $request ) ) {
            return array();
        }

        $code = wp_remote_retrieve_response_code( $request );
        if ( 200 !== $code ) {
            return array();
        }

        $body = json_decode( wp_remote_retrieve_body( $request ), true );
        if ( ! is_array( $body ) || empty( $body['tag_name'] ) ) {
            return array();
        }

        $download_url  = '';
        $signature_url = '';

        if ( ! empty( $body['assets'] ) && is_array( $body['assets'] ) ) {
            foreach ( $body['assets'] as $asset ) {
                if ( isset( $asset['browser_download_url'] ) && str_ends_with( $asset['name'], '.zip' ) ) {
                    $download_url = $asset['browser_download_url'];
                }
                if ( isset( $asset['browser_download_url'] ) && str_ends_with( $asset['name'], '.sig' ) ) {
                    $signature_url = $asset['browser_download_url'];
                }
            }
        }

        // If no zip asset, use zipball_url
        if ( empty( $download_url ) && ! empty( $body['zipball_url'] ) ) {
            $download_url = $body['zipball_url'];
        }

        return array(
            'version'       => ltrim( (string) $body['tag_name'], 'v' ),
            'url'           => isset( $body['html_url'] ) ? esc_url_raw( $body['html_url'] ) : '',
            'download_url'  => esc_url_raw( $download_url ),
            'signature_url' => esc_url_raw( $signature_url ),
            'description'   => isset( $body['body'] ) ? wp_kses_post( $body['body'] ) : '',
            'changelog'     => isset( $body['body'] ) ? wp_kses_post( $body['body'] ) : '',
            'body'          => isset( $body['body'] ) ? $body['body'] : '',
            'published_at'  => isset( $body['published_at'] ) ? $body['published_at'] : '',
        );
    }

    /**
     * Fetch latest commit as fallback
     */
    private function fetch_latest_commit() {
        $request = wp_remote_get(
            'https://api.github.com/repos/' . $this->github_repo . '/commits/main',
            array(
                'timeout' => 15,
                'headers' => array(
                    'Accept'     => 'application/vnd.github+json',
                    'User-Agent' => 'PAX-Support-Pro/' . PAX_SUP_VER,
                ),
            )
        );

        if ( is_wp_error( $request ) ) {
            return array();
        }

        $code = wp_remote_retrieve_response_code( $request );
        if ( 200 !== $code ) {
            return array();
        }

        $body = json_decode( wp_remote_retrieve_body( $request ), true );
        if ( ! is_array( $body ) || empty( $body['sha'] ) ) {
            return array();
        }

        // Use commit date as version (format: YYYY.MM.DD)
        $commit_date = isset( $body['commit']['committer']['date'] ) 
            ? $body['commit']['committer']['date'] 
            : current_time( 'mysql' );
        
        $version = date( 'Y.m.d', strtotime( $commit_date ) );
        
        // Use zipball URL for download
        $download_url = 'https://github.com/' . $this->github_repo . '/archive/refs/heads/main.zip';
        
        $commit_message = isset( $body['commit']['message'] ) ? $body['commit']['message'] : '';
        $commit_url = isset( $body['html_url'] ) ? $body['html_url'] : '';

        return array(
            'version'       => $version,
            'url'           => esc_url_raw( $commit_url ),
            'download_url'  => esc_url_raw( $download_url ),
            'signature_url' => '',
            'description'   => 'Latest commit: ' . esc_html( substr( $commit_message, 0, 100 ) ),
            'changelog'     => 'Latest commit: ' . esc_html( $commit_message ),
            'body'          => $commit_message,
            'is_commit'     => true,
        );
    }

    private function verify_signature( $package_path, $signature_content ) {
        $public_key = openssl_pkey_get_public( PAX_SUP_UPDATE_PUBLIC_KEY );

        if ( ! $public_key ) {
            return new WP_Error( 'pax_sup_signature_key', __( 'Invalid update verification key.', 'pax-support-pro' ) );
        }

        $signature = base64_decode( trim( $signature_content ) );
        if ( ! $signature ) {
            return new WP_Error( 'pax_sup_signature', __( 'Invalid update signature.', 'pax-support-pro' ) );
        }

        $contents = file_get_contents( $package_path );
        if ( false === $contents ) {
            return new WP_Error( 'pax_sup_package_read', __( 'Unable to read update package for verification.', 'pax-support-pro' ) );
        }

        $result = openssl_verify( $contents, $signature, $public_key, OPENSSL_ALGO_SHA256 );
        openssl_free_key( $public_key );

        if ( 1 !== $result ) {
            return new WP_Error( 'pax_sup_signature_invalid', __( 'Update signature verification failed.', 'pax-support-pro' ) );
        }

        return true;
    }

    /**
     * Enqueue update modal assets
     */
    public function enqueue_update_modal_assets() {
        // Only load on admin pages
        if ( ! is_admin() ) {
            return;
        }

        wp_enqueue_style(
            'pax-update-modals',
            PAX_SUP_URL . 'admin/css/update-modals.css',
            array(),
            PAX_SUP_VER
        );

        wp_enqueue_script(
            'pax-update-modals',
            PAX_SUP_URL . 'admin/js/update-modals.js',
            array(),
            PAX_SUP_VER,
            true
        );
    }

    /**
     * Handle after update complete
     */
    public function after_update_complete( $upgrader_object, $options ) {
        // Check if this is our plugin
        if ( 'update' !== $options['action'] || 'plugin' !== $options['type'] ) {
            return;
        }

        // Check if our plugin was updated
        $our_plugin = false;
        if ( isset( $options['plugins'] ) ) {
            foreach ( $options['plugins'] as $plugin ) {
                if ( $plugin === $this->plugin_basename ) {
                    $our_plugin = true;
                    break;
                }
            }
        } elseif ( isset( $options['plugin'] ) && $options['plugin'] === $this->plugin_basename ) {
            $our_plugin = true;
        }

        if ( ! $our_plugin ) {
            return;
        }

        // Get the new version
        $plugin_data = get_plugin_data( PAX_SUP_FILE );
        $new_version = $plugin_data['Version'];

        // Get changelog from release meta
        $changelog = array();
        if ( $this->release_meta && isset( $this->release_meta->body ) ) {
            $changelog = $this->parse_changelog( $this->release_meta->body );
        }

        // Set transient for modal display
        set_transient(
            'pax_sup_update_success',
            array(
                'version'   => $new_version,
                'changelog' => $changelog,
                'timestamp' => time()
            ),
            300 // 5 minutes
        );

        // Add redirect parameter
        add_filter( 'wp_redirect', array( $this, 'add_update_success_param' ) );
    }

    /**
     * Add update success parameter to redirect URL
     */
    public function add_update_success_param( $location ) {
        $transient = get_transient( 'pax_sup_update_success' );
        if ( $transient ) {
            delete_transient( 'pax_sup_update_success' );
            $location = add_query_arg(
                array(
                    'pax_update_status'  => 'success',
                    'pax_update_version' => $transient['version']
                ),
                $location
            );
        }
        return $location;
    }

    /**
     * Parse changelog from release body
     */
    private function parse_changelog( $body ) {
        $changelog = array();
        
        // Split by lines
        $lines = explode( "\n", $body );
        
        foreach ( $lines as $line ) {
            $line = trim( $line );
            
            // Look for bullet points or numbered lists
            if ( preg_match( '/^[-*•]\s+(.+)$/', $line, $matches ) ) {
                $changelog[] = trim( $matches[1] );
            } elseif ( preg_match( '/^\d+\.\s+(.+)$/', $line, $matches ) ) {
                $changelog[] = trim( $matches[1] );
            }
        }
        
        // Limit to 5 items
        return array_slice( $changelog, 0, 5 );
    }
}

function pax_sup_updater() {
    return PAX_Support_Pro_Updater::instance();
}
add_action( 'plugins_loaded', 'pax_sup_updater' );