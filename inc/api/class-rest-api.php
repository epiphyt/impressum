<?php
declare(strict_types = 1);

namespace epiphyt\Impressum\api;

/**
 * REST API related functionality.
 * 
 * @since	3.1.0
 * 
 * @author	Epiphyt
 * @license	GPL2
 * @package	epiphyt\Impressum
 */
final class REST_API {
	/**
	 * Initialize functionality.
	 */
	public function init(): void {
		\add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}
	
	/**
	 * Register all REST routes.
	 * 
	 * Runs after the settings have been registered on init, so the registry is
	 * populated and can be used to build the route arguments.
	 */
	public function register_routes(): void {
		$container = \epiphyt\Impressum\get_container();
		$controller = new Settings_Controller(
			$container->get( 'settings-registry' ),
			$container->get( 'settings-schema' ),
			$container->get( 'settings-validator' )
		);
		
		$controller->register_routes();
	}
}
