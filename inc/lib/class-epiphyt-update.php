<?php
namespace epiphyt\Update;

// exit if ABSPATH is not defined
defined( 'ABSPATH' ) || exit;

/**
 * Do update checks and updates.
 * 
 * @author		Epiphyt
 * @license		GPL3 <https://www.gnu.org/licenses/gpl-3.0.html>
 * @version		1.0.0
 */
class Epiphyt_Update {
	/**
	 * The home URL of the current WordPress instance.
	 * 
	 * @var string
	 */
	private $home_url = '';
	
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
	 * The Software Product ID in the WooCommerce shop
	 * 
	 * @var string The ID
	 */
	public $product_id = '';
	
	/**
	 * The text domain of the plugin for translations.
	 * 
	 * @var string The text domain
	 */
	public static $text_domain = '';
	
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
	 * @param string $product_id The Software Product ID of the WooCommerce product
	 * @param string $home_url The home URL of the current WordPress instance
	 */
	public function __construct( $plugin_base, $text_domain, $product_id, $home_url ) {
		// assign variables
		$this->home_url = $home_url;
		$this->plugin_base = $plugin_base;
		$this->plugin_data = is_multisite() ? (array) get_site_option( 'epiphyt_update' ) : (array) get_option( 'epiphyt_update' );
		$this->product_id = $product_id;
		self::$text_domain = $text_domain;
		
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
		add_filter( 'site_transient_update_plugins', function( $transient ) {
			if ( empty ( $transient->response ) ) return $transient;
			
			if ( array_key_exists( $this->plugin_base, $transient->response ) && strpos( $transient->response[ $this->plugin_base ]->package, $this->update_url ) === false ) {
				unset( $transient->response[ $this->plugin_base ] );
			}
			
			return $transient;
		} );
		
		/**
		 * Before the files are copied to wp-content/plugins, we need to rename
		 * the folder of the plugin, so it matches the slug. That is because
		 * WordPress uses the folder name for the destination inside
		 * wp-content/plugins, not the plugin slug. And the name of the ZIP we
		 * get from the update server is very random and not the plugin slug.
		 * 
		 * @param string $source File source location
		 * @param string $remote_source Remote file source location
		 * @param \WP_Upgrader $wp_upgrader WP_Upgrader instance
		 * @param array $args Extra arguments passed to hooked filters
		 */
		add_filter( 'upgrader_source_selection', function ( $source, $remote_source, $wp_upgrader, $args ) {
			global $wp_filesystem;
			
			$option = is_multisite() ? (array) get_site_option( 'epiphyt_update' ) : (array) get_option( 'epiphyt_update' );
			
			if ( empty( $option['plugins'] ) ) return $source;
			
			$plugin = reset( $option['plugins'] );
			
			// check if the currently updated plugin matches our plugin base name
			if ( $plugin !== false && isset( $args['plugin'] ) && $args['plugin'] === $plugin ) {
				if ( $wp_filesystem->exists( $remote_source ) ) {
					// create a folder with slug as name inside the folder
					$upgrade_dir = trailingslashit( $remote_source ) . dirname( $plugin );
					$wp_filesystem->mkdir( $upgrade_dir );
					
					// move all files into the new upgrade dir to match the 
					foreach ( $wp_filesystem->dirlist( $remote_source ) as $file => $info ) {
						if ( $file === dirname( $plugin ) ) continue;
						rename( trailingslashit( $remote_source ) . $file, \trailingslashit( $upgrade_dir ) . $file );
					}
					
					return trailingslashit( $source ) . dirname( $plugin );
				}
			}
			
			return $source;
		}, 10, 4 );
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
		
		$option = is_multisite() ? (array) get_site_option( 'epiphyt_update' ) : (array) get_option( 'epiphyt_update' );
		$option['plugins'] = array_keys( $this->plugin_data );
		
		if ( is_multisite() ) {
			update_site_option( 'epiphyt_update', $option );
		}
		else {
			update_option( 'epiphyt_update', $option );
		}
		
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
		$option = is_multisite() ? (array) get_site_option( 'epiphyt_update' ) : (array) get_option( 'epiphyt_update' );
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
			// phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain
			/* translators: the website url for news etc. */
			'changelog' => sprintf( __( 'Please visit our website to receive notifications about the latest updates:<br><a href="%1$s">%1$s</a>', self::$text_domain ), 'https://epiph.yt' ),
			// phpcs:enable
		];
		$info->slug = $plugin_data['slug'];
		$info->version = $plugin_data['version'];
		
		return $info;
	}
	
	/**
	 * Get an option or a site option with the same name.
	 * The site option is received if there is no option
	 * with the same name.
	 * 
	 * @param string $option The option you want to get
	 * @return mixed|void
	 */
	protected static function get_real_option( $option ) {
		if ( ! is_string( $option ) ) return;
		
		if ( ! is_multisite() ) {
			// try receive option
			$options = get_option( $option );
			
			if ( empty( $options ) ) {
				$options = get_site_option( $option );
			}
		}
		else {
			$options = get_site_option( $option );
		}
		
		return $options;
	}
	
	/**
	 * Send request to update server.
	 * 
	 * @param array|string $args Query arguments
	 * @param bool $raw Should the response returned in raw format or not 
	 * @return bool|object
	 */
	public function request( $args = '', $raw = false ) {
		$option = is_multisite() ? (array) get_site_option( 'epiphyt_update' ) : (array) get_option( 'epiphyt_update' );
		
		// prepare args
		$args = wp_parse_args( $args, $option );
		$args = array_filter( $args );
		// get home URL
		$args = array_merge( $args, [ 'platform' => $this->home_url ] );
		// get license key if available, otherwise return false
		if ( self::get_real_option( self::$text_domain . '_license_options' ) ) {
			$args = array_merge( $args, self::get_real_option( self::$text_domain . '_license_options' ) );
		}
		else {
			return false;
		}
		// get product id
		$args = array_merge( $args, [ 'product_id' => $this->product_id ] );
		
		// request plugin data
		$request = wp_remote_post( $this->update_url, [ 'body' => $args ] );
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
		$plugin_obj = new \stdClass();
		$plugin_obj->new_version = $plugin['version'];
		$plugin_obj->package = isset( $plugin['download_url'] ) ? $plugin['download_url'] : '';
		$plugin_obj->plugin = $this->plugin_base;
		$plugin_obj->slug = $this->plugin_base;
		$plugin_obj->url = $plugin['homepage'];
		$array = [];
		$array[ $this->plugin_base ] = $plugin_obj;
		
		return $array;
	}
}
