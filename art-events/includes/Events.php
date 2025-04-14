<?php
namespace ArtEvents;

class Events {
    private static $instance = null;

    private function __construct() {
        add_filter('template_include', [$this, 'load_single_event_template']);
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function load_single_event_template($template) {
        if (is_singular('art-event')) {
            $custom_template = ART_EVENTS_PLUGIN_DIR . 'templates/single-art-event.php';            

            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }
}


