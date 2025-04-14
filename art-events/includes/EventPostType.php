<?php
namespace ArtEvents;

class EventPostType {
    private static $instance = null;

    private function __construct() {
        add_action('init', [$this, 'register_post_type']);
        add_action('init', [$this, 'register_taxonomy']);
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register_post_type() {
        $labels = [
            'name'               => _x('Events', 'post type general name', 'art-events'),
            'singular_name'      => _x('Event', 'post type singular name', 'art-events'),
            'menu_name'          => _x('Events', 'admin menu', 'art-events'),
            'name_admin_bar'     => _x('Event', 'add new on admin bar', 'art-events'),
            'add_new'            => _x('Add New', 'event', 'art-events'),
            'add_new_item'       => __('Add New Event', 'art-events'),
            'new_item'           => __('New Event', 'art-events'),
            'edit_item'          => __('Edit Event', 'art-events'),
            'view_item'          => __('View Event', 'art-events'),
            'all_items'          => __('All Events', 'art-events'),
            'search_items'       => __('Search Events', 'art-events'),
            'not_found'          => __('No events found.', 'art-events'),
            'not_found_in_trash' => __('No events found in Trash.', 'art-events')
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'art-event'],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => ['title', 'editor', 'thumbnail', 'excerpt'],
            'menu_icon'          => 'dashicons-calendar-alt'
        ];

        register_post_type('art-event', $args);
    }

    public function register_taxonomy() {
        $labels = [
            'name'              => _x('Event Categories', 'taxonomy general name', 'art-events'),
            'singular_name'     => _x('Event Category', 'taxonomy singular name', 'art-events'),
            'search_items'      => __('Search Event Categories', 'art-events'),
            'all_items'         => __('All Event Categories', 'art-events'),
            'parent_item'       => __('Parent Event Category', 'art-events'),
            'parent_item_colon' => __('Parent Event Category:', 'art-events'),
            'edit_item'         => __('Edit Event Category', 'art-events'),
            'update_item'       => __('Update Event Category', 'art-events'),
            'add_new_item'      => __('Add New Event Category', 'art-events'),
            'new_item_name'     => __('New Event Category Name', 'art-events'),
            'menu_name'         => __('Categories', 'art-events'),
        ];

        $args = [
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'event-category'],
        ];

        register_taxonomy('event_category', ['art-event'], $args);
    }
} 