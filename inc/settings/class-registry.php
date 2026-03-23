<?php
declare(strict_types = 1);

namespace epiphyt\Impressum\settings;

use epiphyt\Impressum\Helper;

/**
 * Settings registry functionality.
 * 
 * @since	3.0.0
 * 
 * @author	Epiphyt
 * @license	GPL2
 * @package	epiphyt\Impressum
 */
final class Registry {
	/**
	 * @var		?\epiphyt\Impressum\Helper Helper class
	 */
	private ?\epiphyt\Impressum\Helper $helper = null;
	
	/**
	 * @var		string[] List of setting types
	 */
	private array $setting_types = [];
	
	/**
	 * @var		\epiphyt\Impressum\settings\Setting[] List of block classes
	 */
	private array $settings = [];
	
	/**
	 * Settings registry constructor.
	 * 
	 * @param	\epiphyt\Impressum\Helper	$helper Helper class
	 */
	public function __construct( Helper $helper ) {
		$this->helper = $helper;
	}
	
	/**
	 * Get a registered setting.
	 * 
	 * @param	string	$key Setting key
	 * @return	?\epiphyt\Impressum\settings\Setting Setting or null
	 */
	public function get_setting( string $key ): ?Setting {
		return $this->settings[ $key ] ?? null;
	}
	
	/**
	 * Get all setting types.
	 * 
	 * @return	string[] List of setting types
	 */
	public function get_setting_types(): array {
		return $this->setting_types;
	}
	
	/**
	 * Get all registered settings.
	 * 
	 * @param	string	$type Settings type
	 * @return	\epiphyt\Impressum\settings\Setting[] Registered settings
	 */
	public function get_settings( string $type = '' ): array {
		if ( empty( $type ) ) {
			return $this->settings;
		}
		
		$settings = $this->settings;
		
		foreach ( $settings as $key => $setting ) {
			if ( $setting->get_data( 'type' ) !== $type ) {
				unset( $settings[ $key ] );
			}
		}
		
		return $settings;
	}
	
	/**
	 * Register a setting.
	 * 
	 * @param	string					$setting_name Name for the setting
	 * @param	array<string, mixed>	$setting_data Data for the setting
	 */
	public function register( string $setting_name, array $setting_data ): void {
		$setting = new Setting( $setting_name, $setting_data, $this->helper );
		$key = \sprintf( '%1$s_%2$s', $setting->type, $setting->name );
		$this->settings[ $key ] = $setting;
		
		if ( ! \in_array( $setting->type, $this->setting_types, true ) ) {
			$this->setting_types[] = $setting->type;
		}
	}
	
	/**
	 * Register multiple settings.
	 * 
	 * @param	array<string, mixed[]>	$settings_data List of settings data
	 */
	public function register_multiple( array $settings_data ): void {
		foreach ( $settings_data as $setting_name => $setting_data ) {
			$this->register( $setting_name, $setting_data );
		}
	}
}
