<?php
/**
 * Postwave Admin — settings page, AJAX handlers, asset registration.
 *
 * @package Postwave
 * @license GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Postwave_Admin {

	/* Menu icon: filled envelope SVG (WordPress masks it with fill:currentColor) */
	const MENU_ICON = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0iY3VycmVudENvbG9yIj48cGF0aCBkPSJNMi4wMDMgNS44ODRMM10gOS44ODJsNy45OTctMy45OThBMiAyIDAgMDAxNiA0SDRhMiAyIDAgMDAtMS45OTcgMS44ODR6Ii8+PHBhdGggZD0iTTE4IDguMTE4bC04IDQtOC00VjE0YTIgMiAwIDAwMiAyaDEyYTIgMiAwIDAwMi0yVjguMTE4eiIvPjwvc3ZnPg==';

	public function __construct() {
		add_action( 'admin_menu',                         array( $this, 'register_page' ) );
		add_action( 'admin_enqueue_scripts',              array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_postwave_test',              array( $this, 'ajax_test' ) );
		add_action( 'admin_post_postwave_save',           array( $this, 'save_settings' ) );
		add_action( 'admin_post_postwave_clear_log',      array( $this, 'clear_log' ) );
		add_action( 'admin_post_postwave_export_log',     array( $this, 'export_log_csv' ) );
		add_action( 'wp_ajax_postwave_fetch_identities',  array( $this, 'ajax_fetch_identities' ) );
	}

	public function register_page() {
		add_menu_page(
			__( 'Postwave JMAP', 'postwave' ),
			__( 'Postwave JMAP', 'postwave' ),
			'manage_options',
			'postwave',
			array( $this, 'render_page' ),
			self::MENU_ICON,
			30
		);
	}

	/**
	 * Save all settings via admin-post.php — single handler, all fields always present.
	 * This eliminates the tab data-loss bug that occurs with per-tab Settings API forms.
	 */
	public function save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'postwave' ) );
		}

		check_admin_referer( 'postwave_save' );

		$old = get_option( POSTWAVE_OPTION_KEY, array() );
		$in  = $_POST['postwave'] ?? array();

		$settings = array(
			'enabled'          => ! empty( $in['enabled'] ) ? 1 : 0,
			'server_url'       => esc_url_raw( trim( $in['server_url'] ?? '' ) ),
			'username'         => sanitize_text_field( $in['username'] ?? '' ),
			'from_name'        => sanitize_text_field( $in['from_name'] ?? '' ),
			'from_email'       => sanitize_email( $in['from_email'] ?? '' ),
			'test_recipient'   => sanitize_email( $in['test_recipient'] ?? '' ),
			'password'         => ! empty( $in['password'] ) ? $in['password'] : ( $old['password'] ?? '' ),
			// v1.1 settings
			'retry_enabled'    => ! empty( $in['retry_enabled'] ) ? 1 : 0,
			'retry_max'        => min( 5, max( 1, (int) ( $in['retry_max'] ?? 3 ) ) ),
			'retry_delay'      => in_array( (int) ( $in['retry_delay'] ?? 300 ), array( 300, 900, 1800, 3600 ), true )
			                        ? (int) $in['retry_delay'] : 300,
			'identity_id'      => sanitize_text_field( $in['identity_id'] ?? '' ),
			'identity_name'    => sanitize_text_field( $in['identity_name'] ?? '' ),
			'identity_email'   => sanitize_email( $in['identity_email'] ?? '' ),
			'tracking_enabled' => ! empty( $in['tracking_enabled'] ) ? 1 : 0,
		);

		update_option( POSTWAVE_OPTION_KEY, $settings );

		$saved_tab = sanitize_key( $in['_tab'] ?? 'general' );
		wp_safe_redirect( add_query_arg(
			array( 'page' => 'postwave', 'saved' => '1', 'tab' => $saved_tab ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	public function enqueue_assets( $hook ) {
		if ( 'toplevel_page_postwave' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'postwave-admin',
			POSTWAVE_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			POSTWAVE_VERSION . '.5'
		);

		wp_enqueue_script(
			'postwave-admin',
			POSTWAVE_PLUGIN_URL . 'assets/js/admin.js',
			array(),
			POSTWAVE_VERSION . '.5',
			true
		);

		wp_localize_script( 'postwave-admin', 'postwave', array(
			'ajax_url'          => admin_url( 'admin-ajax.php' ),
			'nonce'             => wp_create_nonce( 'postwave_test' ),
			'identities_nonce'  => wp_create_nonce( 'postwave_fetch_identities' ),
			'i18n'              => array(
				'account'      => __( 'Account', 'postwave' ),
				'identity'     => __( 'Identity', 'postwave' ),
				'recipient'    => __( 'Recipient', 'postwave' ),
				'capabilities' => __( 'Capabilities', 'postwave' ),
				'warnings'     => __( 'Warnings', 'postwave' ),
			),
		) );
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$options     = get_option( POSTWAVE_OPTION_KEY, array() );
		$stats       = Postwave_Mail_Log::get_stats();
		$entries     = Postwave_Mail_Log::get_entries();
		$tab         = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';
		$is_setup    = empty( $options['server_url'] ) && ! isset( $_GET['skip'] );
		$retry_count = Postwave_Retry_Queue::get_count();

		include POSTWAVE_PLUGIN_DIR . 'templates/page-settings.php';
	}

	public function clear_log() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'postwave' ) );
		}
		check_admin_referer( 'postwave_clear_log' );
		Postwave_Mail_Log::clear();
		wp_safe_redirect( add_query_arg( array( 'page' => 'postwave', 'tab' => 'log', 'cleared' => '1' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public function ajax_test() {
		check_ajax_referer( 'postwave_test', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'postwave' ) ) );
		}

		$options = get_option( POSTWAVE_OPTION_KEY, array() );

		if ( empty( $options['server_url'] ) || empty( $options['username'] ) || empty( $options['password'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Save your server URL, username and password first.', 'postwave' ) ) );
		}

		$client = new Postwave_JMAP_Client( $options['server_url'], $options['username'], $options['password'] );
		$result = $client->discover_session();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		$type = isset( $_POST['type'] ) ? sanitize_key( $_POST['type'] ) : 'connection';

		if ( 'email' === $type ) {
			$to = ! empty( $options['test_recipient'] ) ? $options['test_recipient']
				: ( ! empty( $options['from_email'] ) ? $options['from_email'] : get_bloginfo( 'admin_email' ) );

			if ( ! has_filter( 'pre_wp_mail' ) ) {
				new Postwave_Mailer( $options );
			}

			$mail_error    = null;
			$error_handler = function ( $err ) use ( &$mail_error ) { $mail_error = $err; };
			add_action( 'wp_mail_failed', $error_handler );

			$sent = wp_mail(
				$to,
				__( 'Postwave JMAP — Test Email', 'postwave' ),
				__( 'This is a test email sent via Postwave JMAP. If you received this, JMAP is configured correctly.', 'postwave' )
			);

			remove_action( 'wp_mail_failed', $error_handler );

			if ( $sent ) {
				wp_send_json_success( array( 'message' => __( 'Test email sent!', 'postwave' ), 'recipient' => $to ) );
			}

			$msg = __( 'Failed to send test email.', 'postwave' );
			if ( $mail_error instanceof WP_Error && $mail_error->get_error_message() ) {
				$msg = $mail_error->get_error_message();
			}
			wp_send_json_error( array( 'message' => $msg, 'recipient' => $to ) );
		}

		$session      = $client->get_session();
		$capabilities = array_keys( $session['capabilities'] ?? array() );
		$warnings     = array();

		if ( ! $client->supports_submission() ) {
			$warnings[] = __( 'Server does not advertise urn:ietf:params:jmap:submission. Sending may not work.', 'postwave' );
		}

		$identity       = $client->get_identity( $options['from_email'] ?? '' );
		$identity_label = '';

		if ( is_wp_error( $identity ) ) {
			$warnings[] = $identity->get_error_message();
		} else {
			$identity_label = $identity['name'] ?? '';
			if ( ! empty( $identity['email'] ) ) {
				$identity_label .= $identity_label ? ' <' . $identity['email'] . '>' : $identity['email'];
			}
			if ( ! empty( $identity['id'] ) ) {
				$identity_label .= ' (ID: ' . $identity['id'] . ')';
			}
		}

		wp_send_json_success( array(
			'message'      => __( 'Connection successful!', 'postwave' ),
			'account'      => (string) $client->get_account_id(),
			'identity'     => $identity_label,
			'capabilities' => array_values( $capabilities ),
			'warnings'     => $warnings,
		) );
	}

	/**
	 * Export the mail log as a CSV file download.
	 */
	public function export_log_csv() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'postwave' ) );
		}
		check_admin_referer( 'postwave_export_log' );

		$filename = 'postwave-mail-log-' . gmdate( 'Y-m-d' ) . '.csv';
		$csv      = Postwave_Mail_Log::to_csv();

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename ) . '"' );
		header( 'Content-Length: ' . strlen( $csv ) );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $csv;
		exit;
	}

	/**
	 * AJAX: fetch JMAP identities from the configured server.
	 */
	public function ajax_fetch_identities() {
		check_ajax_referer( 'postwave_fetch_identities', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'postwave' ) ) );
		}

		$options = get_option( POSTWAVE_OPTION_KEY, array() );

		if ( empty( $options['server_url'] ) || empty( $options['username'] ) || empty( $options['password'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Save your server credentials first.', 'postwave' ) ) );
		}

		$client = new Postwave_JMAP_Client( $options['server_url'], $options['username'], $options['password'] );
		$result = $client->discover_session();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		$identities = $client->get_all_identities();

		if ( is_wp_error( $identities ) ) {
			wp_send_json_error( array( 'message' => $identities->get_error_message() ) );
		}

		// Return only safe fields.
		$safe = array_map( function( $id ) {
			return array(
				'id'    => sanitize_text_field( $id['id']    ?? '' ),
				'name'  => sanitize_text_field( $id['name']  ?? '' ),
				'email' => sanitize_email(      $id['email'] ?? '' ),
			);
		}, $identities );

		wp_send_json_success( array( 'identities' => $safe ) );
	}
}
