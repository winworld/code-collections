<?php

add_action('shutdown', function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $message = sprintf(
            "*PHP Error:*\nMessage: `%s`\nFile: `%s`\nLine: `%d`",
            $error['message'],
            $error['file'],
            $error['line']
        );
        send_error_to_slack($message);
    }
});
function send_error_to_slack($text) {
    $webhook_url = 'https://hooks.slack.com/services/XXX/XXX/XXX'; // replace with your URL
    $payload = json_encode([
        'text' => $text
    ]);
    $args = [
        'body'        => $payload,
        'headers'     => ['Content-Type' => 'application/json'],
        'timeout'     => 10,
        'redirection' => 5,
        'blocking'    => true,
    ];
    wp_remote_post($webhook_url, $args);
}