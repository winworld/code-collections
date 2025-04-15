<?php
namespace ArtEvents;

class RSVPMetaBox
{
    private static $instance = null;
    private $meta_fields = [
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'email' => 'Email',
        'number_of_people' => 'Number of People',
        'rsvp_status' => 'Status',
        'event_id' => 'Event Name',
        'user_id' => 'User',        
    ];

    private function __construct()
    {
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('save_post', [$this, 'save_meta_box']);
    }

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function add_meta_box()
    {
        add_meta_box(
            'art_rsvp_meta_box',
            __('RSVP Details', 'art-events'),
            [$this, 'render_meta_box'],
            'rsvp',
            'normal',
            'high'
        );
    }


    public function render_meta_box($post)
    {
        wp_nonce_field('art_rsvp_meta_box', 'art_rsvp_meta_box_nonce');
        ?>
        <div class="art-rsvp-meta-box">
            <?php foreach ($this->meta_fields as $key => $label): ?>
                <p>
                    <label for="<?php echo esc_attr($key); ?>"><?php echo esc_html__($label, 'art-events'); ?>:</label>
                    <?php
                    $value = esc_attr(get_post_meta($post->ID, $key, true));
                    switch ($key) {
                        case 'user_id':
                            $rsvp_user_id = $value;
                            if ($rsvp_user_id) {
                                $user_info = get_userdata($rsvp_user_id);
                                if ($user_info) {
                                    $user_edit_link = get_edit_user_link($rsvp_user_id);
                                    echo '<div><a href="' . esc_url($user_edit_link) . '" target="_blank">' . esc_html($user_info->first_name . ' ' . $user_info->last_name) . '</a></div>';
                                } else {
                                    echo '<div>' . __('User not found.', 'art-events') . '</div>';
                                }
                            } else {
                                echo '<div>' . __('No user assigned.', 'art-events') . '</div>';
                            }
                            break;

                        case 'email':
                            echo '<input type="email" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . $value . '" class="widefat" />';
                            break;

                        case 'event_id':
                            $event_id_value = intval($value);
                            if ($event_id_value) {
                                $event_title = get_the_title($event_id_value);
                                $event_link = get_edit_post_link($event_id_value);
                                if ($event_title && $event_link) {
                                    echo '<div><a href="' . esc_url($event_link) . '" target="_blank">' . esc_html($event_title) . '</a></div>';
                                } else {
                                    echo '<div>' . __('Event not found.', 'art-events') . '</div>';
                                }
                            } else {
                                echo '<div>' . __('No event assigned.', 'art-events') . '</div>';
                            }
                            break;

                        case 'rsvp_status':
                            echo '<select id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" class="widefat">';
                            $statuses = [
                                RSVPHandler::RSVP_YES => __('Yes', 'art-events'),
                                RSVPHandler::RSVP_NO => __('No', 'art-events'),
                            ];
                            foreach ($statuses as $status_key => $status_label) {
                                echo '<option value="' . esc_attr($status_key) . '" ' . selected($value, $status_key, false) . '>' . esc_html($status_label) . '</option>';
                            }
                            echo '</select>';
                            break;

                        default:
                            echo '<input type="text" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . $value . '" class="widefat" />';
                            break;
                    }
                    ?>
                </p>
            <?php endforeach; ?>
        </div>
        <?php
    }


    public function save_meta_box($post_id)
    {
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
