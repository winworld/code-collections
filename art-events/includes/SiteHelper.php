<?php
namespace ArtEvents;

class SiteHelper
{
    private static $instance = null;

    private function __construct()
    {
        // Add any initialization code here
    }

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Redirect with a success or error message
     *
     * @param string $url The URL to redirect to.
     * @param string $message The message to flash.
     * @param string $type The type of message: 'success' or 'error'.
     */
    public static function  redirect_with_message($url, $message, $type = 'success')
    {
        // Set the transient key based on the type of message
        $transient_key = ($type === 'error') ? 'error_message' : 'success_message';

        // Store the message in a transient (temporary option)
        set_transient($transient_key, $message, 30); // Message expires after 30 seconds

        // Store the form data in a transient
        set_transient('posted_data', $_POST, 30); // Expire after 30 seconds

        // Redirect to the desired URL
        wp_redirect($url);
        exit;
    }


    /**
     * Example helper method to format dates.
     *
     * @param string $date The date string.
     * @param string $format The desired format.
     * @return string
     */
    public static function format_date($date, $format = 'Y-m-d')
    {
        $timestamp = strtotime($date);
        return date($format, $timestamp);
    }
   
    public function generate_url($base_url, $params = [])
    {
        return add_query_arg($params, $base_url);
    }
}

// Initialize the class
SiteHelper::get_instance();