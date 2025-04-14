<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rsvp'])) {
    do_action('art_events_handle_rsvp', $_POST);
}



if (have_posts()):
    while (have_posts()):
        the_post();
        $event_start_date = get_post_meta(get_the_ID(), 'event_start_date', true);
        $event_start_time = get_post_meta(get_the_ID(), 'event_start_time', true);
        $event_end_date = get_post_meta(get_the_ID(), 'event_end_date', true);
        $event_end_time = get_post_meta(get_the_ID(), 'event_end_time', true);
        $event_color = get_post_meta(get_the_ID(), 'event_color', true);
        ?>
        <div class="art-event" style="border-left: 5px solid <?php echo esc_attr($event_color); ?>;">
            <h1><?php the_title(); ?></h1>
            <div class="event-details">
                <p><strong><?php _e('Start Date:', 'art-events'); ?></strong> <?php echo esc_html($event_start_date); ?></p>
                <p><strong><?php _e('Start Time:', 'art-events'); ?></strong> <?php echo esc_html($event_start_time); ?></p>
                <p><strong><?php _e('End Date:', 'art-events'); ?></strong> <?php echo esc_html($event_end_date); ?></p>
                <p><strong><?php _e('End Time:', 'art-events'); ?></strong> <?php echo esc_html($event_end_time); ?></p>
            </div>
        </div>
        <?php
        the_content();
    endwhile;
endif;

get_footer();
?>