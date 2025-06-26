<?php
namespace ArtEvents;

class SiteHelper {
	private static $instance = null;

	const TRANSIENT_KEY = 'skey';

	private function __construct() {
		// Add any initialization code here
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
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

	public static function redirect_with_message( $url, $message, $type = 'success', $delete = false ) {
		// Generate a unique transient key based on the user ID or session
		$transient_key = $type . ':' . time() . uniqid();
		$transient_key = self::encrypt_decrypt( $transient_key, 'encrypt' );

		// Store the message in a transient (temporary option)
		set_transient( $transient_key, $message, 20 );

		if ( $type === 'error' ) {
			// Store the form data in a transient
			set_transient( 'posted_data_' . $transient_key, $_POST, 20 );
		}
		if ( $type === 'success' || $delete ) {
			// Clear the posted data transient if it's a success message
			delete_transient( 'posted_data_' . $transient_key );
		}

		// Append the transient key to the URL as a query parameter
		$url = self::generate_url( $url, [ self::TRANSIENT_KEY => $transient_key ] );

		// Redirect to the desired URL
		wp_redirect( $url );
		exit;
	}

	public static function display_flash_message( &$content ) {
		// Retrieve the transient key from the query parameter
		$transient_key = isset( $_GET[ self::TRANSIENT_KEY ] ) ? sanitize_text_field( $_GET[ self::TRANSIENT_KEY ] ) : null;
		if ( $transient_key ) {
			// Retrieve the message from the transient
			$message = get_transient( $transient_key );

			if ( $message ) {
				// Determine the message type (success or error)
				$decrypted_key = self::encrypt_decrypt( $transient_key, 'decrypt' );

				$type = explode( ':', $decrypted_key )[0];
				// Determine the message type
				$message_type = $type === 'error' ? 'error' : 'success';

				// Append the message to the content
				$content .= '<div class="notice notice-' . esc_attr( $message_type ) . ' is-dismissible"><p>' . esc_html( $message ) . '</p></div>';

				// Clear the transient after displaying the message
				delete_transient( $transient_key );
			}
		}
	}

	public static function get_transient_data( $prefix = '' ) {
		// Retrieve the transient key from the query parameter
		$transient_key = isset( $_GET[ self::TRANSIENT_KEY ] )
			? sanitize_text_field( $_GET[ self::TRANSIENT_KEY ] )
			: null;

		// Retrieve the posted data
		$posted_data = get_transient( 'posted_data_' . $transient_key );

		if ( $posted_data ) {
			// Use $posted_data to repopulate the form
			delete_transient( 'posted_data_' . $transient_key ); // Clear after use
			return $posted_data;
		}
		return null;
	}

	public static function pass_data_to_theme( $url, $data, $key = 'plugin_data' ) {
		// Generate a unique transient key based on the provided key
		$transient_key = $key . ':' . time() . uniqid();
		$transient_key = self::encrypt_decrypt( $transient_key, 'encrypt' );

		// Store the data in a transient
		set_transient( $transient_key, $data, 60 ); // Set the expiration to 60 seconds (you can adjust this)

		// Append the transient key to the URL as a query parameter
		$url = self::generate_url( $url, [ self::TRANSIENT_KEY => $transient_key ] );

		// Redirect to the desired URL
		wp_redirect( $url );
		exit;
	}

	public static function display_plugin_data( &$content ) {
		// Retrieve the transient key from the query parameter
		$transient_key = isset( $_GET[ self::TRANSIENT_KEY ] ) ? sanitize_text_field( $_GET[ self::TRANSIENT_KEY ] ) : null;

		if ( $transient_key ) {
			// Retrieve the data from the transient
			$data = get_transient( $transient_key );

			if ( $data ) {
				// Do something with the data (e.g., display it or use it)
				$content .= '<div class="plugin-data">';
				$content .= '<pre>' . esc_html( print_r( $data, true ) ) . '</pre>';
				$content .= '</div>';

				// Clear the transient after displaying the data
				delete_transient( $transient_key );
			}
		}
	}

	public static function encrypt_decrypt( $string, $action ) {
		$secret_vi  = 'vgpHQeMYOW3kweYCwGpTJqlW';
		$secret_key = 'ounAic24pdaUp9z6sCUhyxwPenAH20U6';
		$output     = false;

		$encrypt_method = "AES-256-CBC";

		$key = hash( 'sha256', $secret_key );

		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr( hash( 'sha256', $secret_vi ), 0, 16 );

		if ( $action == 'encrypt' ) {
			$output = openssl_encrypt( $string, $encrypt_method, $key, 0, $iv );
			$output = base64_encode( $output );
			$output = str_replace( '=', '~', $output );
		} else if ( $action == 'decrypt' ) {
			$string = str_replace( '~', '=', $string );
			$output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
		}

		return $output;
	}

	public static function dd( $data ) {
		echo '<pre>';
		print_r( $data );
		echo '</pre>';
		wp_die();
	}

	/**
	 * Example helper method to format dates.
	 *
	 * @param string $date The date string.
	 * @param string $format The desired format.
	 * @return string
	 */
	public static function format_date( $date, $format = 'Y-m-d' ) {
		$timestamp = strtotime( $date );
		return date( $format, $timestamp );
	}

	public static function generate_url( $base_url, $params = [] ) {
		return add_query_arg( $params, $base_url );
	}

}

// Initialize the class
SiteHelper::get_instance();