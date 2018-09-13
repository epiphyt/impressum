<?php
namespace epiphyt\Update;

// exit if ABSPATH is not defined
defined( 'ABSPATH' ) || exit;

/**
 * Manage software licenses.
 * 
 * @version		0.1
 * @author		Epiphyt
 * @license		GPL2 <https://www.gnu.org/licenses/gpl-2.0.html>
 */
class Epiphyt_License extends Epiphyt_Update {
	/**
	 * The option name to listen on.
	 * 
	 * @var array
	 */
	private $options = [];
	
	/**
	 * The WooCommerce API URL.
	 * 
	 * @var string The API
	 */
	public $woo_server = 'https://epiph.yt/wocommerce/?wc-api=software-api';
	
	/**
	 * Epiphyt License constructor.
	 * 
	 * @param string $option_name Option name to listen
	 * @param string $product_id The Software Product ID of the WooCommerce product
	 * @param string $home_url The home URL of the current WordPress instance
	 */
	public function __construct( $option_name, $product_id, $home_url ) {
		// on first activation, the option doesn’t exist yet
		if ( ! is_array( self::get_real_option( $option_name ) ) ) return;
		
		// variable assignments
		$this->options = array_merge( self::get_real_option( $option_name ), [
			'platform' => $home_url,
			'product_id' => $product_id,
			'request' => 'check',
		] );
		
		// rename email key
		if ( ! empty( $this->options['license_email'] ) ) {
			$this->options['email'] = $this->options['license_email'];
			unset( $this->options['license_email'] );
		}
		
		// actions
		add_action( 'admin_notices', [ $this, 'license_activation_failed' ], 100 );
		add_action( 'update_option_' . $option_name, [ $this, 'check_activation' ], 10, 3 );
		add_action( 'update_site_option_' . $option_name, [ $this, 'check_activation' ], 10, 3 );
	}
	
	/**
	 * Activate or deactivate a license.
	 * 
	 * @param string $action The action (activation/deactivation)
	 * @param array $args License data
	 */
	public function activate_or_deactivate( $action, $args ) {
		$args['request'] = $action;
		$request = wp_remote_get( $this->woo_server, [ 'body' => $args ] );
		$response = json_decode( wp_remote_retrieve_body( $request ), true );
		
		if ( isset( $response['error'] ) ) {
			return $response;
		}
		
		return true;
	}
	
	/**
	 * Activate a new license if necessary.
	 * 
	 * @param mixed $old_value The old option value
	 * @param mixed $value The new option value
	 * @param mixed $option The option
	 */
	public function check_activation( $old_value, $value, $option ) {
		// get new value
		$this->options = array_merge( $this->options, $value );
		
		// rename email key
		if ( isset( $this->options['license_email'] ) ) {
			$this->options['email'] = $this->options['license_email'];
			unset( $this->options['license_email'] );
		}
		
		$request = wp_remote_get( $this->woo_server, [ 'body' => $this->options ] );
		$response = json_decode( wp_remote_retrieve_body( $request ), true );
		
		if ( ! $response['success'] || ! isset( $response['activations'] ) || ! in_array( $this->options['platform'], array_column( $response['activations'], 'activation_platform' ), true ) ) {
			// activate new license if there isn’t anyone yet
			$response = $this->activate_or_deactivate( 'activation', $this->options );
			
			if ( $response !== true ) {
				update_option( 'epiphyt_license_response', $response );
			}
			else {
				// clear response
				update_option( 'epiphyt_license_response', '' );
			}
		}
		else {
			update_option( 'epiphyt_license_response', $response );
		}
		
		// otherwise it’s already activated or there’s no such customer account
	}
	
	/**
	 * 
	 */
	public function license_activation_failed() {
		global $pagenow;
		
		$response = get_option( 'epiphyt_license_response' );
		// only if there is a response from the license api
		if ( empty( $response ) ) return;
		// show notice only impressum options|settings page
		// phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification
		if ( ! ( $pagenow === 'options-general.php' || $pagenow === 'settings.php' ) && isset( $_GET['page'] ) && $_GET['page'] === self::$update_slug ) return;
		// phpcs:enable
		?>
<div id="message" class="notice notice-error">
	<p><?php esc_html_e( 'License Activation failed:', 'impressum' ); ?><br>
	<?php
	// phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain
	echo ( isset( $response['error'] ) ? esc_html( $response['error'] ) : esc_html( __( 'Unknown error.', self::$text_domain ) ) );
	// phpcs: enable
	?></p>
</div>
		<?php
	}
}
