<?php
/**
 * Postwave Mailer — intercepts wp_mail() and sends via JMAP.
 *
 * @package Postwave
 * @license GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Postwave_Mailer {

	private $options;

	public function __construct( array $options ) {
		$this->options = $options;
		add_filter( 'pre_wp_mail', array( $this, 'send' ), 10, 2 );
	}

	public function send( $return, $atts ) {
		$to          = $atts['to'];
		$subject     = $atts['subject'];
		$message     = $atts['message'];
		$headers     = $atts['headers'] ?? '';
		$attachments = $atts['attachments'] ?? array();

		$parsed       = $this->parse_headers( $headers );
		$from_name    = $parsed['from_name']  ?: $this->options['from_name'];
		$from_email   = $parsed['from_email'] ?: $this->options['from_email'];
		$content_type = $parsed['content_type'];

		$to_list       = $this->normalize_addresses( $to );
		$cc_list       = $this->normalize_addresses( $parsed['cc'] );
		$bcc_list      = $this->normalize_addresses( $parsed['bcc'] );
		$reply_to_list = $this->normalize_addresses( $parsed['reply_to'] );

		$log = array(
			'from'             => $from_email,
			'to'               => implode( ', ', $to_list ),
			'subject'          => $subject,
			'attachment_count' => is_array( $attachments ) ? count( $attachments ) : 0,
		);

		if ( empty( $to_list ) ) {
			return $this->fail( $log, __( 'No recipient address provided.', 'postwave' ) );
		}

		$client = new Postwave_JMAP_Client(
			$this->options['server_url'],
			$this->options['username'],
			$this->options['password']
		);

		$session = $client->discover_session();
		if ( is_wp_error( $session ) ) {
			return $this->fail( $log, $session->get_error_message() );
		}

		$identity_id = $client->get_identity_id( $from_email );
		if ( is_wp_error( $identity_id ) ) {
			return $this->fail( $log, $identity_id->get_error_message() );
		}
		$log['identity_id'] = $identity_id;

		$account_id = $client->get_account_id();
		$log['account_id'] = $account_id;

		$sent_mailbox = $client->get_mailbox_id_by_role( 'sent' );
		if ( is_wp_error( $sent_mailbox ) ) {
			return $this->fail( $log, $sent_mailbox->get_error_message() );
		}

		// Upload attachments.
		$jmap_attachments = array();
		foreach ( (array) $attachments as $file ) {
			if ( ! is_string( $file ) || ! file_exists( $file ) ) {
				continue;
			}
			$data = file_get_contents( $file );
			if ( false === $data ) {
				continue;
			}
			$mime    = wp_check_filetype( $file );
			$type    = ! empty( $mime['type'] ) ? $mime['type'] : 'application/octet-stream';
			$blob_id = $client->upload_blob( $data, $type );
			if ( is_wp_error( $blob_id ) ) {
				return $this->fail( $log, $blob_id->get_error_message() );
			}
			$jmap_attachments[] = array(
				'blobId'      => $blob_id,
				'type'        => $type,
				'name'        => basename( $file ),
				'disposition' => 'attachment',
			);
		}

		$create_id    = 'pw-' . wp_generate_password( 12, false );
		$is_html      = ( 'text/html' === $content_type );

		$email = array(
			'from'       => array( array( 'name' => $from_name, 'email' => $from_email ) ),
			'to'         => $this->to_jmap( $to_list ),
			'subject'    => $subject,
			'keywords'   => array( '$seen' => true ),
			'mailboxIds' => array( $sent_mailbox => true ),
		);

		if ( ! empty( $cc_list ) ) {
			$email['cc'] = $this->to_jmap( $cc_list );
		}
		if ( ! empty( $bcc_list ) ) {
			$email['bcc'] = $this->to_jmap( $bcc_list );
		}
		if ( ! empty( $reply_to_list ) ) {
			$email['replyTo'] = $this->to_jmap( $reply_to_list );
		}

		if ( $is_html ) {
			$plain                  = wp_strip_all_tags( $message );
			$email['bodyValues']    = array(
				'text' => array( 'value' => $plain ),
				'html' => array( 'value' => $message ),
			);
			$email['textBody']      = array( array( 'partId' => 'text', 'type' => 'text/plain' ) );
			$email['htmlBody']      = array( array( 'partId' => 'html', 'type' => 'text/html' ) );
		} else {
			$email['bodyValues']    = array( 'text' => array( 'value' => $message ) );
			$email['textBody']      = array( array( 'partId' => 'text', 'type' => 'text/plain' ) );
		}

		if ( ! empty( $jmap_attachments ) ) {
			$email['attachments'] = $jmap_attachments;
		}
		$log['attachment_count'] = count( $jmap_attachments );

		// Step 1: Email/set — create the email object.
		$create_resp = $client->request( array(
			array( 'Email/set', array( 'accountId' => $account_id, 'create' => array( $create_id => $email ) ), '0' ),
		) );

		if ( is_wp_error( $create_resp ) ) {
			return $this->fail( $log, $create_resp->get_error_message() );
		}

		if ( ! empty( $create_resp[0][1]['notCreated'] ) ) {
			$err = reset( $create_resp[0][1]['notCreated'] );
			return $this->fail( $log, $err['description'] ?? __( 'Failed to create email via JMAP.', 'postwave' ) );
		}

		$email_id = $create_resp[0][1]['created'][ $create_id ]['id'] ?? '';
		if ( empty( $email_id ) ) {
			return $this->fail( $log, __( 'JMAP email creation returned no email id.', 'postwave' ) );
		}
		$log['email_id'] = $email_id;

		// Step 2: EmailSubmission/set — submit for delivery.
		$submit_resp = $client->request( array(
			array(
				'EmailSubmission/set',
				array(
					'accountId' => $account_id,
					'create'    => array(
						'sub-1' => array( 'emailId' => $email_id, 'identityId' => $identity_id ),
					),
				),
				'0',
			),
		) );

		if ( is_wp_error( $submit_resp ) ) {
			return $this->fail( $log, $submit_resp->get_error_message() );
		}

		if ( ! empty( $submit_resp[0][1]['notCreated'] ) ) {
			$err = reset( $submit_resp[0][1]['notCreated'] );
			return $this->fail( $log, $err['description'] ?? __( 'Failed to submit email via JMAP.', 'postwave' ) );
		}

		foreach ( array_merge( $create_resp, $submit_resp ) as $resp ) {
			if ( isset( $resp[0] ) && 'error' === $resp[0] ) {
				return $this->fail( $log, $resp[1]['description'] ?? __( 'JMAP error.', 'postwave' ) );
			}
		}

		Postwave_Mail_Log::add( array_merge( $log, array( 'status' => 'sent' ) ) );

		return true;
	}

	private function fail( array $log, $message ) {
		Postwave_Mail_Log::add( array_merge( $log, array( 'status' => 'failed', 'error' => $message ) ) );
		do_action( 'wp_mail_failed', new WP_Error( 'wp_mail_failed', $message ) );
		return false;
	}

	private function parse_headers( $headers ) {
		$result = array(
			'from_name'    => '',
			'from_email'   => '',
			'cc'           => array(),
			'bcc'          => array(),
			'reply_to'     => array(),
			'content_type' => 'text/plain',
		);

		if ( empty( $headers ) ) {
			return $result;
		}

		if ( ! is_array( $headers ) ) {
			$headers = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
		}

		foreach ( $headers as $header ) {
			if ( false === strpos( $header, ':' ) ) {
				continue;
			}
			list( $name, $value ) = explode( ':', trim( $header ), 2 );
			$name  = strtolower( trim( $name ) );
			$value = trim( $value );

			switch ( $name ) {
				case 'from':
					if ( preg_match( '/(.*)<(.+)>/', $value, $m ) ) {
						$result['from_name']  = trim( $m[1], ' "' );
						$result['from_email'] = trim( $m[2] );
					} else {
						$result['from_email'] = $value;
					}
					break;
				case 'cc':
					$result['cc'][] = $value;
					break;
				case 'bcc':
					$result['bcc'][] = $value;
					break;
				case 'reply-to':
					$result['reply_to'][] = $value;
					break;
				case 'content-type':
					if ( false !== stripos( $value, 'text/html' ) ) {
						$result['content_type'] = 'text/html';
					}
					break;
			}
		}

		return $result;
	}

	private function normalize_addresses( $addresses ) {
		if ( empty( $addresses ) ) {
			return array();
		}
		if ( is_string( $addresses ) ) {
			$addresses = array_map( 'trim', explode( ',', $addresses ) );
		}
		return array_values( array_filter( array_map( 'trim', (array) $addresses ) ) );
	}

	private function to_jmap( array $addresses ) {
		$result = array();
		foreach ( $addresses as $addr ) {
			if ( preg_match( '/(.*)<(.+)>/', $addr, $m ) ) {
				$result[] = array( 'name' => trim( $m[1], ' "' ), 'email' => trim( $m[2] ) );
			} else {
				$result[] = array( 'email' => trim( $addr ) );
			}
		}
		return $result;
	}
}
