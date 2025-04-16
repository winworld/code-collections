<?php
namespace ArtEvents;

class Notification
{
    private static $instance = null;

    private function __construct()
    {
        // Hook into actions for sending notifications
        add_action('art_events_send_notification', [$this, 'send_notification'], 10, 2);
    }

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Send a notification email
     *
     * @param array $data The data for the notification.
     * @param string $template_key The key for the email template settings.
     */
    public function send_notification($data, $option_key = 'email')
    {
    
        // Get email settings from Options
        $saved_settings = get_option(PortalSettings::OPTION_PREFIX . $option_key, []);
        $default_settings = PortalSettings::get_default_settings()[$option_key] ?? [];
        $options = array_merge($default_settings, $saved_settings);

        // Extract email settings
        $from_name = $options['from_name'] ?? get_bloginfo('name');
        $from_email = $options['from_email'] ?? 'noreply@' . parse_url(get_bloginfo('url'), PHP_URL_HOST);
        $subject = $options['subject'] ?? 'Notification';
        $email_template = $options['content'] ?? 'Hello {first_name}, this is a notification.';

        // Replace placeholders in the email content
        $email_content = $this->replace_placeholders($email_template, $data);

        // Add "Add to Calendar" link if event data is provided
        if (isset($data['event_title'], $data['event_start_date'], $data['event_start_time'], $data['event_end_date'], $data['event_end_time'])) {
            $add_to_calendar_link = $this->generate_add_to_calendar_link(
                $data['event_title'],
                $data['event_start_date'],
                $data['event_start_time'],
                $data['event_end_date'],
                $data['event_end_time']
            );
            $email_content .= "\n\n" . __('Add to Calendar:', 'art-events') . "\n" . $add_to_calendar_link;

            // Add iCal download link
            $ical_download_link = $this->generate_ical_download_link(
                $data['event_title'],
                $data['event_start_date'],
                $data['event_start_time'],
                $data['event_end_date'],
                $data['event_end_time'],
                $data['event_description'] ?? '',
                $data['event_location'] ?? ''
            );
            $email_content .= "\n\n" . __('Download iCal:', 'art-events') . "\n" . $ical_download_link;
        }

        // Send the email
        $headers = [
            'From: ' . $from_name . ' <' . $from_email . '>',
            'Content-Type: text/plain; charset=UTF-8',
        ];
        wp_mail($data['email'], $subject, $email_content, $headers);
    }

    /**
     * Replace placeholders in the email template
     *
     * @param string $template The email template.
     * @param array $data The data to replace placeholders.
     * @return string The processed email content.
     */
    private function replace_placeholders($template, $data)
    {
        $placeholders = [
            '{first_name}' => $data['first_name'] ?? '',
            '{last_name}' => $data['last_name'] ?? '',
            '{event_title}' => $data['event_title'] ?? '',
            '{number_of_people}' => $data['number_of_people'] ?? '',
            '{action}' => $data['action'] ?? '',
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $template);
    }

    /**
     * Generate "Add to Calendar" link
     *
     * @param string $event_title The event title.
     * @param string $event_date The event start date.
     * @param string $event_time The event start time.
     * @param string $event_end_date The event end date.
     * @param string $event_end_time The event end time.
     * @return string The calendar link.
     */
    private function generate_add_to_calendar_link($event_title, $event_date, $event_time, $event_end_date, $event_end_time)
    {
        // Get the timezone from WordPress settings
        $timezone_string = get_option('timezone_string') ?: 'UTC';
        $timezone = new \DateTimeZone($timezone_string);

        // Create DateTime objects for start and end times
        $start_datetime = new \DateTime("$event_date $event_time", $timezone);
        $end_datetime = !empty($event_end_date) && !empty($event_end_time)
            ? new \DateTime("$event_end_date $event_end_time", $timezone)
            : (clone $start_datetime)->modify('+2 hours'); // Default to +2 hours if end time is not provided

        // Format the start and end times in Google Calendar-compatible format (UTC)
        $start_datetime->setTimezone(new \DateTimeZone('UTC'));
        $end_datetime->setTimezone(new \DateTimeZone('UTC'));
        $start_utc = $start_datetime->format('Ymd\THis\Z');
        $end_utc = $end_datetime->format('Ymd\THis\Z');

        // Generate the Google Calendar link
        $calendar_url = add_query_arg([
            'action' => 'TEMPLATE',
            'text' => urlencode($event_title),
            'dates' => $start_utc . '/' . $end_utc,
            'details' => urlencode('Event: ' . $event_title),
            'location' => urlencode(get_bloginfo('name')),
        ], 'https://www.google.com/calendar/render');

        return $calendar_url;
    }

