<?php
/**
 * Plugin Name: Art Events
 * Plugin URI: https://digitaldots.com.mm
 * Description: A plugin to manage art events with custom post type and meta boxes
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://digitaldots.com.mm
 * Text Domain: art-events
 * Domain Path: /languages
 */

namespace ArtEvents;

use ArtEvents\EventPostType;
use ArtEvents\EventMetaBox;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('ART_EVENTS_VERSION', '1.0.0');
define('ART_EVENTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ART_EVENTS_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once ART_EVENTS_PLUGIN_DIR . 'vendor/autoload.php';

class Art_Events {
    /**
     * Instance of this class
     *
     * @var Art_Events
     */
    private static $instance = null;

    /**
     * Post type instance
     *
     * @var Art_Event_Post_Type
     */
    private $post_type;

    /**
     * Meta box instance
     *
     * @var Art_Event_Meta_Box
     */
    private $meta_box;

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        // Include required files
        // $includes = [
        //     'includes/post-type/class-event-post-type.php',
        //     'includes/meta-box/class-event-meta-box.php'
        // ];

        // foreach ($includes as $file) {
        //     $file_path = ART_EVENTS_PLUGIN_DIR . $file;
        //     if (file_exists($file_path)) {
        //         require_once $file_path;
        //     }
        // }

        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        $this->init_hooks();
    }

    /**
     * Get the singleton instance
     *
     * @return Art_Events
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Initialize instances
        EventPostType::get_instance();        
        EventMetaBox::get_instance();
        Events::get_instance();
        RSVPPostType::get_instance();
        RSVPMetaBox::get_instance();
        RSVPHandler::get_instance();
    }

    public function enqueue_admin_assets(): void
    {
        wp_enqueue_script(
            'art-events-admin-script',
            ART_EVENTS_PLUGIN_URL . 'assets/js/admin.js',
            [],
            filemtime(ART_EVENTS_PLUGIN_DIR . 'assets/js/admin.js'),
            true
        );
        wp_enqueue_style(
            'art-events-admin-style',
            ART_EVENTS_PLUGIN_URL . 'assets/css/admin.css',
            [],
            filemtime(ART_EVENTS_PLUGIN_DIR . 'assets/css/admin.css')
        );
    }
}

// Initialize the plugin
add_action('plugins_loaded', function() {
    Art_Events::get_instance();
});
