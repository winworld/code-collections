<?php
namespace ArtEvents;

class RSVPPostType {
    private static $instance = null;

    private function __construct() {
        add_action('init', [$this, 'register_post_type']);
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register_post_type() {
        $labels = [
            'name'          => __('RSVPs', 'art-events'),
            'singular_name' => __('RSVP', 'art-events'),
        ];

        $args = [
            'labels'        => $labels,
            'public'        => false,
            'show_ui'       => true,
            'supports'      => ['title', 'custom-fields'],
            'capability_type' => 'post',
            'show_in_menu'    => 'edit.php?post_type=art-event',
        ];

        register_post_type('rsvp', $args);
    }
}

// Initialize the class
RSVPPostType::get_instance();