<?php
namespace ArtEvents;

class EventMetaBox {
    private static $instance = null;
    private $meta_box_id = 'art_events_meta_box';
    private $meta_fields = [
        'event_start_date' => 'Event Start Date',
        'event_start_time' => 'Event Start Time',
        'event_end_date' => 'Event End Date',
        'event_end_time' => 'Event End Time',
        'event_color' => 'Event Color', 
        'max_capacity' => 'Max Capacity',
        'max_people_per_rsvp' => 'Max People Per RSVP',
    ];

    private function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('save_post', [$this, 'save_meta_box']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function enqueue_admin_scripts() {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    }

    public function add_meta_box() {
        add_meta_box(
            $this->meta_box_id,
            __('Event Details', 'art-events'),
            [$this, 'render_meta_box'],
            'art-event',
            'normal',
            'high'
        );
    }

    public function render_meta_box($post) {
        wp_nonce_field('art_events_meta_box', 'art_events_meta_box_nonce');

        $event_start_date = get_post_meta($post->ID, 'event_start_date', true);
        $event_start_time = get_post_meta($post->ID, 'event_start_time', true);
        $event_end_date = get_post_meta($post->ID, 'event_end_date', true);
        $event_end_time = get_post_meta($post->ID, 'event_end_time', true);
        $event_color = get_post_meta($post->ID, 'event_color', true);
        $max_capacity = get_post_meta($post->ID, 'max_capacity', true);
        $max_people_per_rsvp = get_post_meta($post->ID, 'max_people_per_rsvp', true);
        ?>
        <div class="art-events-meta-box">
            <h4><?php _e('Event Start', 'art-events'); ?></h4>
            <p>
                <label for="event_start_date"><?php _e('Start Date:', 'art-events'); ?></label>
                <input type="date" id="event_start_date" name="event_start_date" 
                    value="<?php echo esc_attr($event_start_date); ?>" class="widefat" />
            </p>
            <p>
                <label for="event_start_time"><?php _e('Start Time:', 'art-events'); ?></label>
                <input type="time" id="event_start_time" name="event_start_time" 
                    value="<?php echo esc_attr($event_start_time); ?>" class="widefat" />
            </p>

            <h4><?php _e('Event End', 'art-events'); ?></h4>
            <p>
                <label for="event_end_date"><?php _e('End Date:', 'art-events'); ?></label>
                <input type="date" id="event_end_date" name="event_end_date" 
                    value="<?php echo esc_attr($event_end_date); ?>" class="widefat" />
            </p>
            <p>
                <label for="event_end_time"><?php _e('End Time:', 'art-events'); ?></label>
                <input type="time" id="event_end_time" name="event_end_time" 
                    value="<?php echo esc_attr($event_end_time); ?>" class="widefat" />
            </p>

            <h4><?php _e('Event Color', 'art-events'); ?></h4>
            <p>
                <label for="event_color"><?php _e('Color:', 'art-events'); ?></label>
                <input type="text" id="event_color" name="event_color" 
                    value="<?php echo esc_attr($event_color); ?>" class="color-picker" />
            </p>
            <h4><?php _e('RSVP Settings', 'art-events'); ?></h4>
            <p>
                <label for="max_capacity"><?php _e('Max Capacity:', 'art-events'); ?></label>
                <input type="number" id="max_capacity" name="max_capacity" 
                    value="<?php echo esc_attr($max_capacity); ?>" class="widefat" />
            </p>    
            <p>
                <label for="max_people_per_rsvp"><?php _e('Max People Per RSVP:', 'art-events'); ?></label>
                <input type="number" id="max_people_per_rsvp" name="max_people_per_rsvp" 
                    value="<?php echo esc_attr($max_people_per_rsvp); ?>" class="widefat" />
            </p>
        </div>
        <?php
    }

    public function save_meta_box($post_id) {
        if (!isset($_POST['art_events_meta_box_nonce'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['art_events_meta_box_nonce'], 'art_events_meta_box')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        foreach ($this->meta_fields as $field => $label) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
    }
} 