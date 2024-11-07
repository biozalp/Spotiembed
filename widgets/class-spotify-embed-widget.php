<?php
/**
 * Spotify Embed Widget Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Spotify_Embed_Widget extends \Elementor\Widget_Base {
    /**
     * Get widget name
     */
    public function get_name() {
        return 'spotify_embed';
    }

    /**
     * Get widget title
     */
    public function get_title() {
        return esc_html__('Spotify Embed', 'spotify-embed-elementor');
    }

    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-code';
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

    /**
     * Register widget controls
     */
    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Content', 'spotify-embed-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'spotify_url',
            [
                'label' => esc_html__('Spotify URL', 'spotify-embed-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'dynamic' => [
                    'active' => true,
                    'categories' => [
                        \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
                        \Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
                        \Elementor\Modules\DynamicTags\Module::POST_META_CATEGORY,
                    ],
                ],
                'placeholder' => esc_html__('Enter Spotify URL', 'spotify-embed-elementor'),
                'description' => esc_html__('Enter the URL of a Spotify track, album, or playlist. Supports ACF fields.', 'spotify-embed-elementor'),
            ]
        );

        // Add fallback URL control
        $this->add_control(
            'fallback_url',
            [
                'label' => esc_html__('Fallback URL', 'spotify-embed-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__('Enter fallback Spotify URL', 'spotify-embed-elementor'),
                'description' => esc_html__('This URL will be used if the dynamic URL is empty', 'spotify-embed-elementor'),
                'condition' => [
                    'spotify_url[url]!' => '',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            [
                'label' => esc_html__('Style', 'spotify-embed-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'embed_height',
            [
                'label' => esc_html__('Height', 'spotify-embed-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 80,
                        'max' => 500,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 352,
                ],
                'selectors' => [
                    '{{WRAPPER}} .spotify-embed-container iframe' => 'height: {{SIZE}}{{UNIT}};',
                ],
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

        // Add error handling class
        $container_class = 'spotify-embed-container';
        if (!wp_http_validate_url($embed_url)) {
            $container_class .= ' spotify-embed-error';
        }
        ?>
        <div class="<?php echo esc_attr($container_class); ?>">
            <iframe 
                src="<?php echo $embed_url; ?>"
                width="100%" 
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
        
        // Handle ACF field values
        if (typeof spotifyUrl === 'object') {
            spotifyUrl = spotifyUrl.url || spotifyUrl.value || '';
        }

        // Use fallback if main URL is empty
        if (!spotifyUrl && settings.fallback_url) {
            spotifyUrl = settings.fallback_url;
        }

        if (spotifyUrl) { 
            var embedUrl = spotifyUrl.replace('open.spotify.com', 'open.spotify.com/embed');
            if (embedUrl.indexOf('/embed/') === -1) {
                embedUrl = embedUrl.replace('spotify.com', 'spotify.com/embed');
            }
        #>
            <div class="spotify-embed-container">
                <iframe 
                    src="{{ embedUrl }}"
                    width="100%" 
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