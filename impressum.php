<?php
declare(strict_types = 1);

namespace epiphyt\Impressum;

use epiphyt\Basic_Auth\Basic_Auth;
use epiphyt\Impressum\api\REST_API;
use epiphyt\Impressum\blocks\Block_Registry;
use epiphyt\Impressum\settings\Data;
use epiphyt\Impressum\settings\Legacy_Renderer;
use epiphyt\Impressum\settings\Registry;
use epiphyt\Impressum\settings\Schema;
use epiphyt\Impressum\settings\Tabs;
use epiphyt\Impressum\settings\Validator;

/*
Plugin Name:		Impressum
Plugin URI:			https://wordpress.org/plugins/impressum/
Description:		Simple Imprint Generator
Version:			3.0.2
Requires at least:	6.8
Requires PHP:		8.1
Author:				Epiphyt
Author URI:			https://epiph.yt/en/
License:			GPL2
License URI:		https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:		impressum
Domain Path:		/languages

Impressum is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Impressum is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Impressum. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/
\defined( 'ABSPATH' ) || exit;

if ( ! \defined( 'EPI_IMPRESSUM_BASE' ) ) {
	if ( \file_exists( \WP_PLUGIN_DIR . '/impressum/' ) ) {
		\define( 'EPI_IMPRESSUM_BASE', \WP_PLUGIN_DIR . '/impressum/' );
	}
	else if ( \file_exists( \WPMU_PLUGIN_DIR . '/impressum/' ) ) {
		\define( 'EPI_IMPRESSUM_BASE', \WPMU_PLUGIN_DIR . '/impressum/' );
	}
	else {
		\define( 'EPI_IMPRESSUM_BASE', \plugin_dir_path( __FILE__ ) );
	}
}

\define( 'EPI_IMPRESSUM_FILE', \EPI_IMPRESSUM_BASE . \basename( __FILE__ ) );
\define( 'EPI_IMPRESSUM_URL', \plugin_dir_url( \EPI_IMPRESSUM_FILE ) );
\define( 'EPI_IMPRESSUM_VERSION', '3.0.2' );

/**
 * Autoload all necessary classes.
 * 
 * @param	string	$class_name The class name of the auto-loaded class
 */
\spl_autoload_register( static function( $class_name ): void {
	$namespaces = [
		'epiphyt\Basic_Auth\\',
		__NAMESPACE__ . '\\',
	];
	$resolve = false;
	
	foreach ( $namespaces as $namespace ) {
		if ( \str_starts_with( $class_name, $namespace ) ) {
			$resolve = true;
			
			break;
		}
	}
	
	if ( ! $resolve ) {
		return;
	}
	
	$path = \explode( '\\', $class_name );
	$filename = \str_replace( '_', '-', \strtolower( \array_pop( $path ) ) );
	$class_name = \str_replace(
		[ 'epiphyt\basic_auth\\', 'epiphyt\impressum\\', '\\', '_' ],
		[ 'lib\\', '', '/', '-' ],
		\strtolower( $class_name )
	);
	
	foreach ( [ 'class', 'interface', 'trait' ] as $file_type ) {
		$type_class_name = \preg_replace( '/' . \preg_quote( $filename, '/' ) . '$/', $file_type . '-' . $filename, $class_name );
		$maybe_file = __DIR__ . '/inc/' . $type_class_name . '.php';
		
		if ( \file_exists( $maybe_file ) ) {
			require_once $maybe_file;
			break;
		}
	}
} );

/**
 * Get the plugin container.
 * 
 * @return	\epiphyt\Impressum\Plugin_Container The plugin container
 */
