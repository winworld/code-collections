<?php
namespace ArtEvents;

class RSVPMetaBox {
    private static $instance = null;
    private $meta_fields = [
        'first_name'       => 'First Name',
        'last_name'        => 'Last Name',
        'email'            => 'Email',
        'number_of_people' => 'Number of People',
        'event_id'         => 'Event ID',
    ];

    private function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('save_post', [$this, 'save_meta_box']);
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function add_meta_box() {
        add_meta_box(
            'art_rsvp_meta_box',
            __('RSVP Details', 'art-events'),
            [$this, 'render_meta_box'],
            'rsvp',
            'normal',
            'high'
        );
    }

    public function render_meta_box($post) {
        wp_nonce_field('art_rsvp_meta_box', 'art_rsvp_meta_box_nonce');
        ?>
        <div class="art-rsvp-meta-box">
            <?php foreach ($this->meta_fields as $key => $label): ?>
                <p>
                    <label for="<?php echo esc_attr($key); ?>"><?php echo esc_html__($label, 'art-events'); ?>:</label>
                    <input 
                        type="<?php echo $key === 'email' ? 'email' : ($key === 'number_of_people' || $key === 'event_id' ? 'number' : 'text'); ?>" 
                        id="<?php echo esc_attr($key); ?>" 
                        name="<?php echo esc_attr($key); ?>" 
                        value="<?php echo esc_attr(get_post_meta($post->ID, $key, true)); ?>" 
                        class="widefat" 
                    />
                </p>
            <?php endforeach; ?>
        </div>
        <?php
    }

    public function save_meta_box($post_id) {        
        if (!isset($_POST['art_rsvp_meta_box_nonce'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['art_rsvp_meta_box_nonce'], 'art_rsvp_meta_box')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save RSVP meta data
        foreach ($this->meta_fields as $key => $label) {
            if (isset($_POST[$key])) {
                $value = sanitize_text_field($_POST[$key]);
                if ($key === 'email') {
                    $value = sanitize_email($_POST[$key]);
                } elseif ($key === 'number_of_people' || $key === 'event_id') {
                    $value = intval($_POST[$key]);
                }
                update_post_meta($post_id, $key, $value);
            }
        }
    }
}

// Initialize the class
RSVPMetaBox::get_instance();
