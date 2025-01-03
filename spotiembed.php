<?php
/**
 * @wordpress-plugin
 * Plugin Name: Spotiembed
 * Description: A simple plugin which adds an Elementor widget to usable widget library.
 * Version: 1.0.0
 * Author: Berk Ilgar Ozalp
 * Author URI: https://biozalp.com/
 * License: GPL-2.0+	 
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

 if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Plugin Class
 */
final class Spotiembed {
    /**
     * Plugin Version
     */
    const VERSION = '1.0.0';

    /**
     * Minimum Elementor Version
     */
    const MINIMUM_ELEMENTOR_VERSION = '3.0.0';

    /**
     * Instance
     */
    private static $_instance = null;

    /**
     * Instance
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', [$this, 'i18n']);
        add_action('plugins_loaded', [$this, 'init']);
    }

    /**
     * Load Textdomain
     */
    public function i18n() {
        load_plugin_textdomain('spotiembed');
    }

    /**
     * Initialize the plugin
     */
    public function init() {
        // Check if Elementor installed and activated
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_elementor']);
            return;
        }

        // Check for required Elementor version
        if (!version_compare(ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_elementor_version']);
            return;
        }

        // Register Widget
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
    }

    /**
     * Admin notice for missing Elementor
     */
    public function admin_notice_missing_elementor() {
        deactivate_plugins(plugin_basename(__FILE__));

        return sprintf(
            wp_kses(
                __('<div class="notice notice-warning is-dismissible"><p>"%1$s" requires "%2$s" to be installed and activated.</p></div>', 'spotiembed'),
                [
                    'div' => [
                        'class' => [],
                    ],
                    'p' => [],
                ]
            ),
            '<strong>' . esc_html__('Spotiembed Elementor Widget', 'spotiembed') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'spotiembed') . '</strong>'
        );
    }

    /**
     * Admin notice for minimum Elementor version
     */
    public function admin_notice_minimum_elementor_version() {
        deactivate_plugins(plugin_basename(__FILE__));

        return sprintf(
            wp_kses(
                __('<div class="notice notice-warning is-dismissible"><p>"%1$s" requires "%2$s" version %3$s or greater.</p></div>', 'spotiembed'),
                [
                    'div' => [
                        'class' => [],
                    ],
                    'p' => [],
                ]
            ),
            '<strong>' . esc_html__('Spotiembed Elementor Widget', 'spotiembed') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'spotiembed') . '</strong>',
            self::MINIMUM_ELEMENTOR_VERSION
        );
    }

    /**
     * Register Widgets
     */
    public function register_widgets($widgets_manager) {
        require_once(__DIR__ . '/widgets/class-spotiembed-widget.php');
        $widgets_manager->register(new \Spotify_Embed_Widget());
    }
}

// Initialize the plugin
Spotify_Embed_Elementor_Widget::instance();