<?php
/**
 * Spotiembed Widget Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Spotiembed_Widget extends \Elementor\Widget_Base {
    /**
     * Get widget name
     */
    public function get_name() {
        return 'spotiembed';
    }

    /**
     * Get widget title
     */
    public function get_title() {
        return esc_html__('Spotiembed', 'spotiembed');
    }

    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-headphones';
    }

    /**
     * Get widget categories
     */
    public function get_categories() {
        return ['general'];
    }

    /**
     * Get widget keywords
     */
    public function get_keywords() {
        return ['spotify', 'music', 'embed', 'player', 'acf'];
    }

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);
        add_action('elementor/frontend/after_register_styles', [$this, 'register_widget_styles']);
        add_action('elementor/element/after_add_attributes', [$this, 'add_widget_class']);
    }

    public function add_widget_class($element) {
        if ($element->get_name() === 'spotiembed') {
            $settings = $element->get_settings_for_display();
            $height = !empty($settings['embed_height']) ? $settings['embed_height'] : '152';
            $element->add_render_attribute('_wrapper', 'class', 'spotiembed-height-' . $height);
        }
    }

    public function register_widget_styles() {
        wp_register_style('spotiembed-widget', false);
        wp_enqueue_style('spotiembed-widget');
        wp_add_inline_style('spotiembed-widget', '
            /* Target all possible Elementor widget states */
            .elementor-widget-spotiembed,
            .elementor-element.elementor-widget-spotiembed,
            .elementor-element.elementor-element-edit-mode.elementor-widget-spotiembed,
            .elementor-element.elementor-element-edit-mode.elementor-widget.elementor-widget-spotiembed {
                margin: 0 !important;
                padding: 0 !important;
                line-height: 0 !important;
            }
            
            /* Target widget container */
            .elementor-widget-spotiembed .elementor-widget-container,
            .elementor-element.elementor-widget-spotiembed .elementor-widget-container {
                margin: 0 !important;
                padding: 0 !important;
                line-height: 0 !important;
            }

            /* Target preview mode */
            .elementor-editor-active .elementor-widget-spotiembed,
            .elementor-editor-active .elementor-widget-spotiembed .elementor-widget-container {
                margin: 0 !important;
                padding: 0 !important;
                line-height: 0 !important;
            }

            /* Force margin/padding removal for 152px specifically */
            .elementor-widget-spotiembed[data-settings*=\'"size":152\'],
            .elementor-widget-spotiembed[data-settings*=\'"size":152\'] .elementor-widget-container,
            .elementor-widget-spotiembed[data-settings*=\'"size":152\'] .spotiembed {
                margin: 0 !important;
                padding: 0 !important;
                line-height: 0 !important;
            }

            .elementor-widget-spotiembed iframe {
                display: block !important;
                vertical-align: bottom !important;
            }
            /* Target the specific 152px case */
            .elementor-widget-spotiembed iframe[height="152"] {
                margin-bottom: -20px !important;
            }

            .elementor-widget.spotiembed-height-152 {
                min-height: 152px !important;
                height: 152px !important;
            }
            .elementor-widget.spotiembed-height-152 .elementor-widget-container {
                min-height: 152px !important;
                height: 152px !important;
            }
        ');
    }

    /**
     * Register widget controls
     */
    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Content', 'spotiembed'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'spotify_url',
            [
                'label' => esc_html__('Spotify URL', 'spotiembed'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'dynamic' => [
                    'active' => true,
                    'categories' => [
                        \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
                        \Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
                        \Elementor\Modules\DynamicTags\Module::POST_META_CATEGORY,
                    ],
                ],
                'placeholder' => esc_html__('Enter Spotify URL', 'spotiembed'),
                'description' => esc_html__('Enter the URL of a Spotify track, album, or playlist. Supports ACF fields.', 'spotiembed'),
            ]
        );

        // Add fallback URL control
        $this->add_control(
            'fallback_url',
            [
                'label' => esc_html__('Fallback URL', 'spotiembed'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__('Enter fallback Spotify URL', 'spotiembed'),
                'description' => esc_html__('This URL will be used if the dynamic URL is empty', 'spotiembed'),
                'condition' => [
                    'spotify_url[url]!' => '',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_section',
            [
                'label' => esc_html__('Style', 'spotiembed'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'content_width',
            [
                'label' => esc_html__('Content Width', 'spotiembed'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'full',
                'options' => [
                    'full' => esc_html__('Full Width', 'spotiembed'),
                    'boxed' => esc_html__('Boxed', 'spotiembed'),
                ],
                'prefix_class' => 'elementor-widget-',
                'render_type' => 'template',
            ]
        );

        $this->add_responsive_control(
            'embed_width',
            [
                'label' => esc_html__('Width', 'spotiembed'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['%', 'px'],
                'range' => [
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => 0,
                        'max' => 2000,
                        'step' => 10,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 100,
                ],
                'selectors' => [
                    '{{WRAPPER}} iframe' => 'width: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'content_width' => 'boxed',
                ],
            ]
        );

        $this->add_control(
            'embed_height',
            [
                'label' => esc_html__('Player Height', 'spotiembed'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '152',
                'options' => [
                    '80' => esc_html__('Compact (80px)', 'spotiembed'),
                    '152' => esc_html__('Standard (152px)', 'spotiembed'),
                    '352' => esc_html__('Expanded (352px)', 'spotiembed'),
                ],
                'selectors' => [
                    '{{WRAPPER}} iframe' => 'height: {{VALUE}}px;',
                ],
            ]
        );

        $this->add_control(
            'align',
            [
                'label' => esc_html__('Alignment', 'spotiembed'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'spotiembed'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'spotiembed'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'spotiembed'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .spotiembed' => 'text-align: {{VALUE}};',
                ],
                'condition' => [
                    'content_width' => 'boxed',
                ],
            ]
        );

        $this->add_control(
            'widget_spacing',
            [
                'label' => esc_html__('Widget Spacing', 'spotiembed'),
                'type' => \Elementor\Controls_Manager::HIDDEN,
                'default' => 'custom',
                'prefix_class' => 'elementor-widget-',
                'selectors' => [
                    '.elementor-widget.elementor-element.elementor-widget-spotiembed' => 'margin-bottom: 0 !important;',
                    '.elementor-widget.elementor-element.elementor-widget-spotiembed .elementor-widget-container' => 'margin-bottom: 0 !important;',
                ],
            ]
        );

        // Add custom CSS for height-specific cases
        $this->add_control(
            'height_styles',
            [
                'type' => \Elementor\Controls_Manager::HIDDEN,
                'default' => 'yes',
                'selectors' => [
                    '.elementor-widget.spotiembed-height-152' => 'min-height: 152px !important; height: 152px !important;',
                    '.elementor-widget.spotiembed-height-152 .elementor-widget-container' => 'min-height: 152px !important; height: 152px !important;',
                ],
            ]
        );

        $this->add_responsive_control(
            'widget_space',
            [
                'label' => esc_html__('Widget Space', 'spotiembed'),
                'type' => \Elementor\Controls_Manager::HIDDEN,
                'default' => [
                    'size' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}}' => 'margin: 0 !important; padding: 0 !important;',
                    '{{WRAPPER}} .elementor-widget-container' => 'margin: 0 !important; padding: 0 !important;',
                    '{{WRAPPER}}.elementor-widget-spotiembed' => 'margin: 0 !important; padding: 0 !important;',
                    '{{WRAPPER}} .spotiembed' => 'margin: 0 !important; padding: 0 !important;',
                ],
                'condition' => [
                    'embed_height!' => '',
                ],
            ]
        );

        // Add custom JS and CSS for snap points
        $this->add_control(
            'height_snap_script',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<style>
                    .spotiembed-height-slider .elementor-slider {
                        position: relative;
                        padding-bottom: 20px !important;
                    }
                    .spotiembed-height-slider .elementor-slider::before,
                    .spotiembed-height-slider .elementor-slider::after {
                        content: "";
                        position: absolute;
                        bottom: -5px;
                        width: 2px;
                        height: 10px;
                        background: #556068;
                    }
                    .spotiembed-height-slider .elementor-slider::before {
                        left: -2px;
                    }
                    .spotiembed-height-slider .elementor-slider::after {
                        right: -2px;
                    }
                    .spotiembed-height-slider .elementor-slider .noUi-handle::before {
                        content: "";
                        position: absolute;
                        left: 50%;
                        bottom: -15px;
                        transform: translateX(-50%);
                        width: 2px;
                        height: 10px;
                        background: #556068;
                    }
                    .spotiembed-height-slider .elementor-control-field {
                        position: relative;
                    }
                    .spotiembed-height-slider .elementor-control-field::after {
                        content: "Compact  •  Standard  •  Expanded";
                        position: absolute;
                        bottom: -20px;
                        left: -2px;
                        right: -2px;
                        text-align: center;
                        font-size: 11px;
                        color: #556068;
                    }
                    .spotiembed-height-slider .elementor-slider-input {
                        display: none;
                    }
                </style>',
            ]
        );

        // Add custom JS for height control
        $this->add_control(
            'height_refresh_script',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<script>
                    jQuery(document).ready(function($) {
                        elementor.channels.editor.on("change:section", function() {
                            var widget = $(".elementor-widget-spotiembed");
                            if (widget.length) {
                                elementor.reloadPreview();
                            }
                        });
                    });
                </script>',
            ]
        );

        // Add JavaScript to handle height changes
        $this->add_control(
            'height_handler_script',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<script>
                    elementor.channels.editor.on("change", function(view) {
                        if (view.model.get("widgetType") === "spotiembed") {
                            var height = view.model.getSetting("embed_height");
                            var $iframe = view.$el.find("iframe");
                            if ($iframe.length) {
                                $iframe.height(height);
                                // Force elementor to recalculate widget height
                                elementor.helpers.refreshHeight();
                            }
                        }
                    });
                </script>',
                'condition' => [
                    'embed_height!' => '',
                ],
            ]
        );

        $this->add_control(
            'theme',
            [
                'label' => esc_html__('Theme', 'spotiembed'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Dark', 'spotiembed'),
                'label_off' => esc_html__('Default', 'spotiembed'),
                'return_value' => '0',
                'default' => '',
                'description' => esc_html__('Toggle between default and dark theme', 'spotiembed'),
                'separator' => 'before',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Convert regular Spotify URL to embed URL
     */
    private function get_embed_url($spotify_url) {
        if (empty($spotify_url)) {
            return '';
        }

        // Remove any query parameters
        $spotify_url = strtok($spotify_url, '?');

        // Handle ACF field values that might be arrays or objects
        if (is_array($spotify_url)) {
            $spotify_url = isset($spotify_url['url']) ? $spotify_url['url'] : '';
        } elseif (is_object($spotify_url)) {
            $spotify_url = isset($spotify_url->url) ? $spotify_url->url : '';
        }

        // Clean the URL
        $spotify_url = trim($spotify_url);
        
        if (empty($spotify_url)) {
            return '';
        }

        // Handle both regular and embed URLs
        $spotify_url = str_replace('open.spotify.com', 'open.spotify.com/embed', $spotify_url);
        
        // If it's already an embed URL, return as is
        if (strpos($spotify_url, '/embed/') !== false) {
            return esc_url($spotify_url);
        }

        // Convert regular URL to embed URL
        return esc_url(str_replace('spotify.com', 'spotify.com/embed', $spotify_url));
    }

    /**
     * Get URL from settings with fallback
     */
    private function get_url_from_settings($settings) {
        $url = !empty($settings['spotify_url']) ? $settings['spotify_url'] : '';
        
        // If URL is empty and fallback is set, use fallback
        if (empty($url) && !empty($settings['fallback_url'])) {
            $url = $settings['fallback_url'];
        }

        // Handle ACF field return values
        if (is_array($url)) {
            if (isset($url['url'])) {
                $url = $url['url'];
            } elseif (isset($url['value'])) {
                $url = $url['value'];
            }
        }

        return $url;
    }

    /**
     * Render widget output on the frontend
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Get URL with fallback support
        $spotify_url = $this->get_url_from_settings($settings);

        if (empty($spotify_url)) {
            return;
        }

        $embed_url = $this->get_embed_url($spotify_url);
        
        if (empty($embed_url)) {
            return;
        }

        // Get height from settings
        $height = !empty($settings['embed_height']) ? $settings['embed_height'] : '152';

        // Add theme parameter if dark theme is selected
        if (!empty($settings['theme'])) {
            $embed_url = add_query_arg('theme', $settings['theme'], $embed_url);
        }

        ?>
        <div class="spotiembed">
            <iframe 
                style="border-radius:12px"
                src="<?php echo esc_url($embed_url); ?>"
                width="<?php echo esc_attr($settings['embed_width']['size']) . esc_attr($settings['embed_width']['unit']); ?>" 
                height="<?php echo esc_attr($height); ?>"
                frameborder="0" 
                allowfullscreen="" 
                allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" 
                loading="lazy">
            </iframe>
        </div>
        <?php
    }

    /**
     * Render widget output in the editor
     */
    protected function content_template() {
        ?>
        <#
        var spotifyUrl = settings.spotify_url;
        
        if (typeof spotifyUrl === 'object') {
            spotifyUrl = spotifyUrl.url || spotifyUrl.value || '';
        }

        if (!spotifyUrl && settings.fallback_url) {
            spotifyUrl = settings.fallback_url;
        }

        var height = settings.embed_height || '152';

        if (spotifyUrl) { 
            var embedUrl = spotifyUrl.replace('open.spotify.com', 'open.spotify.com/embed');
            if (embedUrl.indexOf('/embed/') === -1) {
                embedUrl = embedUrl.replace('spotify.com', 'spotify.com/embed');
            }

            // Add theme parameter if dark theme is selected
            if (settings.theme) {
                embedUrl += (embedUrl.indexOf('?') === -1 ? '?' : '&') + 'theme=' + settings.theme;
            }
        #>
        <div class="spotiembed">
            <iframe 
                style="border-radius:12px"
                src="{{{ embedUrl }}}"
                width="{{{ settings.embed_width.size }}}{{{ settings.embed_width.unit }}}"
                height="{{{ height }}}"
                frameborder="0" 
                allowfullscreen="" 
                allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" 
                loading="lazy">
            </iframe>
        </div>
        <# } #>
        <?php
    }
}