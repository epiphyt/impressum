<?php
declare(strict_types = 1);

namespace epiphyt\Impressum;

/**
 * Helper functions for the Impressum plugin.
 * 
 * @author	Epiphyt
 * @license	GPL2
 * @package	epiphyt\Impressum
 */
// phpcs:ignore Universal.Classes.RequireFinalClass, Squiz.Commenting.ClassComment.Missing
class Helper {
	/**
	 * @var		bool Whether the option cache invalidation hooks are registered
	 */
	private static bool $cache_hooks_registered = false;
	
	/**
	 * @var		array<string, mixed> Memoized option values, keyed by scope and option name
	 */
	private static array $options = [];
	
	/**
	 * @var		string Explicitly set option scope, empty to determine it automatically
	 */
	private static string $scope = '';
	
	/**
	 * Print out the settings fields for a particular settings section.
	 * 
	 * Part of the Settings API. Use this in a settings page to output
	 * a specific section. Should normally be called by do_settings_sections()
	 * rather than directly.
	 * 
	 * do_settings_fields() from core with adjustments to the <th> element.
	 * 
	 * @deprecated	3.1.0 The settings page is rendered by the settings app.
	 * @global		array	$wp_settings_fields Storage array of settings fields and their pages/sections.
	 * @since		2.1.0
	 * 
	 * @param	string	$page Slug title of the admin page whose settings fields you want to show.
	 * @param	string	$section Slug title of the settings section whose fields you want to show.
	 */
	public static function do_settings_fields( string $page, string $section ): void {
		\_deprecated_function( __METHOD__, '3.1.0' );
		
		global $wp_settings_fields;
		
		if ( ! isset( $wp_settings_fields[ $page ][ $section ] ) ) {
			return;
		}
		
		$field_data = self::get_option( 'impressum_field_data', ! \is_network_admin() );
		
		foreach ( (array) $wp_settings_fields[ $page ][ $section ] as $field ) {
			$class = '';
			
			if ( ! empty( $field['args']['class'] ) ) {
				$class = ' class="' . \esc_attr( $field['args']['class'] ) . '"';
			}
			
			echo "<tr{$class}>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			
			if ( ! empty( $field['args']['label_for'] ) ) {
				$title = $field['title'];
				
				if ( isset( $field_data[ $field['args']['label_for'] ]['name'] ) ) {
					$title = $field_data[ $field['args']['label_for'] ]['name'];
				}
				
				echo '<th scope="row">';
				
				if ( ! empty( $field_data[ $field['args']['label_for'] ]['name'] ) ) {
					echo '<input type="hidden" name="impressum_field_data[' . \esc_attr( $field['args']['label_for'] ) . '][name]" value="' . \esc_attr( $field_data[ $field['args']['label_for'] ]['name'] ) . '" />';
				}
				
				echo '<label for="' . \esc_attr( $field['args']['label_for'] ) . '">' . \esc_html( $title ) . '</label></th>';
			}
			else {
				echo '<th scope="row">' . \esc_html( $field['title'] ) . '</th>';
			}
			
			echo '<td>';
			\call_user_func( $field['callback'], $field['args'] ); // phpcs:ignore NeutronStandard.Functions.DisallowCallUserFunc.CallUserFunc
			echo '</td>';
			echo '</tr>';
		}
	}
	
	/**
	 * Prints out all settings sections added to a particular settings page
	 * 
	 * Part of the Settings API. Use this in a settings page callback function
	 * to output all the sections and fields that were added to that $page with
	 * add_settings_section() and add_settings_field()
	 * 
	 * do_settings_sections() from core with custom do_settings_fields()
	 * 
	 * @deprecated	3.1.0 The settings page is rendered by the settings app.
	 * @global		array	$wp_settings_sections Storage array of all settings sections added to admin pages.
	 * @global		array	$wp_settings_fields Storage array of settings fields and info about their pages/sections.
	 * @since		2.1.0
	 * 
	 * @param	string	$page The slug name of the page whose settings sections you want to output.
	 */
	public static function do_settings_sections( string $page ): void {
		\_deprecated_function( __METHOD__, '3.1.0' );
		
		global $wp_settings_sections, $wp_settings_fields;
		
		if ( ! isset( $wp_settings_sections[ $page ] ) ) {
			return;
		}
		
		foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
			if ( $section['title'] ) {
				echo "<h2>{$section['title']}</h2>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			
			if ( $section['callback'] ) {
				\call_user_func( $section['callback'], $section ); // phpcs:ignore NeutronStandard.Functions.DisallowCallUserFunc.CallUserFunc
			}
			
			if (
				! isset( $wp_settings_fields )
				|| ! isset( $wp_settings_fields[ $page ] )
				|| ! isset( $wp_settings_fields[ $page ][ $section['id'] ] )
			) {
				continue;
			}
			echo '<table class="form-table" role="presentation">';
			self::do_settings_fields( $page, $section['id'] );
			echo '</table>';
		}
	}
	
	/**
	 * Get an option from the database.
	 * Within the network scope, the network wide option is returned.
	 * Merging network wide and site options is part of the Plus version only.
	 * 
	 * @param	string	$option The option you want to get
	 * @param	bool	$useless Useless in the free version
	 * @return	mixed Option value
	 */
	public static function get_option( string $option, bool $useless = false ): mixed { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		self::register_cache_hooks();
		
		$scope = self::get_scope();
		$key = $scope . ':' . $option;
		
		if ( ! \array_key_exists( $key, self::$options ) ) {
			self::$options[ $key ] = $scope === 'network' ? \get_site_option( $option ) : \get_option( $option );
		}
		
		return self::$options[ $key ];
	}
	
	/**
	 * Get the current option scope.
	 * 
	 * A REST request is never a network admin request, so the scope cannot be
	 * determined reliably on its own and needs to be set via with_scope().
	 * 
	 * @since	3.1.0
	 * 
	 * @return	string Either 'network' or 'site'
	 */
	public static function get_scope(): string {
		if ( self::$scope !== '' ) {
			return self::$scope;
		}
		
		return \is_network_admin() ? 'network' : 'site';
	}
	
	/**
	 * Run a callback within an explicit option scope.
	 * 
	 * @since	3.1.0
	 * 
	 * @param	string		$scope Either 'network' or 'site'
	 * @param	callable	$callback Callback to run within the scope
	 * @return	mixed Return value of the callback
	 */
	public static function with_scope( string $scope, callable $callback ): mixed {
		$previous_scope = self::$scope;
		self::$scope = $scope;
		
		try {
			return $callback();
		}
		finally {
			self::$scope = $previous_scope;
		}
	}
	
	/**
	 * Invalidate a memoized option value.
	 * Hooked to option write actions so the memo never serves stale data.
	 * 
	 * @param	string	$option Name of the option that changed
	 */
	public static function clear_option_cache( string $option ): void {
		unset( self::$options[ 'network:' . $option ], self::$options[ 'site:' . $option ] );
	}
	
	/**
	 * Register the option cache invalidation hooks once.
	 */
	private static function register_cache_hooks(): void {
		if ( self::$cache_hooks_registered ) {
			return;
		}
		
		self::$cache_hooks_registered = true;
		
		$hooks = [
			'add_site_option',
			'added_option',
			'delete_site_option',
			'deleted_option',
			'update_site_option',
			'updated_option',
		];
		
		foreach ( $hooks as $hook ) {
			\add_action( $hook, [ self::class, 'clear_option_cache' ] );
		}
	}
}
