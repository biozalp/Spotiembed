<?php
namespace Spotiembed\Includes\Elementor;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Module {
    public function __construct() {
        // Register Dynamic Tag
        add_action('elementor/dynamic_tags/register', function($dynamic_tags) {
            require_once(__DIR__ . '/dynamic-tags/spotify-url.php');
            $dynamic_tags->register(new Dynamic_Tags\Spotify_URL());
        });
    }
}
