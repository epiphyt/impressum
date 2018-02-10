<?php
// exit if ABSPATH is not defined
defined( 'ABSPATH' ) || exit;

/**
 * Do update checks and updates.
 * 
 * @version		0.1
 * @author		Epiphyt
 * @license		GPL2 <https://www.gnu.org/licenses/gpl-2.0.html>
 */
class Epiphyt_Update {
	/**
	 * The base name of the plugin we want to check updates for.
	 * 
	 * @var string The base name 
	 */
	public $plugin_base = '';
	
	/**
	 * All plugins with a manual update check.
	 * 
	 * @var array The plugin data
	 */
	private $plugin_data = null;
	
	/**
	 * The name of the plugin we want to check updates for.
	 * 
	 * @var string The name 
	 */
	public $plugin_name = '';
	
	/**
	 * The text domain of the plugin for translations.
	 * 
	 * @var string The text domain
	 */
	public $text_domain = '';
	
	/**
	 * The slug of the plugin you want to check updates for.
	 * 
	 * @var string The slug
	 */
	public static $update_slug = '';
	
	/**
	 * The URL to the update server.
	 * 
	 * @var string The URL
	 */
	public $update_url = 'https://update.epiph.yt';
	
	/**
	 * Update constructor.
	 * 
	 * @param string $plugin_base The base name of the plugin we want to check
	 * @param string $text_domain The textdomain of the plugin
	 */
	public function __construct( $plugin_base, $text_domain ) {
		// assign variables
		$this->plugin_base = $plugin_base;
		$this->plugin_data = is_multisite() ? (array) get_site_option( 'epiphyt_update' ) : (array) get_option( 'epiphyt_update' );
		$this->text_domain = $text_domain;
		
		// hooks
		add_filter( 'plugins_api', [ $this, 'get_info' ], 10, 3 );
		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_updates' ] );
		
		/**
		 * Check for plugins with the same slug (for example from W.org) and
		 * remove update notifications for them.
		 * 
		 * For whatever reason, it seems to be working better here in an
		 * anonymous function rather than in a method.
		 */
		add_filter( 'site_transient_update_plugins', function ( $transient ) {
			if ( empty ( $transient->response ) ) return $transient;
			
			if ( array_key_exists( $this->plugin_base, $transient->response ) && strpos( $transient->response[ $this->plugin_base ]->package, $this->update_url ) === false ) {
				unset( $transient->response[ $this->plugin_base ] );
			}
			
			return $transient;
		} );
	}
	
	/**
	 * Check plugin for updates.
	 * 
	 * @param object $data The plugin update transient.
	 * @return mixed
	 */
	public function check_updates( $data ) {
		$slug = self::$update_slug;
		$this->plugin_data = self::request( [
			'action'  => 'get_metadata',
			'slug' => $slug,
		] );
		
		if ( $this->plugin_data === false ) return $data;
		if ( ! isset( $data->response ) ) $data->response = [];
		
		$this->plugin_data = array_filter( $this->plugin_data, [ $this, 'has_update' ] );
		
		foreach ( $this->plugin_data as $plugin ) {
			if ( strpos( $this->plugin_base, $plugin->slug ) !== false ) {
				// add data to transient
				$data->response[ $this->plugin_base ] = $plugin;
			}
		}
		
		$option = get_option( 'epiphyt_update', [] );
		$option['plugins'] = array_keys( $this->plugin_data );
		
		update_option( 'epiphyt_update', $option );
		
		return $data;
	}
	
	/**
	 * Get plugin information.
	 * 
	 * @param object $data The plugin update data
	 * @param string $action Request action
	 * @param object $args Extra parameters
	 * @return object
	 */
	public function get_info( $data, $action, $args ) {
		$option = get_option( 'epiphyt_update', [] );
		$plugins = isset( $option['plugins'] ) ? $option['plugins'] : [];
		
		if ( $action !== 'plugin_information' || ! isset( $args->slug ) || ! in_array( $args->slug, $plugins, true ) ) {
			return $data;
		}
		
		$plugin_data = self::request( array(
			'action'  => 'get_metadata',
			'slug' => substr( $args->slug, 0, strpos( $args->slug, '/' ) ),
		), true );
		$plugin_data = json_decode( $plugin_data, true );
		
		// the update server has no changelog support
		// just return a fixed message
		$info = new stdClass();
		$info->name = $plugin_data['name'];
		$info->author = $plugin_data['author'];
		$info->homepage = $plugin_data['homepage'];
		$info->sections = [
			'changelog' => sprintf( __( 'Please visit our website to receive notifications about the latest updates:<br><a href="%1$s">%1$s</a>', $this->text_domain ), 'https://epiph.yt' ),
		];
		$info->slug = $plugin_data['slug'];
		$info->version = $plugin_data['version'];
		
		return $info;
	}
	
	/**
	 * Send request to update server.
	 * 
	 * @param array|string $args Query arguments
	 * @param bool $raw Should the response returned in raw format or not 
	 * @return bool|object
	 */
	public function request( $args = '', $raw = false ) {
		// add email and API key to the request params.
		$option = get_option( 'impressum_update', [] );
		// prepare args
		$args = wp_parse_args( $args, $option );
		$args = array_filter( $args );
		// TODO: Remove after testing
		$args['license_key'] = 'totallyvalid';
		
		// request plugin data
		$request = wp_safe_remote_post( $this->update_url, [ 'body' => $args, ] );
		$response = wp_remote_retrieve_body( $request );
		
		// check response
		if ( ! is_wp_error( $request ) || $response === 200 ) {
			return $raw ? $response : $this->prepare_response( $response );
		}
		
		return false;
	}
	
	/**
	 * Check if a plugin has an update to a new version.
	 * 
	 * @param object $plugin_data The plugin update data
	 * @return bool
	 */
	protected function has_update( $plugin_data ) {
		$plugins = get_plugins();
		
		return isset( $plugins[ $plugin_data->plugin ] ) && version_compare( $plugins[ $plugin_data->plugin ]['Version'], $plugin_data->new_version, '<' );
	}
	
	/**
	 * Prepare the response for a proper processing.
	 * 
	 * @param string $response The response of the update server
	 * @return array
	 */
	protected function prepare_response( $response ) {
		$plugin = json_decode( $response, true );
		$pluginObj = new stdClass();
		$pluginObj->new_version = $plugin['version'];
		$pluginObj->package = isset( $plugin['download_url'] ) ? $plugin['download_url'] : '';
		$pluginObj->plugin = $this->plugin_base;
		$pluginObj->slug = $this->plugin_base;
		$pluginObj->url = $plugin['homepage'];
		$array = [];
		$array[ $this->plugin_base ] = $pluginObj;
		
		return $array;
	}
}