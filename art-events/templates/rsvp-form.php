<?php 
$posted_data = get_transient('posted_data');
?>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="save_rsvp">
    <input type="hidden" name="event_id" value="<?php echo get_the_ID(); ?>">
    <?php wp_nonce_field('save_rsvp_action', 'save_rsvp_nonce'); ?>
    <p>
        <label for="first_name"><?php _e('First Name:', 'art-events'); ?></label>
        <input type="text" id="first_name" name="first_name" value="<?php echo isset($posted_data['first_name']) ? esc_attr($posted_data['first_name']) : ''; ?>" required />
    </p>
    <p>
        <label for="last_name"><?php _e('Last Name:', 'art-events'); ?></label>
        <input type="text" id="last_name" name="last_name" value="<?php echo isset($posted_data['last_name']) ? esc_attr($posted_data['last_name']) : ''; ?>" required />
    </p>
    <p>
        <label for="email"><?php _e('Email:', 'art-events'); ?></label>
        <input type="email" id="email" name="email" value="<?php echo isset($posted_data['email']) ? esc_attr($posted_data['email']) : ''; ?>" required />
    </p>
    <p>
        <label for="number_of_people"><?php _e('Number of People:', 'art-events'); ?></label>
        <input type="number" id="number_of_people" name="number_of_people" value="<?php echo isset($posted_data['number_of_people']) ? esc_attr($posted_data['number_of_people']) : ''; ?>" required />
    </p>
    <p>
        <button type="submit" name="submit_rsvp"><?php _e('Submit RSVP', 'art-events'); ?></button>
    </p>
</form>

<!-- Update RSVP -->
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="update_rsvp">
    <?php wp_nonce_field('update_rsvp_action', 'update_rsvp_nonce'); ?>
    <p>
        <button type="submit" name="update_rsvp"><?php _e('Update RSVP', 'art-events'); ?></button>
    </p>
</form>

<!-- Cancel RSVP -->
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="cancel_rsvp">
    <?php wp_nonce_field('cancel_rsvp_action', 'cancel_rsvp_nonce'); ?>
    <p>
        <button type="submit" name="cancel_rsvp"><?php _e('Cancel RSVP', 'art-events'); ?></button>
    </p>
</form>