function get_container(): Plugin_Container {
	global $impressum_container;
	
	if ( ! $impressum_container instanceof Plugin_Container ) {
		$container = new Plugin_Container();
		$container->set(
			'helper',
			static function(): Helper {
				return new Helper();
			}
		);
		$container->set(
			'block-registry',
			static function(): Block_Registry {
				return new Block_Registry();
			}
		);
		$container->set(
			'settings-registry',
			static function(): Registry {
				return new Registry( \epiphyt\Impressum\get_container()->get( 'helper' ) );
			}
		);
		$container->set(
			'settings-validator',
			static function(): Validator {
				return new Validator( \epiphyt\Impressum\get_container()->get( 'settings-registry' ) );
			}
		);
		$container->set(
			'settings-legacy-renderer',
			static function(): Legacy_Renderer {
				return new Legacy_Renderer();
			}
		);
		$container->set(
			'settings-tabs',
			static function(): Tabs {
				return new Tabs(
					\epiphyt\Impressum\get_container()->get( 'settings-registry' ),
					\epiphyt\Impressum\get_container()->get( 'settings-legacy-renderer' )
				);
			}
		);
		$container->set(
			'settings-schema',
			static function(): Schema {
				return new Schema(
					\epiphyt\Impressum\get_container()->get( 'settings-registry' ),
					\epiphyt\Impressum\get_container()->get( 'settings-validator' ),
					\epiphyt\Impressum\get_container()->get( 'settings-legacy-renderer' ),
					\epiphyt\Impressum\get_container()->get( 'settings-tabs' )
				);
			}
		);
		$container->set(
			'admin',
			static function(): Admin {
				return new Admin(
					\epiphyt\Impressum\get_container()->get( 'settings-registry' ),
					\epiphyt\Impressum\get_container()->get( 'settings-validator' )
				);
			}
		);
		$container->set(
			'frontend',
			static function(): Frontend {
				return Frontend::get_instance();
			}
		);
		$container->set(
			'settings-data',
			static function(): Data {
				return new Data( \epiphyt\Impressum\get_container()->get( 'settings-registry' ) );
			}
		);
		$container->set(
			'plugin',
			static function(): Plugin {
				return new Plugin( \epiphyt\Impressum\get_container()->get( 'settings-registry' ) );
			}
		);
		$container->set(
			'rest-api',
			static function(): REST_API {
				return new REST_API();
			}
		);
		$container->set(
			'site-health',
			static function(): Site_Health {
				return new Site_Health( \epiphyt\Impressum\get_container()->get( 'admin' ) );
			}
		);
		$impressum_container = $container;
	}
	
	return $impressum_container;
}

/**
 * Initialize the plugin.
 */
function initialize_plugin(): void {
	( new Basic_Auth() )->load();
	\epiphyt\Impressum\get_container()->get( 'plugin' )->init();
	\epiphyt\Impressum\get_container()->get( 'admin' )->init();
	\epiphyt\Impressum\get_container()->get( 'block-registry' )->init();
	\epiphyt\Impressum\get_container()->get( 'settings-data' )->init();
	\epiphyt\Impressum\get_container()->get( 'rest-api' )->init();
	\epiphyt\Impressum\get_container()->get( 'site-health' )->init();
}

/**
 * Activate the plugin.
 * 
 * The plugin is resolved not before the activation, since resolving it while
 * loading the plugin would instantiate its dependencies before Impressum Plus
 * has been able to replace them.
 * 
 * @since	3.1.0
 */
function activate_plugin(): void {
	\epiphyt\Impressum\get_container()->get( 'plugin' )->activate();
}

/**
 * Deactivate the plugin.
 * 
 * @since	3.1.0
 */
function deactivate_plugin(): void {
	\epiphyt\Impressum\get_container()->get( 'plugin' )->deactivate();
}

\add_action( 'plugins_loaded', __NAMESPACE__ . '\initialize_plugin' );
\register_activation_hook( \EPI_IMPRESSUM_FILE, __NAMESPACE__ . '\activate_plugin' );
\register_deactivation_hook( \EPI_IMPRESSUM_FILE, __NAMESPACE__ . '\deactivate_plugin' );