    /**
     * Generate iCal invite content
     *
     * @param string $event_title The event title.
     * @param string $event_date The event start date.
     * @param string $event_time The event start time.
     * @param string $event_end_date The event end date.
     * @param string $event_end_time The event end time.
     * @param string $event_description The event description.
     * @param string $event_location The event location.
     * @return string The iCal file content.
     */
    private function generate_ical_content($event_title, $event_date, $event_time, $event_end_date, $event_end_time, $event_description = '', $event_location = '')
    {
        $start_timestamp = strtotime($event_date . ' ' . $event_time);

        if (!empty($event_end_date) && !empty($event_end_time)) {
            $end_timestamp = strtotime($event_end_date . ' ' . $event_end_time);
        } else {
            $end_timestamp = strtotime('+2 hours', $start_timestamp); // Default to +2 hours
        }

        $start_datetime = date('Ymd\THis', $start_timestamp);
        $end_datetime = date('Ymd\THis', $end_timestamp);

        $uid = uniqid() . '@' . parse_url(get_bloginfo('url'), PHP_URL_HOST);

        $timezone = get_option('timezone_string') ?: 'UTC';

        $ical_content = "BEGIN:VCALENDAR\r\n";
        $ical_content .= "VERSION:2.0\r\n";
        $ical_content .= "PRODID:-//Your Site//NONSGML v1.0//EN\r\n";
        $ical_content .= "BEGIN:VTIMEZONE\r\n";
        $ical_content .= "TZID:$timezone\r\n";
        $ical_content .= "LAST-MODIFIED:" . gmdate('Ymd\THis\Z') . "\r\n";
        $ical_content .= "TZURL:https://www.tzurl.org/zoneinfo-outlook/$timezone\r\n";
        $ical_content .= "X-LIC-LOCATION:$timezone\r\n";
        $ical_content .= "END:VTIMEZONE\r\n";
        $ical_content .= "BEGIN:VEVENT\r\n";
        $ical_content .= "UID:$uid\r\n";
        $ical_content .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
        $ical_content .= "DTSTART;TZID=$timezone:$start_datetime\r\n";
        $ical_content .= "DTEND;TZID=$timezone:$end_datetime\r\n";
        $ical_content .= "SUMMARY:" . addcslashes($event_title, ",;") . "\r\n";
        $ical_content .= "DESCRIPTION:" . addcslashes($event_description, ",;") . "\r\n";
        $ical_content .= "LOCATION:" . addcslashes($event_location, ",;") . "\r\n";
        $ical_content .= "END:VEVENT\r\n";
        $ical_content .= "END:VCALENDAR\r\n";

        return $ical_content;
    }

    /**
     * Generate iCal download link
     *
     * @param string $event_title The event title.
     * @param string $event_date The event start date.
     * @param string $event_time The event start time.
     * @param string $event_end_date The event end date.
     * @param string $event_end_time The event end time.
     * @param string $event_description The event description.
     * @param string $event_location The event location.
     * @return string The iCal download link.
     */
    private function generate_ical_download_link($event_title, $event_date, $event_time, $event_end_date, $event_end_time, $event_description = '', $event_location = '')
    {
        $ical_content = $this->generate_ical_content($event_title, $event_date, $event_time, $event_end_date, $event_end_time, $event_description, $event_location);

        // Save the iCal content to a temporary file
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['basedir'] . '/event-' . uniqid() . '.ics';
        file_put_contents($file_path, $ical_content);

        // Generate the download link
        $file_url = $upload_dir['baseurl'] . '/' . basename($file_path);
        return $file_url;
    }
}
