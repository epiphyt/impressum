<?php
declare(strict_types = 1);

namespace epiphyt\Basic_Auth;

/**
 * Enables usage of Basic Auth for REST API requests.
 * Based on https://github.com/WP-API/Basic-Auth
 * 
 * @author	Epiphyt
 * @license	GPL2
 * @package	epiphyt\Impressum
 */
final class Basic_Auth {
	/**
	 * @var		?\WP_Error Error of the last authentication attempt
	 */
	private ?\WP_Error $error = null;
	
	/**
	 * Get the credentials of the current request.
	 * 
	 * Credentials are passed on unaltered, since sanitizing would change
	 * passwords containing characters that are stripped by sanitizing.
	 * 
	 * @return	?string[] Username and password, or null if there are none
	 */
	private function get_credentials(): ?array {
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- credentials must not be altered
		if ( isset( $_SERVER['PHP_AUTH_USER'] ) ) {
			$username = (string) \wp_unslash( $_SERVER['PHP_AUTH_USER'] );
			$password = isset( $_SERVER['PHP_AUTH_PW'] ) ? (string) \wp_unslash( $_SERVER['PHP_AUTH_PW'] ) : '';
			
			return $username !== '' ? [ $username, $password ] : null;
		}
		
		// needs .htaccess adjustments on some servers:
		// SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
		$authorization = (string) \wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '' );
		// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		
		if ( ! \preg_match( '/^Basic\s+(\S+)$/i', \trim( $authorization ), $matches ) ) {
			return null;
		}
		
		$decoded = \base64_decode( $matches[1], true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		
		if ( $decoded === false || ! \str_contains( $decoded, ':' ) ) {
			return null;
		}
		
		// the password may contain colons itself
		list( $username, $password ) = \explode( ':', $decoded, 2 );
		
		return $username !== '' ? [ $username, $password ] : null;
	}
	
	/**
	 * Check whether the current request has been sent by a browser from another site.
	 * 
	 * An origin allowed via the allowed_http_origins filter is always accepted.
	 * Requests without an origin and without fetch metadata are not sent by a
	 * browser on behalf of another site.
	 * 
	 * @return	bool Whether the request is a cross-site request
	 */
	private function is_cross_site_request(): bool {
		$origin = \get_http_origin();
		
		if ( $origin !== '' ) {
			return ! $this->is_own_origin( $origin ) && \is_allowed_http_origin( $origin ) === '';
		}
		
		if ( ! isset( $_SERVER['HTTP_SEC_FETCH_SITE'] ) ) {
			return false;
		}
		
		$fetch_site = \sanitize_key( \wp_unslash( $_SERVER['HTTP_SEC_FETCH_SITE'] ) );
		
		return ! \in_array( $fetch_site, [ 'none', 'same-origin' ], true );
	}
	
	/**
	 * Check whether an origin is the origin of this site, including its port.
	 * 
	 * @param	string	$origin Origin to check
	 * @return	bool Whether the origin is the origin of this site
	 */
	private function is_own_origin( string $origin ): bool {
		foreach ( [ \home_url(), \site_url() ] as $url ) {
			$parts = \wp_parse_url( $url );
			
			if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
				continue;
			}
			
			$own_origin = $parts['scheme'] . '://' . $parts['host'] . ( isset( $parts['port'] ) ? ':' . $parts['port'] : '' );
			
			if ( \strtolower( $own_origin ) === \strtolower( $origin ) ) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Get the error of the Basic Auth authentication.
	 * 
	 * @param	mixed	$error Current authentication error
	 * @return	mixed Authentication error
	 */
	public function json_basic_auth_error( mixed $error ): mixed {
		if ( ! empty( $error ) ) {
			return $error;
		}
		
		// the user may not be determined yet, which is when the error is set
		\wp_get_current_user();
		
		return $this->error ?? $error;
	}
	
	/**
	 * Authenticate a user via Basic Auth.
	 * 
	 * Only REST requests are authenticated. The REST server resets a missing
	 * user before serving the request, so a user determined before the request
	 * has been identified as REST request is determined again.
	 * 
	 * @param	mixed	$user Currently determined user
	 * @return	mixed Determined user ID, or the passed user if not authenticated via Basic Auth
	 */
	public function json_basic_auth_handler( mixed $user ): mixed {
		if ( ! empty( $user ) || ! \wp_is_serving_rest_request() ) {
			return $user;
		}
		
		$credentials = $this->get_credentials();
		
		if ( $credentials === null ) {
			return $user;
		}
		
		// browsers send cached credentials automatically, which would allow
		// cross-site request forgery
		if ( $this->is_cross_site_request() ) {
			$this->error = new \WP_Error(
				'impressum_rest_basic_auth_cross_site',
				\__( 'Basic authentication is not allowed for cross-site requests.', 'impressum' ),
				[ 'status' => 403 ]
			);
			
			return $user;
		}
		
		list( $username, $password ) = $credentials;
		
		// in multisite, wp_authenticate_spam_check calls get_currentuserinfo,
		// which calls this filter again and would recurse infinitely
		\remove_filter( 'determine_current_user', [ $this, 'json_basic_auth_handler' ], 20 );
		
		$authenticated_user = \wp_authenticate( $username, $password );
		
		if ( \is_wp_error( $authenticated_user ) ) {
			$authenticated_user = \wp_authenticate_application_password( null, $username, $password );
		}
		
		\add_filter( 'determine_current_user', [ $this, 'json_basic_auth_handler' ], 20 );
		
		if ( ! $authenticated_user instanceof \WP_User ) {
			$this->error = new \WP_Error(
				'impressum_rest_basic_auth_invalid',
				\__( 'The username or password is invalid.', 'impressum' ),
				[ 'status' => 401 ]
			);
			
			return $user;
		}
		
		$this->error = null;
		
		return $authenticated_user->ID;
	}
	
	/**
	 * Load the classes' filter.
	 */
	public function load(): void {
		\add_filter( 'determine_current_user', [ $this, 'json_basic_auth_handler' ], 20 );
		\add_filter( 'rest_authentication_errors', [ $this, 'json_basic_auth_error' ] );
	}
}
