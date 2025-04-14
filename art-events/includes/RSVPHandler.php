<?php
namespace ArtEvents;

class RSVPHandler
{
    private static $instance = null;

    private function __construct()
    {
        add_filter('manage_rsvp_posts_columns', [$this, 'set_custom_edit_rsvp_columns']);
        add_action('manage_rsvp_posts_custom_column', [$this, 'custom_rsvp_column'], 10, 2);
        add_filter('the_content', [$this, 'display_rsvp_form'], 10, 1);

        add_filter('redirect_post_location', [$this, 'remove_post_updated_message'], 10, 2);
        add_filter('pre_post_update', [$this, 'handle_admin_rsvp_update'], 10, 2);
        add_action('admin_notices', [$this, 'display_custom_error_message']);
        // Add admin_post hooks for RSVP actions
        add_action('admin_post_save_rsvp', [$this, 'save_rsvp']);
        add_action('admin_post_nopriv_save_rsvp', [$this, 'save_rsvp']);
        add_action('admin_post_update_rsvp', [$this, 'update_rsvp']);
        add_action('admin_post_nopriv_update_rsvp', [$this, 'update_rsvp']);
        add_action('admin_post_cancel_rsvp', [$this, 'cancel_rsvp']);
        add_action('admin_post_nopriv_cancel_rsvp', [$this, 'cancel_rsvp']);
    }

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function save_rsvp()
    {
        // Verify nonce
        if (!isset($_POST['save_rsvp_nonce']) || !wp_verify_nonce($_POST['save_rsvp_nonce'], 'save_rsvp_action')) {
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

        // Check event capacity
        $max_capacity = get_post_meta($event_id, 'max_capacity', true);
        $current_rsvp_count = intval(get_post_meta($event_id, 'current_rsvp_count', true));
        if ($current_rsvp_count + $number_of_people > $max_capacity) {
            SiteHelper::redirect_with_message(wp_get_referer(), 'Sorry, the event is at full capacity.', 'error');
        }

        // Insert RSVP post
        $post_id = wp_insert_post([
            'post_type' => 'rsvp',
            'post_title' => $first_name . ' ' . $last_name,
            'post_status' => 'publish',
        ]);

        if ($post_id) {
            // Save RSVP meta fields
            update_post_meta($post_id, 'first_name', $first_name);
            update_post_meta($post_id, 'last_name', $last_name);
            update_post_meta($post_id, 'email', $email);
            update_post_meta($post_id, 'number_of_people', $number_of_people);
            update_post_meta($post_id, 'event_id', $event_id);

            // Update current RSVP count
            update_post_meta($event_id, 'current_rsvp_count', $current_rsvp_count + $number_of_people);
        }

        // Redirect back with success message
        SiteHelper::redirect_with_message(wp_get_referer(), 'RSVP\'ed is successfully made', 'success');

    }

    public function update_rsvp()
    {
        // Verify nonce
        if (!isset($_POST['update_rsvp_nonce']) || !wp_verify_nonce($_POST['update_rsvp_nonce'], 'update_rsvp_action')) {
            wp_die(__('Invalid nonce verification.', 'art-events'));
        }

        // Get RSVP ID and update status
        $rsvp_id = intval($_POST['rsvp_id']);
        update_post_meta($rsvp_id, 'rsvp_status', 'no');

        // Redirect back with success message
        wp_redirect(add_query_arg('rsvp', 'updated', wp_get_referer()));
        exit;
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
        wp_delete_post($rsvp_id, true);

        // Redirect back with success message
        wp_redirect(add_query_arg('rsvp', 'cancelled', wp_get_referer()));
        exit;
    }

    public function display_rsvp_form($content)
    {
        if (is_single() && get_post_type() === 'art-event') {
            // Display success message
            if ($message = get_transient('success_message')) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
                delete_transient('success_message'); // Clear after showing
            }

            // Display error message
            if ($message = get_transient('error_message')) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($message) . '</p></div>';
                delete_transient('error_message'); // Clear after showing
            }


            ob_start();
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

    public function display_custom_error_message() {
        // Check if the transient with the error message exists
        if ($message = get_transient('rsvp_update_error_message')) {
            // Display the error message
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($message) . '</p></div>';
    
            // Delete the transient after displaying it
            delete_transient('rsvp_update_error_message');
        }
    }

    

public function remove_post_updated_message($location, $post_id) {
    // Check if there is an error message set
    // if (get_transient('rsvp_update_error_message')) {
    //     // Modify the location URL to prevent WordPress from displaying the "Post Updated" message
    //     // This will redirect to the edit screen but not trigger a success message        
    //     $location = add_query_arg(array('post' => $post_id, 'message' => 'error'), admin_url('post.php'));
    // }
    return $location;
}

    public function set_custom_edit_rsvp_columns($columns): array
    {
        unset($columns['title']);
        $columns = [
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
            case 'event_name':
                $event_id = get_post_meta($post_id, 'event_id', true);
                if ($event_id) {
                    $event_title = get_the_title($event_id);
                    $event_link = get_edit_post_link($event_id);
                    echo '<a href="' . esc_url($event_link) . '">' . esc_html($event_title) . '</a>';
                }
                break;
            case 'rsvp_date':
                echo get_the_date('Y/m/d H:i:s', $post_id);
                break;
        }
    }
}

// Initialize the class
RSVPHandler::get_instance();