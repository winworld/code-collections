<?php
namespace ArtEvents;

class RSVPHandler
{
    private static $instance = null;
    const RSVP_YES = 'yes';
    const RSVP_NO = 'no';

    private function __construct()
    {
        // Add RSVP form to the content of the event post type
        add_filter('the_content', [$this, 'display_rsvp_form'], 10, 1);

        add_filter('pre_post_update', [$this, 'handle_admin_rsvp_update'], 10, 2);
        add_action('admin_notices', [$this, 'display_custom_error_message']);

        // Handle RSVP is sent to trash
        add_action('trashed_post', [$this, 'remove_rsvp_on_trash'], 10, 1);      

        // Add admin_post hooks for RSVP actions
        add_action('admin_post_save_rsvp', [$this, 'handle_rsvp']);
        add_action('admin_post_nopriv_save_rsvp', [$this, 'handle_rsvp']);
        add_action('admin_post_update_rsvp', [$this, 'handle_rsvp']);
        add_action('admin_post_nopriv_update_rsvp', [$this, 'handle_rsvp']);
        add_action('admin_post_cancel_rsvp', [$this, 'cancel_rsvp']);
        add_action('admin_post_nopriv_cancel_rsvp', [$this, 'cancel_rsvp']);

        // admin columns for RSVP
        add_filter('manage_rsvp_posts_columns', [$this, 'set_custom_edit_rsvp_columns']);
        add_action('manage_rsvp_posts_custom_column', [$this, 'custom_rsvp_column'], 10, 2);
    }

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function handle_rsvp()
    {
        // Verify nonce
        if (!isset($_POST['rsvp_nonce']) || !wp_verify_nonce($_POST['rsvp_nonce'], isset($_POST['rsvp_id']) ? 'update_rsvp_action' : 'save_rsvp_action')) {
            wp_die(__('Invalid nonce verification.', 'art-events'));
        }

        // Validate required fields
        if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['email']) || empty($_POST['number_of_people'])) {
            wp_die(__('Please fill in all required fields.', 'art-events'));
        }

        // Sanitize input data
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);
        $number_of_people = intval($_POST['number_of_people']);
        $event_id = intval($_POST['event_id']);
        $user_id = get_current_user_id();

        // Check if this is an update or a new RSVP
        $is_update = isset($_POST['rsvp_id']) && !empty($_POST['rsvp_id']);
        $rsvp_id = $is_update ? intval($_POST['rsvp_id']) : null;

        // Get current RSVP count and user RSVP count
        $max_capacity = intval(get_post_meta($event_id, 'max_capacity', true));
        $current_rsvp_count = intval(get_post_meta($event_id, 'current_rsvp_count', true));
        $user_rsvp_count = $is_update ? intval(get_post_meta($rsvp_id, 'number_of_people', true)) : 0;

        // Calculate the new RSVP count
        $new_rsvp_count = ($current_rsvp_count - $user_rsvp_count) + $number_of_people;

        // Validate against max capacity
        if ($new_rsvp_count > $max_capacity) {
            SiteHelper::redirect_with_message(wp_get_referer(), __('Sorry, the event is at full capacity.', 'art-events'), 'error');
        }

        if ($is_update) {
            // Update existing RSVP

        } else {
            // Create new RSVP
            $rsvp_id = wp_insert_post([
                'post_type' => 'rsvp',
                'post_title' => $first_name . ' ' . $last_name,
                'post_status' => 'publish',
            ]);

            if ($rsvp_id) {
                update_post_meta($rsvp_id, 'event_id', $event_id);
                update_post_meta($rsvp_id, 'user_id', $user_id);
                update_post_meta($rsvp_id, 'rsvp_status', self::RSVP_YES);
            }
        }
        // Update RSVP meta data
        update_post_meta($rsvp_id, 'first_name', $first_name);
        update_post_meta($rsvp_id, 'last_name', $last_name);
        update_post_meta($rsvp_id, 'email', $email);
        update_post_meta($rsvp_id, 'number_of_people', $number_of_people);

        // Update current RSVP count
        update_post_meta($event_id, 'current_rsvp_count', $new_rsvp_count);

        $event_title = get_the_title($event_id);
        $event_start_date = get_post_meta($event_id, 'event_start_date', true);
        $event_start_time = get_post_meta($event_id, 'event_start_time', true);
        $event_end_date = get_post_meta($event_id, 'event_end_date', true);
        $event_end_time = get_post_meta($event_id, 'event_end_time', true);

        // Prepare email data
        $email_data = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'event_title' => $event_title,
            'event_start_date' => $event_start_date,
            'event_start_time' => $event_start_time,  
            'event_end_date' => $event_end_date,
            'event_end_time' => $event_end_time,               
            'number_of_people' => $number_of_people,
            'action' => 'created',
        ];
        
        // If this is an update, set the action to 'updated'
        if($is_update) {
            $email_data['action'] = 'updated';
        }        
        // Send notification email
        do_action('art_events_send_notification', $email_data, 'email');

        // Redirect back with success message
        $message = $is_update ? __('RSVP\'ed is successfully updated', 'art-events') : __('RSVP\'ed is successfully made', 'art-events');
        SiteHelper::redirect_with_message(wp_get_referer(), $message, 'success');
    }

    public function cancel_rsvp()
    {
        // Verify nonce
        if (!isset($_POST['cancel_rsvp_nonce']) || !wp_verify_nonce($_POST['cancel_rsvp_nonce'], 'cancel_rsvp_action')) {
            wp_die(__('Invalid nonce verification.', 'art-events'));
        }

        // Get RSVP ID and delete it
        $rsvp_id = intval($_POST['rsvp_id']);
        $event_id = get_post_meta($rsvp_id, 'event_id', true);
        $number_of_people = intval(get_post_meta($rsvp_id, 'number_of_people', true));

        // Update event capacity
        $current_rsvp_count = intval(get_post_meta($event_id, 'current_rsvp_count', true));
        update_post_meta($event_id, 'current_rsvp_count', $current_rsvp_count - $number_of_people);

        // Delete RSVP post
        // wp_delete_post($rsvp_id, true);
        update_post_meta($rsvp_id, 'rsvp_status', self::RSVP_NO);

        // Redirect back with success message
        SiteHelper::redirect_with_message(wp_get_referer(), 'Sorry to see you go', 'success', true);
    }

    public function remove_rsvp_on_trash($post_id)
    {
        // Check if the post type is 'rsvp'
        if (get_post_type($post_id) !== 'rsvp') {
            return;
        }

        $rsvp_status = get_post_meta($post_id, 'rsvp_status', true);
        if($rsvp_status === self::RSVP_NO) {
            return;
        }

        // Get the event ID from the RSVP post meta
        $event_id = get_post_meta($post_id, 'event_id', true);
        $number_of_people = intval(get_post_meta($post_id, 'number_of_people', true));

        // Update the event's current RSVP count
        $current_rsvp_count = intval(get_post_meta($event_id, 'current_rsvp_count', true));
        update_post_meta($event_id, 'current_rsvp_count', $current_rsvp_count - $number_of_people);

        // Delete the RSVP post
        wp_delete_post($post_id, true);
    }

    public function display_rsvp_form($content)
    {
        if (is_single() && get_post_type() === 'art-event') {
            // Display flash messages (success or error)
            SiteHelper::display_flash_message($content);

            if (!is_user_logged_in()) {
                $login_url = wp_login_url(get_permalink());
                $content .= '<p>' . __('You need to be logged in to RSVP for this event.', 'art-events') . '</p>';
                $content .= '<p><a href="' . esc_url($login_url) . '">' . __('Click here to log in.', 'art-events') . '</a></p>';
                return $content;
            }

            // Add a link to the user's profile page
            $user_id = get_current_user_id();
            $user_profile_url = get_edit_user_link($user_id);
            $content .= '<p>' . sprintf(__('Logged in as <a href="%s">your profile</a>.', 'art-events'), esc_url($user_profile_url)) . '</p>';

            ob_start();

            $user_rsvp = $this->get_user_rsvp_for_event(get_the_ID());

            if ($user_rsvp) {
                $rsvp_status = get_post_meta($user_rsvp['rsvp_id'], 'rsvp_status', true);
                if ($rsvp_status === 'yes') {
                    $content .= '<p>' . __('You have already RSVP\'d for this event.', 'art-events') . '</p>';
                } else {
                    $content .= '<p>' . __('You have cancelled your RSVP. You can resubmit your RSVP below.', 'art-events') . '</p>';
                }
            } else {
                $content .= '<p>' . __('Enter your details below to RSVP.', 'art-events') . '</p>';
            }
            $posted_data = SiteHelper::get_transient_data('posted_data');
            include ART_EVENTS_PLUGIN_DIR . 'templates/rsvp-form.php';
            $content .= ob_get_clean();
        }
        return $content;
    }
    public function handle_admin_rsvp_update($post_id, $post)
    {

        // Check if the post type is 'rsvp'
        if ($post['post_type'] !== 'rsvp') {
            return;
        }

        // Verify if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Verify user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Sanitize and update RSVP meta fields
        if (isset($_POST['first_name'])) {
            update_post_meta($post_id, 'first_name', sanitize_text_field($_POST['first_name']));
        }
        if (isset($_POST['last_name'])) {
            update_post_meta($post_id, 'last_name', sanitize_text_field($_POST['last_name']));
        }
        if (isset($_POST['email'])) {
            update_post_meta($post_id, 'email', sanitize_email($_POST['email']));
        }
        if (isset($_POST['number_of_people'])) {
            $number_of_people = intval($_POST['number_of_people']);
            update_post_meta($post_id, 'number_of_people', $number_of_people);

            // Update the event's RSVP count
            $event_id = get_post_meta($post_id, 'event_id', true);
            if ($event_id) {
                $max_capacity = intval(get_post_meta($event_id, 'max_capacity', true));
                $current_rsvp_count = intval(get_post_meta($event_id, 'current_rsvp_count', true));
                $previous_rsvp_count = intval(get_post_meta($post_id, '_previous_rsvp_count', true));

                // If this is a new RSVP, initialize the previous RSVP count to 0
                if (!$previous_rsvp_count) {
                    $previous_rsvp_count = 0;
                }

                // Calculate the new RSVP count
                $new_rsvp_count = $current_rsvp_count - $previous_rsvp_count + $number_of_people;

                // Validate against max capacity
                if ($new_rsvp_count > $max_capacity) {
                    set_transient('rsvp_update_error_message', 'Error: Capacity is full!', 30);
                    return false;
                }
                // Update the RSVP count for the event
                update_post_meta($event_id, 'current_rsvp_count', $new_rsvp_count);

                // Save the new RSVP count for this RSVP
                update_post_meta($post_id, '_previous_rsvp_count', $number_of_people);
            }
        }
    }

    public function display_custom_error_message()
    {
        // Check if the transient with the error message exists
        if ($message = get_transient('rsvp_update_error_message')) {
            // Display the error message
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($message) . '</p></div>';

            // Delete the transient after displaying it
            delete_transient('rsvp_update_error_message');
        }
    }

    public function set_custom_edit_rsvp_columns($columns): array
    {
        // Remove the default title column and add custom columns
        unset($columns['title']);

        $columns = [
            'cb' => '<input type="checkbox" />',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'number_of_people' => 'Number of People',
            'event_name' => 'Event Name',
            'rsvp_status' => 'RSVP Status',
            'rsvp_date' => 'Date',
        ];
        return $columns;
    }

    public function custom_rsvp_column($column, $post_id): void
    {
        switch ($column) {
            case 'first_name':
                echo esc_html(get_post_meta($post_id, 'first_name', true));
                break;
            case 'last_name':
                echo esc_html(get_post_meta($post_id, 'last_name', true));
                break;
            case 'email':
                $email = get_post_meta($post_id, 'email', true);
                echo '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>';
                break;
            case 'number_of_people':
                echo esc_html(get_post_meta($post_id, 'number_of_people', true));
                break;
            case 'rsvp_status':
                $rsvp_status = get_post_meta($post_id, 'rsvp_status', true);
                echo esc_html(ucfirst($rsvp_status));
                break;
            case 'event_name':
                $event_id = get_post_meta($post_id, 'event_id', true);
                if ($event_id) {
                    $event_title = get_the_title($event_id);
                    $event_link = get_edit_post_link($event_id);
                    echo '<a href="' . esc_url($event_link) . '">' . esc_html($event_title) . '</a>';
                } else {
                    echo 'N/A';
                }
                break;
            case 'rsvp_date':
                echo get_the_date('d/m/Y H:i:s', $post_id);
                break;
        }
    }

    public function get_user_rsvp_for_event($event_id)
    {
        // Check if the user is logged in
        $user_id = get_current_user_id();
        if (!$user_id) {
            return null; // User is not logged in
        }

        // Query for the RSVP post
        $args = [
            'post_type' => 'rsvp',
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => 'event_id',
                    'value' => $event_id,
                    'compare' => '='
                ],
                [
                    'key' => 'user_id',
                    'value' => $user_id,
                    'compare' => '='
                ],
                [
                    'key' => 'rsvp_status',
                    'value' => self::RSVP_YES,
                    'compare' => '='
                ],
            ]
        ];

        $query = new \WP_Query($args);

        // Return the RSVP post if found
        if ($query->have_posts()) {
            $query->the_post();
            $rsvp_id = get_the_ID();
            $rsvp = [
                'rsvp_id' => $rsvp_id,
                'first_name' => get_post_meta($rsvp_id, 'first_name', true),
                'last_name' => get_post_meta($rsvp_id, 'last_name', true),
                'email' => get_post_meta($rsvp_id, 'email', true),
                'number_of_people' => get_post_meta($rsvp_id, 'number_of_people', true),
                'rsvp_status' => get_post_meta($rsvp_id, 'rsvp_status', true),
            ];
            wp_reset_postdata();
        } else {
            // Get current logged in user details   
            $user = wp_get_current_user();
            $rsvp = [
                'rsvp_id' => null,
                'first_name' => $user->user_firstname,
                'last_name' => $user->user_lastname,
                'email' => $user->user_email,
                'number_of_people' => 1,
                'rsvp_status' => null,
            ];
        }
        return $rsvp;
    }
}
