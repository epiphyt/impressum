<?php
declare(strict_types = 1);

namespace epiphyt\Impressum\settings;

use epiphyt\Impressum\Helper;

/**
 * A setting representation.
 * 
 * @since	3.0.0
 * 
 * @author	Epiphyt
 * @license	GPL2
 * @package	epiphyt\Impressum
 */
final class Setting {
	/**
	 * @var		?\epiphyt\Impressum\Helper Helper class
	 */
	private ?\epiphyt\Impressum\Helper $helper = null;
	
	/**
	 * @var		array{data_type: array{enum?: string[], type?: string}, description: string, hide_output: bool, setting_attributes: mixed[], setting_callback: ?callable, setting_page: string, setting_section: string, title: string, type: string} Setting data 
	 */
	public array $data = [];
	
	/**
	 * @var		string Setting name
	 */
	public string $name = '';
	
	/**
	 * @var		string Setting type
	 */
	public string $type = '';
	
	/**
	 * @var		mixed Setting value
	 */
	public mixed $value = null;
	
	/**
	 * Setting constructor.
	 * 
	 * @param	string						$name Setting name
	 * @param	mixed[]						$data Setting data
	 * @param	\epiphyt\Impressum\Helper	$helper Helper class
	 */
	public function __construct( string $name, array $data, Helper $helper ) {
		$this->helper = $helper;
		$this->name = $name;
		$this->data = $this->migrate_old_data( $data );
		$this->type = $this->data['type'];
	}
	
	/**
	 * Get the setting's data
	 * 
	 * @param	string	$key Optional setting key to get the data from
	 * @return	array{custom_title?: string, data_type: array{enum?: string[], type?: string}, description: string, hide_output: bool, setting_attributes: mixed[], setting_callback: ?callable, setting_page: string, setting_section: string, title: string, type: string}|mixed Setting data
	 */
	public function get_data( string $key = '' ): mixed {
		if ( empty( $key ) ) {
			return $this->data;
		}
		
		return $this->data[ $key ] ?? null;
	}
	
	/**
	 * Get the setting's title
	 * 
	 * @return	string Setting title
	 */
	public function get_title(): string {
		return $this->data['custom_title'] ?? $this->data['title'];
	}
	
	/**
	 * Get the setting's value.
	 * 
	 * @param	bool	$merge Whether to merge with global setting (only in Impressum Plus)
	 * @return	mixed Setting's value
	 */
	public function get_value( bool $merge = false ): mixed {
		if ( $this->value !== null ) {
			return $this->value;
		}
		
		/** @disregard P1009 Existence of \epiphyt\Impressum_Plus\Helper is checked in class_exists */
		if (
			$merge
			&& (
				! \class_exists( '\epiphyt\Impressum_Plus\Helper' )
				|| ! $this->helper instanceof \epiphyt\Impressum_Plus\Helper
			)
		) {
			$merge = false;
		}
		
		$settings_data = $this->helper::get_option( $this->type, $merge );
		$this->value = $settings_data[ $this->name ] ?? null;
		
		return $this->value;
	}
	
	/**
	 * Migrate old data to current structure.
	 * 
	 * @param	mixed[]	$data Old data format
	 * @return	array{data_type: array{enum?: string[], type?: string}, description: string, hide_output: bool, setting_attributes: mixed[], setting_callback: ?callable, setting_page: string, setting_section: string, title: string, type: string} New data format
	 */
	private function migrate_old_data( array $data ): array {
		$field_data = Helper::get_option( 'impressum_field_data', ! \is_network_admin() );
		$settings_data = [
			'custom_title' => $field_data[ $this->name ]['name'] ?? null,
			'data_type' => [],
			'description' => $data['api']['description'] ?? '',
			'hide_output' => $data['no_output'] ?? false,
			'setting_attributes' => $data['args'] ?? [],
			'setting_callback' => $data['callback'] ?? null,
			'setting_page' => $data['page'] ?? '',
			'setting_section' => $data['section'] ?? '',
			'title' => $data['title'] ?? '',
			'type' => $data['option'] ?? ( $data['args']['setting'] ?? 'impressum_imprint_options' ),
		];
		
		if ( isset( $data['api']['enum'] ) ) {
			$settings_data['data_type']['enum'] = $data['api']['enum'];
		}
		
		if ( isset( $data['api']['type'] ) ) {
			$settings_data['data_type']['type'] = $data['api']['type'];
		}
		
		return $settings_data;
	}
	
	/**
	 * Set a new value for the setting.
	 * 
	 * @param	mixed	$new_value New value to set
	 * @return	bool Whether new value has been set successfully
	 */
	public function set_value( mixed $new_value ): bool {
		$settings_data = $this->helper::get_option( $this->type );
		$settings_data[ $this->name ] = $new_value;
		$this->value = $new_value;
		
		return \update_option( $this->type, $settings_data );
	}
}
