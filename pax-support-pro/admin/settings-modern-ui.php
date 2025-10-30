<?php
/**
 * Modern Settings UI Rendering
 * PAX Support Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function pax_sup_render_modern_settings() {
    if ( ! current_user_can( pax_sup_get_console_capability() ) ) {
        return;
    }

    $options = pax_sup_get_options();
    ?>
    <div class="pax-modern-settings">
        <!-- Header -->
        <div class="pax-settings-header">
            <h1>
                <span class="dashicons dashicons-admin-settings"></span>
                <?php esc_html_e( 'PAX Support Pro Settings', 'pax-support-pro' ); ?>
            </h1>
        </div>

        <form method="post" action="">
            <?php wp_nonce_field( 'pax_sup_save_settings' ); ?>
            
            <div class="pax-settings-layout">
                <!-- Settings Content -->
                <div class="pax-settings-content">
                    
                    <!-- General Settings Card -->
                    <div class="pax-card">
                        <div class="pax-card-header">
                            <span class="dashicons dashicons-admin-generic"></span>
                            <h2><?php esc_html_e( 'General Settings', 'pax-support-pro' ); ?></h2>
                        </div>
                        <div class="pax-card-body">
                            <!-- Enable Plugin -->
                            <div class="pax-form-group">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <div>
                                        <label class="pax-form-label">
                                            <span class="dashicons dashicons-yes-alt"></span>
                                            <?php esc_html_e( 'Enable Plugin', 'pax-support-pro' ); ?>
                                            <span class="pax-tooltip" data-tooltip="<?php esc_attr_e( 'Master switch to enable/disable the entire support system', 'pax-support-pro' ); ?>">?</span>
                                        </label>
                                        <p class="pax-form-description"><?php esc_html_e( 'Master switch to enable or disable the entire support system.', 'pax-support-pro' ); ?></p>
                                    </div>
                                    <label class="pax-toggle">
                                        <input type="checkbox" name="enabled" <?php checked( $options['enabled'] ); ?>>
                                        <span class="pax-toggle-slider"></span>
                                    </label>
                                </div>
                            </div>

                            <!-- Enable Chat -->
                            <div class="pax-form-group">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <div>
                                        <label class="pax-form-label">
                                            <span class="dashicons dashicons-format-chat"></span>
                                            <?php esc_html_e( 'Enable Chat', 'pax-support-pro' ); ?>
                                            <span class="pax-tooltip" data-tooltip="<?php esc_attr_e( 'Show the chat launcher on your website', 'pax-support-pro' ); ?>">?</span>
                                        </label>
                                        <p class="pax-form-description"><?php esc_html_e( 'Display the chat launcher widget on your website.', 'pax-support-pro' ); ?></p>
                                    </div>
                                    <label class="pax-toggle">
                                        <input type="checkbox" name="enable_chat" <?php checked( $options['enable_chat'] ); ?>>
                                        <span class="pax-toggle-slider"></span>
                                    </label>
                                </div>
                            </div>

                            <!-- Enable Tickets -->
                            <div class="pax-form-group">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <div>
                                        <label class="pax-form-label">
                                            <span class="dashicons dashicons-tickets-alt"></span>
                                            <?php esc_html_e( 'Enable Tickets', 'pax-support-pro' ); ?>
                                            <span class="pax-tooltip" data-tooltip="<?php esc_attr_e( 'Allow users to create support tickets', 'pax-support-pro' ); ?>">?</span>
                                        </label>
                                        <p class="pax-form-description"><?php esc_html_e( 'Allow users to create and manage support tickets.', 'pax-support-pro' ); ?></p>
                                    </div>
                                    <label class="pax-toggle">
                                        <input type="checkbox" name="enable_ticket" <?php checked( $options['enable_ticket'] ); ?>>
                                        <span class="pax-toggle-slider"></span>
                                    </label>
                                </div>
                            </div>

                            <!-- Brand Name -->
                            <div class="pax-form-group">
                                <label class="pax-form-label">
                                    <span class="dashicons dashicons-tag"></span>
                                    <?php esc_html_e( 'Brand Name', 'pax-support-pro' ); ?>
                                    <span class="pax-tooltip" data-tooltip="<?php esc_attr_e( 'Your brand name displayed in the chat header', 'pax-support-pro' ); ?>">?</span>
                                </label>
                                <p class="pax-form-description"><?php esc_html_e( 'Your brand name displayed in the chat header.', 'pax-support-pro' ); ?></p>
                                <input type="text" name="brand_name" value="<?php echo esc_attr( $options['brand_name'] ); ?>" class="pax-color-input" style="font-family: inherit;">
                            </div>
                        </div>
                    </div>

                    <!-- Color Settings Card -->
                    <div class="pax-card">
                        <div class="pax-card-header">
                            <span class="dashicons dashicons-art"></span>
                            <h2><?php esc_html_e( 'Color Scheme', 'pax-support-pro' ); ?></h2>
                        </div>
                        <div class="pax-card-body">
                            <!-- Accent Color -->
                            <div class="pax-form-group">
                                <label class="pax-form-label">
                                    <span class="dashicons dashicons-admin-appearance"></span>
                                    <?php esc_html_e( 'Primary Color', 'pax-support-pro' ); ?>
                                    <span class="pax-tooltip" data-tooltip="<?php esc_attr_e( 'Main accent color for buttons and highlights', 'pax-support-pro' ); ?>">?</span>
                                </label>
                                <p class="pax-form-description"><?php esc_html_e( 'Changes the main accent color for the chat interface.', 'pax-support-pro' ); ?></p>
                                <div class="pax-color-picker-wrapper">
                                    <div class="pax-color-preview" style="background: <?php echo esc_attr( $options['color_accent'] ); ?>"></div>
                                    <input type="color" name="color_accent" value="<?php echo esc_attr( $options['color_accent'] ); ?>" class="pax-color-input">
                                </div>
                            </div>

                            <!-- Background Color -->
                            <div class="pax-form-group">
                                <label class="pax-form-label">
                                    <span class="dashicons dashicons-admin-appearance"></span>
                                    <?php esc_html_e( 'Background Color', 'pax-support-pro' ); ?>
                                    <span class="pax-tooltip" data-tooltip="<?php esc_attr_e( 'Main background color of the chat window', 'pax-support-pro' ); ?>">?</span>
                                </label>
                                <p class="pax-form-description"><?php esc_html_e( 'Main background color of the chat window.', 'pax-support-pro' ); ?></p>
                                <div class="pax-color-picker-wrapper">
                                    <div class="pax-color-preview" style="background: <?php echo esc_attr( $options['color_bg'] ); ?>"></div>
                                    <input type="color" name="color_bg" value="<?php echo esc_attr( $options['color_bg'] ); ?>" class="pax-color-input">
                                </div>
                            </div>

                            <!-- Panel Color -->
                            <div class="pax-form-group">
                                <label class="pax-form-label">
                                    <span class="dashicons dashicons-admin-appearance"></span>
                                    <?php esc_html_e( 'Panel Color', 'pax-support-pro' ); ?>
                                    <span class="pax-tooltip" data-tooltip="<?php esc_attr_e( 'Color for panels and cards within the chat', 'pax-support-pro' ); ?>">?</span>
                                </label>
                                <p class="pax-form-description"><?php esc_html_e( 'Color for panels and cards within the chat.', 'pax-support-pro' ); ?></p>
                                <div class="pax-color-picker-wrapper">
                                    <div class="pax-color-preview" style="background: <?php echo esc_attr( $options['color_panel'] ); ?>"></div>
                                    <input type="color" name="color_panel" value="<?php echo esc_attr( $options['color_panel'] ); ?>" class="pax-color-input">
                                </div>
                            </div>

                            <!-- Border Color -->
                            <div class="pax-form-group">
                                <label class="pax-form-label">
                                    <span class="dashicons dashicons-admin-appearance"></span>
                                    <?php esc_html_e( 'Border Color', 'pax-support-pro' ); ?>
                                    <span class="pax-tooltip" data-tooltip="<?php esc_attr_e( 'Color for borders and dividers', 'pax-support-pro' ); ?>">?</span>
                                </label>
                                <p class="pax-form-description"><?php esc_html_e( 'Color for borders and dividers.', 'pax-support-pro' ); ?></p>
                                <div class="pax-color-picker-wrapper">
                                    <div class="pax-color-preview" style="background: <?php echo esc_attr( $options['color_border'] ); ?>"></div>
                                    <input type="color" name="color_border" value="<?php echo esc_attr( $options['color_border'] ); ?>" class="pax-color-input">
                                </div>
                            </div>

                            <!-- Text Color -->
                            <div class="pax-form-group">
                                <label class="pax-form-label">
                                    <span class="dashicons dashicons-admin-appearance"></span>
                                    <?php esc_html_e( 'Text Color', 'pax-support-pro' ); ?>
                                    <span class="pax-tooltip" data-tooltip="<?php esc_attr_e( 'Primary text color', 'pax-support-pro' ); ?>">?</span>
                                </label>
                                <p class="pax-form-description"><?php esc_html_e( 'Primary text color for the chat interface.', 'pax-support-pro' ); ?></p>
                                <div class="pax-color-picker-wrapper">
                                    <div class="pax-color-preview" style="background: <?php echo esc_attr( $options['color_text'] ); ?>"></div>
                                    <input type="color" name="color_text" value="<?php echo esc_attr( $options['color_text'] ); ?>" class="pax-color-input">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AI Settings Card -->
                    <div class="pax-card">
                        <div class="pax-card-header">
                            <span class="dashicons dashicons-superhero"></span>
                            <h2><?php esc_html_e( 'AI Assistant', 'pax-support-pro' ); ?></h2>
                        </div>
                        <div class="pax-card-body">
                            <!-- Enable AI -->
                            <div class="pax-form-group">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <div>
                                        <label class="pax-form-label">
                                            <span class="dashicons dashicons-superhero-alt"></span>
                                            <?php esc_html_e( 'Enable AI Assistant', 'pax-support-pro' ); ?>
                                            <span class="pax-tooltip" data-tooltip="<?php esc_attr_e( 'Enable AI-powered chat responses', 'pax-support-pro' ); ?>">?</span>
                                        </label>
                                        <p class="pax-form-description"><?php esc_html_e( 'Enable AI-powered automatic responses in chat.', 'pax-support-pro' ); ?></p>
                                    </div>
                                    <label class="pax-toggle">
                                        <input type="checkbox" name="ai_assistant_enabled" <?php checked( $options['ai_assistant_enabled'] ); ?>>
                                        <span class="pax-toggle-slider"></span>
                                    </label>
                                </div>
                            </div>

                            <!-- OpenAI Integration -->
                            <div class="pax-form-group">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <div>
                                        <label class="pax-form-label">
                                            <span class="dashicons dashicons-cloud"></span>
                                            <?php esc_html_e( 'OpenAI Integration', 'pax-support-pro' ); ?>
                                            <span class="pax-tooltip" data-tooltip="<?php esc_attr_e( 'Use OpenAI API for advanced AI responses', 'pax-support-pro' ); ?>">?</span>
                                        </label>
                                        <p class="pax-form-description"><?php esc_html_e( 'Use OpenAI API for advanced AI responses.', 'pax-support-pro' ); ?></p>
                                    </div>
                                    <label class="pax-toggle">
                                        <input type="checkbox" name="openai_enabled" <?php checked( $options['openai_enabled'] ); ?>>
                                        <span class="pax-toggle-slider"></span>
                                    </label>
                                </div>
                            </div>

                            <!-- API Key -->
                            <div class="pax-form-group">
                                <label class="pax-form-label">
                                    <span class="dashicons dashicons-admin-network"></span>
                                    <?php esc_html_e( 'OpenAI API Key', 'pax-support-pro' ); ?>
                                    <span class="pax-tooltip" data-tooltip="<?php esc_attr_e( 'Your OpenAI API key for authentication', 'pax-support-pro' ); ?>">?</span>
                                </label>
                                <p class="pax-form-description"><?php esc_html_e( 'Your OpenAI API key. Can also be defined in wp-config.php as PXA_OPENAI_API_KEY.', 'pax-support-pro' ); ?></p>
                                <input type="password" name="openai_key" value="<?php echo esc_attr( $options['openai_key'] ); ?>" class="pax-color-input" style="font-family: 'Monaco', monospace;" autocomplete="off">
                            </div>

                            <!-- Temperature -->
                            <div class="pax-form-group">
                                <label class="pax-form-label">
                                    <span class="dashicons dashicons-performance"></span>
                                    <?php esc_html_e( 'AI Temperature', 'pax-support-pro' ); ?>
                                    <span class="pax-tooltip" data-tooltip="<?php esc_attr_e( 'Controls randomness: 0 = focused, 1 = creative', 'pax-support-pro' ); ?>">?</span>
                                </label>
                                <p class="pax-form-description"><?php esc_html_e( 'Controls AI creativity. Lower values (0.1-0.3) are more focused, higher values (0.7-1.0) are more creative.', 'pax-support-pro' ); ?></p>
                                <div class="pax-range-wrapper">
                                    <div class="pax-range-header">
                                        <span><?php esc_html_e( 'Temperature', 'pax-support-pro' ); ?></span>
                                        <span class="pax-range-value"></span>
                                    </div>
                                    <input type="range" name="openai_temperature" min="0" max="1" step="0.05" value="<?php echo esc_attr( $options['openai_temperature'] ); ?>" class="pax-range-slider" data-unit="">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Layout Settings Card -->
                    <div class="pax-card">
                        <div class="pax-card-header">
                            <span class="dashicons dashicons-layout"></span>
                            <h2><?php esc_html_e( 'Layout & Position', 'pax-support-pro' ); ?></h2>
                        </div>
                        <div class="pax-card-body">
                            <!-- Launcher Position -->
                            <div class="pax-form-group">
                                <label class="pax-form-label">
                                    <span class="dashicons dashicons-move"></span>
                                    <?php esc_html_e( 'Chat Launcher Position', 'pax-support-pro' ); ?>
                                    <span class="pax-tooltip" data-tooltip="<?php esc_attr_e( 'Where the chat launcher appears on your site', 'pax-support-pro' ); ?>">?</span>
                                </label>
                                <p class="pax-form-description"><?php esc_html_e( 'Selects where the chat launcher button appears on your website.', 'pax-support-pro' ); ?></p>
                                <select name="launcher_position" class="pax-select">
                                    <option value="bottom-left" <?php selected( $options['launcher_position'], 'bottom-left' ); ?>><?php esc_html_e( 'Bottom Left', 'pax-support-pro' ); ?></option>
                                    <option value="bottom-right" <?php selected( $options['launcher_position'], 'bottom-right' ); ?>><?php esc_html_e( 'Bottom Right', 'pax-support-pro' ); ?></option>
                                    <option value="top-left" <?php selected( $options['launcher_position'], 'top-left' ); ?>><?php esc_html_e( 'Top Left', 'pax-support-pro' ); ?></option>
                                    <option value="top-right" <?php selected( $options['launcher_position'], 'top-right' ); ?>><?php esc_html_e( 'Top Right', 'pax-support-pro' ); ?></option>
                                </select>
                            </div>

                            <!-- Ticket Cooldown -->
                            <div class="pax-form-group">
                                <label class="pax-form-label">
                                    <span class="dashicons dashicons-clock"></span>
                                    <?php esc_html_e( 'Ticket Cooldown (days)', 'pax-support-pro' ); ?>
                                    <span class="pax-tooltip" data-tooltip="<?php esc_attr_e( 'Days users must wait between tickets (0 = disabled)', 'pax-support-pro' ); ?>">?</span>
                                </label>
                                <p class="pax-form-description"><?php esc_html_e( 'Number of days users must wait before creating another ticket. Set to 0 to disable.', 'pax-support-pro' ); ?></p>
                                <div class="pax-range-wrapper">
                                    <div class="pax-range-header">
                                        <span><?php esc_html_e( 'Days', 'pax-support-pro' ); ?></span>
                                        <span class="pax-range-value"></span>
                                    </div>
                                    <input type="range" name="ticket_cooldown_days" min="0" max="30" step="1" value="<?php echo intval( $options['ticket_cooldown_days'] ); ?>" class="pax-range-slider" data-unit=" days">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Menu Items -->
                    <div class="pax-card">
                        <div class="pax-card-header">
                            <span class="dashicons dashicons-menu-alt"></span>
                            <h2><?php esc_html_e( 'Chat Menu Items', 'pax-support-pro' ); ?></h2>
                        </div>
                        <div class="pax-card-body">
                            <p class="pax-form-description" style="margin-bottom: 20px;">
                                <?php esc_html_e( 'Customize the menu items shown in the chat widget. Click on a label to edit it inline. Changes sync in real-time.', 'pax-support-pro' ); ?>
                            </p>
                            
                            <div id="pax-menu-items-list">
                                <?php
                                $menu_items = isset( $options['chat_menu_items'] ) && is_array( $options['chat_menu_items'] )
                                    ? $options['chat_menu_items']
                                    : pax_sup_default_menu_items();
                                
                                $menu_icons_map = array(
                                    'chat'          => 'dashicons-format-chat',
                                    'ticket'        => 'dashicons-tickets-alt',
                                    'help'          => 'dashicons-editor-help',
                                    'speed'         => 'dashicons-performance',
                                    'agent'         => 'dashicons-admin-users',
                                    'whatsnew'      => 'dashicons-megaphone',
                                    'troubleshooter'=> 'dashicons-admin-tools',
                                    'diag'          => 'dashicons-chart-line',
                                    'callback'      => 'dashicons-phone',
                                    'order'         => 'dashicons-cart',
                                    'myreq'         => 'dashicons-list-view',
                                    'feedback'      => 'dashicons-star-filled',
                                    'donate'        => 'dashicons-heart',
                                );
                                
                                foreach ( $menu_items as $key => $item ) :
                                    $label = isset( $item['label'] ) ? $item['label'] : ucfirst( $key );
                                    $visible = isset( $item['visible'] ) ? $item['visible'] : 1;
                                    $icon_class = isset( $menu_icons_map[ $key ] ) ? $menu_icons_map[ $key ] : 'dashicons-admin-generic';
                                ?>
                                <div class="pax-menu-item" data-key="<?php echo esc_attr( $key ); ?>">
                                    <div class="pax-menu-item-icon">
                                        <span class="dashicons <?php echo esc_attr( $icon_class ); ?>"></span>
                                    </div>
                                    <div class="pax-menu-item-content">
                                        <input type="text" 
                                               name="menu_items[<?php echo esc_attr( $key ); ?>][label]" 
                                               value="<?php echo esc_attr( $label ); ?>" 
                                               class="pax-menu-item-label"
                                               data-original="<?php echo esc_attr( $label ); ?>"
                                               placeholder="<?php echo esc_attr( ucfirst( $key ) ); ?>">
                                        <span class="pax-menu-item-key"><?php echo esc_html( $key ); ?></span>
                                    </div>
                                    <label class="pax-toggle pax-menu-item-toggle">
                                        <input type="checkbox" 
                                               name="menu_items[<?php echo esc_attr( $key ); ?>][visible]" 
                                               value="1"
                                               <?php checked( $visible ); ?>>
                                        <span class="pax-toggle-slider"></span>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Live Preview Panel -->
                <div class="pax-preview-panel">
                    <div class="pax-preview-header">
                        <h3><?php esc_html_e( 'Live Preview', 'pax-support-pro' ); ?></h3>
                        <span class="pax-preview-badge">
                            <span class="dashicons dashicons-visibility" style="font-size: 14px; width: 14px; height: 14px;"></span>
                            <?php esc_html_e( 'Live', 'pax-support-pro' ); ?>
                        </span>
                    </div>
                    <div class="pax-preview-content">
                        <div class="pax-preview-chat">
                            <div class="pax-preview-header-bar">
                                <span class="pax-preview-led"></span>
                                <span class="pax-preview-title"><?php echo esc_html( $options['brand_name'] ); ?></span>
                            </div>
                            <div class="pax-preview-message">
                                <?php esc_html_e( '👋 Welcome! How can I help you today?', 'pax-support-pro' ); ?>
                            </div>
                            <button class="pax-preview-button" type="button">
                                <?php esc_html_e( 'Send Message', 'pax-support-pro' ); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pax-actions">
                <button type="submit" class="pax-btn pax-btn-primary">
                    <span class="dashicons dashicons-saved"></span>
                    <?php esc_html_e( 'Save Changes', 'pax-support-pro' ); ?>
                </button>
                <button type="button" id="pax-reset-defaults" class="pax-btn pax-btn-danger">
                    <span class="dashicons dashicons-image-rotate"></span>
                    <?php esc_html_e( 'Reset to Defaults', 'pax-support-pro' ); ?>
                </button>
            </div>
        </form>

        <!-- Reset Confirmation Modal -->
        <div id="pax-reset-modal" class="pax-modal-overlay">
            <div class="pax-modal">
                <div class="pax-modal-header">
                    <span class="dashicons dashicons-warning"></span>
                    <h3><?php esc_html_e( 'Reset to Default Settings?', 'pax-support-pro' ); ?></h3>
                </div>
                <div class="pax-modal-body">
                    <p><?php esc_html_e( 'This will restore all settings to their default values. Your current configuration will be lost.', 'pax-support-pro' ); ?></p>
                    <p><strong><?php esc_html_e( 'This action cannot be undone.', 'pax-support-pro' ); ?></strong></p>
                </div>
                <div class="pax-modal-actions">
                    <button type="button" id="pax-cancel-reset" class="pax-btn pax-btn-secondary">
                        <?php esc_html_e( 'Cancel', 'pax-support-pro' ); ?>
                    </button>
                    <button type="button" id="pax-confirm-reset" class="pax-btn pax-btn-danger">
                        <?php esc_html_e( 'Reset Settings', 'pax-support-pro' ); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php
}
