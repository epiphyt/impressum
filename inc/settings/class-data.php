<?php
declare(strict_types = 1);

namespace epiphyt\Impressum\settings;

/**
 * Settings data related functionality.
 * 
 * @since	3.0.0
 * 
 * @author	Epiphyt
 * @license	GPL2
 * @package	epiphyt\Impressum
 */
final class Data {
	/**
	 * @var		?\epiphyt\Impressum\settings\Registry Settings registry
	 */
	private ?\epiphyt\Impressum\settings\Registry $registry = null;
	
	/**
	 * Data constructor.
	 * 
	 * @param	\epiphyt\Impressum\settings\Registry	$registry Settings registry
	 */
	public function __construct( Registry $registry ) {
		$this->registry = $registry;
	}
	
	/**
	 * Initialize functionality.
	 */
	public function init(): void {
		// must be executed after settings registration
		\add_action( 'init', [ $this, 'register_filters' ], 15 );
	}
	
	/**
	 * Register filters.
	 */
	public function register_filters(): void {
		foreach ( $this->registry->get_setting_types() as $setting_type ) {
			\add_filter( "pre_update_option_{$setting_type}", [ $this, 'sanitize_update_option' ], 10, 3 );
		}
	}
	
	/**
	 * Sanitize an option during update.
	 * Makes sure that only registered settings are updated.
	 * 
	 * @param	mixed	$value New value
	 * @param	mixed	$old_value Old value
	 * @param	string	$option Option name to update
	 * @return	mixed Sanitized new value
	 */
	public function sanitize_update_option( mixed $value, mixed $old_value, string $option ): mixed {
		$settings = $this->registry->get_settings( $option );
		
		if ( ! \is_array( $value ) ) {
			return [];
		}
		
		foreach ( $value as $option_name => $option_value ) {
			if ( ! isset( $settings[ "{$option}_{$option_name}" ] ) ) {
				unset( $value[ $option_name ] );
				
				continue;
			}
			
			$value[ $option_name ] = $this->sanitize_value( $settings[ "{$option}_{$option_name}" ], $option_value );
		}
		
		return $value;
	}
	
	/**
	 * Sanitize a single setting value depending on its field type.
	 * 
	 * @param	\epiphyt\Impressum\settings\Setting	$setting Setting the value belongs to
	 * @param	mixed								$value Value to sanitize
	 * @return	mixed Sanitized value
	 */
	private function sanitize_value( Setting $setting, mixed $value ): mixed {
		if ( \is_array( $value ) ) {
			return \map_deep( $value, 'sanitize_text_field' );
		}
		
		// leave non-string scalars (booleans, integers) untouched
		if ( ! \is_string( $value ) ) {
			return $value;
		}
		
		return match ( $setting->get_data( 'setting_callback' ) ) {
			'email' => \sanitize_email( $value ),
			'textarea' => \sanitize_textarea_field( $value ),
			default => \sanitize_text_field( $value ),
		};
	}
}
