<?php

namespace ArtEvents;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class PortalSettings
{
    private static $instance = null;

    // Define a constant for the settings prefix
    const OPTION_PREFIX = '_art_options_';

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_menu_pages']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
    }

    /**
     * Add menu and submenu pages.
     */
    public function add_menu_pages()
    {
        // Main menu page
        add_menu_page(
            __('Theme Options', 'art-events'),
            __('Theme Options', 'art-events'),
            'manage_options',
            'theme-options',
            [$this, 'render_settings_page'],
            'dashicons-admin-generic',
            20
        );
    }

    /**
     * Register settings for each module.
     */
    public function register_settings()
    {
        $settings = [
            'general' => [$this, 'sanitize_general_settings'],
            'email' => [$this, 'sanitize_email_settings'],
        ];

        foreach ($settings as $key => $sanitize_callback) {
            register_setting(
                self::OPTION_PREFIX . $key . '_group',
                self::OPTION_PREFIX . $key,
                ['sanitize_callback' => $sanitize_callback]
            );
        }
    }

    /**
     * Enqueue custom admin styles for the settings page.
     */
    public function enqueue_admin_styles()
    {
        wp_add_inline_style('wp-admin', $this->get_custom_css());
    }

    /**
     * Render the settings page with vertical tabs.
     */
    public function render_settings_page()
    {
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Theme Options', 'art-events'); ?></h1>
            <div class="art-events-settings-container">
                <!-- Vertical Tabs -->
                <div class="art-events-settings-tabs">
                    <a href="?page=theme-options&tab=general"
                        class="art-events-tab <?php echo $active_tab === 'general' ? 'active' : ''; ?>">
                        <?php esc_html_e('General Settings', 'art-events'); ?>
                    </a>
                    <a href="?page=theme-options&tab=email"
                        class="art-events-tab <?php echo $active_tab === 'email' ? 'active' : ''; ?>">
                        <?php esc_html_e('Email Settings', 'art-events'); ?>
                    </a>
                </div>

                <!-- Tab Content -->
                <div class="art-events-settings-content">
                    <?php
                    if ($active_tab === 'general') {
                        $this->render_settings_form('general');
                    } elseif ($active_tab === 'email') {
                        $this->render_settings_form('email');
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render a settings form dynamically based on the key.
     *
     * @param string $key The settings key (e.g., 'general', 'email').
     */
    private function render_settings_form($key)
    {
        $saved_settings = get_option(self::OPTION_PREFIX . $key, []);
        $default_settings = self::get_default_settings()[$key];
        $options = array_merge($default_settings, $saved_settings);

        ?>
        <form method="post" action="options.php">
            <?php settings_fields(self::OPTION_PREFIX . $key . '_group'); ?>
            <table class="form-table">
                <?php foreach ($options as $field_key => $field_value): ?>
                    <tr>
                        <th scope="row"><?php echo esc_html(ucwords(str_replace('_', ' ', $field_key))); ?></th>
                        <td>
                            <?php if (in_array($field_key, ['site_description', 'content'], true)): ?>
                                <!-- Render as textarea for specific keys -->
                                <textarea name="<?php echo self::OPTION_PREFIX . $key . "[$field_key]"; ?>" rows="5"
                                    class="large-text"><?php echo esc_textarea($field_value); ?></textarea>
                            <?php else: ?>
                                <!-- Render as text input for other keys -->
                                <input type="text" name="<?php echo self::OPTION_PREFIX . $key . "[$field_key]"; ?>"
                                    value="<?php echo esc_attr($field_value); ?>" class="regular-text">
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php submit_button(); ?>
        </form>
        <?php
    }

    /**
     * Sanitize general settings.
     */
    public function sanitize_general_settings($input)
    {
        return [
            'site_name' => sanitize_text_field($input['site_name'] ?? ''),
            'site_description' => wp_kses_post($input['site_description'] ?? ''),
        ];
    }

    /**
     * Sanitize email settings.
     */
    public function sanitize_email_settings($input)
    {
        return [
            'from_name' => sanitize_text_field($input['from_name'] ?? ''),
            'from_email' => sanitize_email($input['from_email'] ?? ''),
            'subject' => sanitize_text_field($input['subject'] ?? ''),
            'content' => wp_kses_post($input['content'] ?? ''),
        ];
    }

    /**
     * Get default settings.
     *
     * @return array
     */
    public static function get_default_settings(): array
    {
        return [
            'general' => [
                'site_name' => 'Art Events',
                'site_description' => 'Welcome to Art Events!',
            ],
            'email' => [
                'from_name' => 'Art Events',
                'from_email' => 'noreply@artevents.com',
                'subject' => 'RSVP Confirmation',
                'content' => 'Hello {first_name}, your RSVP for {event_title} has been confirmed.',
            ],
        ];
    }

    /**
     * Custom CSS for vertical tabs.
     */
    private function get_custom_css()
    {
        return "
            .art-events-settings-container {
                display: flex;
                gap: 20px;
            }
            .art-events-settings-tabs {
                width: 200px;
                border-right: 1px solid #ccc;
            }
            .art-events-tab {
                display: block;
                padding: 10px 15px;
                text-decoration: none;
                color: #555;
                border-left: 3px solid transparent;
            }
            .art-events-tab:hover {
                background-color: #f1f1f1;
            }
            .art-events-tab.active {
                background-color: #fff;
                border-left-color: #007cba;
                font-weight: bold;
                color: #007cba;
            }
            .art-events-settings-content {
                flex-grow: 1;
            }
        ";
    }
}

// Initialize the class
add_action('init', function () {
    PortalSettings::get_instance();
});
