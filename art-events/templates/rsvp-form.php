<?php 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>  
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="<?php echo isset($user_rsvp) && $user_rsvp['rsvp_status'] === self::RSVP_YES ? 'update_rsvp' : 'save_rsvp'; ?>">
    <input type="hidden" name="event_id" value="<?php echo get_the_ID(); ?>">
<?php if (isset($user_rsvp) && $user_rsvp['rsvp_status'] === self::RSVP_YES) : ?>
        <input type="hidden" name="rsvp_id" value="<?php echo esc_attr($user_rsvp['rsvp_id']); ?>">
    <?php endif; ?>
    <?php wp_nonce_field(isset($user_rsvp) && $user_rsvp['rsvp_status'] === self::RSVP_YES ? 'update_rsvp_action' : 'save_rsvp_action', 'rsvp_nonce'); ?>
    <p>
        <label for="first_name"><?php _e('First Name:', 'art-events'); ?></label>
        <input type="text" id="first_name" name="first_name" value="<?php echo isset($posted_data['first_name']) ? esc_attr($posted_data['first_name']) : (isset($user_rsvp) ? esc_attr($user_rsvp['first_name']) : ''); ?>" required />
    </p>
    <p>
        <label for="last_name"><?php _e('Last Name:', 'art-events'); ?></label>
        <input type="text" id="last_name" name="last_name" value="<?php echo isset($posted_data['last_name']) ? esc_attr($posted_data['last_name']) : (isset($user_rsvp) ? esc_attr($user_rsvp['last_name']) : ''); ?>" required />
    </p>
    <p>
        <label for="email"><?php _e('Email:', 'art-events'); ?></label>
        <input type="email" id="email" name="email" value="<?php echo isset($posted_data['email']) ? esc_attr($posted_data['email']) : (isset($user_rsvp) ? esc_attr($user_rsvp['email']) : ''); ?>" required />
    </p>
    <p>
        <label for="number_of_people"><?php _e('Number of People:', 'art-events'); ?></label>
        <input type="number" id="number_of_people" name="number_of_people" min="1" max="2" required value="<?php echo isset($posted_data['number_of_people']) ? esc_attr($posted_data['number_of_people']) : (isset($user_rsvp) ? esc_attr($user_rsvp['number_of_people']) : '1'); ?>" />
    </p>
    <p>
        <button type="submit" name="submit_rsvp">
            <?php echo isset($user_rsvp) && $user_rsvp['rsvp_status'] === self::RSVP_YES ? __('Update RSVP', 'art-events') : __('Submit RSVP', 'art-events'); ?>
        </button>
    </p>
</form>

<!-- Cancel RSVP -->
<?php if (isset($user_rsvp) && $user_rsvp['rsvp_status'] === self::RSVP_YES) : ?>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="cancel_rsvp">
        <input type="hidden" name="rsvp_id" value="<?php echo esc_attr($user_rsvp['rsvp_id']); ?>">
        <?php wp_nonce_field('cancel_rsvp_action', 'cancel_rsvp_nonce'); ?>
        <p>
            <button type="submit" name="cancel_rsvp"><?php _e('Cancel RSVP', 'art-events'); ?></button>
        </p>
    </form>
<?php endif; ?>