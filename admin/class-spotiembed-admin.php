<?php
/**
 * The admin-specific functionality of the plugin.
 */

class Spotiembed_Admin {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'css/spotiembed-admin.css',
            array(),
            $this->version,
            'all'
        );

        // Add Dashicons for feature icons
        wp_enqueue_style('dashicons');
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'js/spotiembed-admin.js',
            array('jquery'),
            $this->version,
            false
        );
    }

    public function add_admin_menu() {
        add_management_page(
            'Spotiembed Settings',
            'Spotiembed',
            'manage_options',
            'spotiembed-settings',
            array($this, 'display_settings_page')
        );
    }

    public function register_settings() {
        register_setting('spotiembed_settings', 'spotiembed_post_types');
    }

    /**
     * Register meta box for Spotify URL
     */
    public function register_meta_box() {
        $enabled_post_types = get_option('spotiembed_post_types', ['post']);
        
        foreach ($enabled_post_types as $post_type) {
            add_meta_box(
                'spotiembed_url_field',
                'Spotify Embed URL',
                array($this, 'render_meta_box'),
                $post_type,
                'normal',
                'high'
            );
        }
    }

    /**
     * Render meta box content
     */
    public function render_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('spotiembed_url_nonce', 'spotiembed_url_nonce');

        // Get current value
        $spotify_url = get_post_meta($post->ID, '_spotiembed_url', true);
        ?>
        <div class="spotiembed-field-container">
            <p class="description">Enter a Spotify URL for any content you want to embed (track, album, artist, playlist, etc.)</p>
            <input 
                type="url" 
                id="spotiembed_url" 
                name="spotiembed_url" 
                value="<?php echo esc_attr($spotify_url); ?>" 
                class="widefat"
                placeholder="https://open.spotify.com/..."
            >
            <?php if (!empty($spotify_url)) : ?>
                <div class="preview-container" style="margin-top: 10px;">
                    <button type="button" class="button preview-spotify-embed">Preview Embed</button>
                    <div class="spotify-preview" style="margin-top: 10px; display: none;"></div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Save meta box data
     */
    public function save_meta_box($post_id) {
        // Check if our nonce is set
        if (!isset($_POST['spotiembed_url_nonce'])) {
            return;
        }

        // Verify the nonce
        if (!wp_verify_nonce($_POST['spotiembed_url_nonce'], 'spotiembed_url_nonce')) {
            return;
        }

        // If this is an autosave, we don't want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save the Spotify URL
        if (isset($_POST['spotiembed_url'])) {
            $spotify_url = esc_url_raw($_POST['spotiembed_url']);
            if (!empty($spotify_url)) {
                update_post_meta($post_id, '_spotiembed_url', $spotify_url);
            } else {
                delete_post_meta($post_id, '_spotiembed_url');
            }
        }
    }

    public function display_settings_page() {
        $post_types = get_post_types(['public' => true], 'objects');
        $saved_post_types = get_option('spotiembed_post_types', ['post']);
        ?>
        <div class="wrap">
            <div class="notice-container">
                <?php do_action('admin_notices'); ?>
            </div>
            <div class="cgf-wrap">
                <div class="cgf-admin-header">
                    <div>
                        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                        <p style="margin: 10px 0 0; opacity: 0.9;">Easily embed Spotify content in your WordPress posts and pages</p>
                    </div>
                </div>

                <div class="cgf-admin-container">
                    <div class="cgf-admin-main">
                        <form method="post" action="options.php">
                            <?php
                            settings_fields('spotiembed_settings');
                            do_settings_sections('spotiembed_settings');
                            ?>
                            <h2>Enable Spotify Embed</h2>
                            <p class="description">Select which post types should have the Spotify embed functionality available. The embed option will appear in the editor for the selected post types.</p>
                            
                            <ul class="cgf-post-type-list">
                                <?php foreach ($post_types as $post_type): ?>
                                    <li>
                                        <label>
                                            <input type="checkbox"
                                                   name="spotiembed_post_types[]"
                                                   value="<?php echo esc_attr($post_type->name); ?>"
                                                   <?php checked(in_array($post_type->name, $saved_post_types)); ?>>
                                            <span class="dashicons dashicons-<?php echo $post_type->name === 'post' ? 'admin-post' : 'admin-page'; ?>"></span>
                                            <span><?php echo esc_html($post_type->labels->singular_name); ?></span>
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <?php submit_button('Save Settings'); ?>
                        </form>
                    </div>

                    <div class="cgf-admin-sidebar">
                        <h3>Support the Development</h3>
                        <p>If you find this plugin useful, consider supporting its development. Your support helps maintain and improve the plugin with new features and updates!</p>
                        
                        <a href="https://www.buymeacoffee.com/biozalp" target="_blank" class="cgf-coffee-button">
                            <span class="dashicons dashicons-coffee"></span>
                            Buy me a coffee
                        </a>

                        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef;">
                            <h3>Need Help?</h3>
                            <p>Check out the <a href="https://wordpress.org/plugins/spotiembed" target="_blank">plugin documentation</a> or <a href="https://wordpress.org/support/plugin/spotiembed" target="_blank">support forums</a> for assistance. If you cannot find what you're looking for, please <a href="mailto:berk@biozalp.com" target="_blank">send an email</a>.</p>
                        </div>

                        <div class="cgf-plugin-recommendation">
                            <h3>Check Out My Other Plugins</h3>
                            <p>Looking for more ways to enhance your WordPress site? Check out my other plugins for additional functionality.</p>
                            <a href="https://biozalp.com/plugins" target="_blank" class="button">
                                <span class="dashicons dashicons-external" style="margin: 3px 5px 0 -2px;"></span>
                                View More Plugins
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
