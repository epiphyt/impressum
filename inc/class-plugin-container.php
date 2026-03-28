<?php
declare(strict_types = 1);

namespace epiphyt\Impressum;

/**
 * Plugin dependency injection container.
 * 
 * @author	Epiphyt
 * @license	GPL2
 * @package	epiphyt\Impressum
 */
final class Plugin_Container {
	/**
	 * @var		callable[] Active instances
	 */
	private array $instances = [];
	
	/**
	 * @var		callable[] Available services
	 */
	private array $services = [];
	
	/**
	 * Get a service instance or a WP_Error object.
	 * 
	 * @param	string	$id Service ID
	 * @return	object Service instance object or \WP_Error object
	 */
	public function get( string $id ): object {
		if ( ! $this->has( $id ) ) {
			return new \WP_Error( 'service_not_found', \sprintf( 'Service not found: %s', $id ) );
		}
		
		if ( ! isset( $this->instances[ $id ] ) ) {
			$this->instances[ $id ] = $this->services[ $id ]( $this );
		}
		
		return $this->instances[ $id ];
	}
	
	/**
	 * Check, whether a service with the given ID exists.
	 * 
	 * @param	string	$id Service ID
	 * @return	bool Whether a service with the given ID exists
	 */
	public function has( string $id ): bool {
		return isset( $this->services[ $id ] );
	}
	
	/**
	 * Set a service.
	 * 
	 * @param	string		$id Service ID
	 * @param	callable	$factory Service factory
	 */
	public function set( string $id, callable $factory ): void {
		$this->services[ $id ] = $factory;
		
		if ( isset( $this->instances[ $id ] ) ) {
			unset( $this->instances[ $id ] );
		}
	}
}
