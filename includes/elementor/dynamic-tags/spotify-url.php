<?php
namespace Spotiembed\Includes\Elementor\Dynamic_Tags;

use Elementor\Core\DynamicTags\Data_Tag;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Spotify_URL extends Data_Tag {
    public function get_name() {
        return 'spotify-url';
    }

    public function get_title() {
        return __('Spotify URL', 'spotiembed');
    }

    public function get_group() {
        return 'media';
    }

    public function get_categories() {
        return ['url'];
    }

    protected function get_value(array $options = []) {
        $post_id = get_the_ID();
        $spotify_url = get_post_meta($post_id, '_spotiembed_url', true);
        
        if (empty($spotify_url)) {
            return '';
        }
        
        return $spotify_url;
    }
}
