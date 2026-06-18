<?php
/**
 * Postwave Mail Log — stores metadata for sent and failed mail attempts.
 *
 * @package Postwave
 * @license AGPL-3.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Postwave_Mail_Log {

	const MAX_ENTRIES = 100;

	public static function add( array $entry ) {
		$entries = self::get_entries();

		$normalized = array(
			'timestamp'        => (int) time(),
			'status'           => ( isset( $entry['status'] ) && 'sent' === $entry['status'] ) ? 'sent' : 'failed',
			'to'               => sanitize_text_field( $entry['to'] ?? '' ),
			'from'             => sanitize_email( $entry['from'] ?? '' ),
			'subject'          => sanitize_text_field( wp_strip_all_tags( $entry['subject'] ?? '' ) ),
			'error'            => sanitize_text_field( $entry['error'] ?? '' ),
			'attachment_count' => max( 0, (int) ( $entry['attachment_count'] ?? 0 ) ),
			'account_id'       => sanitize_text_field( $entry['account_id'] ?? '' ),
			'identity_id'      => sanitize_text_field( $entry['identity_id'] ?? '' ),
			'email_id'         => sanitize_text_field( $entry['email_id'] ?? '' ),
		);

		array_unshift( $entries, $normalized );
		$entries = array_slice( $entries, 0, self::MAX_ENTRIES );

		update_option( POSTWAVE_LOG_OPTION, $entries, false );
	}

	public static function get_entries() {
		$entries = get_option( POSTWAVE_LOG_OPTION, array() );
		if ( ! is_array( $entries ) ) {
			return array();
		}
		return array_values( array_filter( $entries, 'is_array' ) );
	}

	public static function clear() {
		delete_option( POSTWAVE_LOG_OPTION );
	}

	public static function get_stats() {
		$entries = self::get_entries();
		$now     = time();
		$day     = $now - DAY_IN_SECONDS;
		$week    = $now - WEEK_IN_SECONDS;

		$stats = array(
			'total'        => count( $entries ),
			'sent_today'   => 0,
			'failed_today' => 0,
			'sent_week'    => 0,
			'failed_week'  => 0,
		);

		foreach ( $entries as $entry ) {
			$ts     = (int) ( $entry['timestamp'] ?? 0 );
			$is_sent = 'sent' === ( $entry['status'] ?? '' );

			if ( $ts >= $day ) {
				$is_sent ? $stats['sent_today']++ : $stats['failed_today']++;
			}
			if ( $ts >= $week ) {
				$is_sent ? $stats['sent_week']++ : $stats['failed_week']++;
			}
		}

		return $stats;
	}
}
