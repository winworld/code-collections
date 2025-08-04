<?php

add_action( 'shutdown', function () {
	$error = error_get_last();
	if ( $error && in_array( $error['type'], [ E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR ] ) ) {
		$message = sprintf(
			"*PHP Error:*\nMessage: `%s`\nFile: `%s`\nLine: `%d`",
			$error['message'],
			$error['file'],
			$error['line']
		);
        $action_name = 'YCI';
		send_log_to_slack( $action_name, $message );
	}
} );
function send_log_to_slack( $action_name, $data ) {
	$webhook_url = 'https://hooks.slack.com/services/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';
	// serialize the data
	if ( is_array( $data ) || is_object( $data ) ) {
		$payload = json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
	} else {
		$payload = (string) $data;
	}
	$entry = $action_name . "\n";
	if ( strpos( $payload, "\n" ) !== false ) {
		$lines = explode( "\n", trim( $payload ) );
		foreach ( $lines as $line ) {
			$entry .= "{$line}\n";
		}
	} else {
		$entry .= "{$payload}\n";
	}
	$form_data_payload = json_encode(
		[ 
			'text' => $entry
		]
	);
	$args              = [ 
		'body'    => $form_data_payload,
		'headers' => [ 'Content-Type' => 'application/json' ]
	];
	$result            = wp_remote_post( $webhook_url, $args );
}