<?php
namespace epiphyt\Impressum;

// exit if ABSPATH is not defined
defined( 'ABSPATH' ) || exit;

/**
 * Impressum.
 * 
 * @version		0.1
 * @author		Epiphyt
 * @license		GPL3 <https://www.gnu.org/licenses/gpl-3.0.html>
 */
class Impressum {
	/**
	 * The full path to the main plugin file.
	 * @var string
	 */
	public $plugin_file = '';
	
	/**
	 * Impressum constructor.
	 * 
	 * @param string $plugin_file The path of the main plugin file
	 */
	public function __construct( $plugin_file ) {
		// return on Ajax or autosave
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}
		
		// assign variables
		$this->plugin_file = $plugin_file;
		
		add_action( 'init', [ $this, 'load_textdomain' ] );
	}
	
	/**
	 * Load translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'impressum', false, dirname( plugin_basename( $this->plugin_file ) ) . '/languages' );
	}
	
	/**
	 * Get an option or a site option with the same name.
	 * The site option is received if there is no option
	 * with the same name.
	 * 
	 * @param string $option The option you want to get
	 * @return mixed|void
	 */
	protected static function impressum_get_option( $option ) {
		if ( ! is_string( $option ) ) return;
		
		if ( ! is_network_admin() ) {
			// try receive option
			$options = get_option( $option );
			
			if ( ! $options ) {
				$options = get_site_option( $option );
			}
		}
		else {
			$options = get_site_option( $option );
		}
		
		return $options;
	}
}