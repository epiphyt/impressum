<?php
namespace epiphyt\Impressum;

/*
Plugin Name:		Impressum
Plugin URI:			https://wordpress.org/plugins/impressum/
Description:		Simple Imprint Generator
Version:			2.1.3
Requires at least:	5.0
Requires PHP:		5.6
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
\define( 'EPI_IMPRESSUM_VERSION', '2.1.3' );

/**
 * Autoload all necessary classes.
 * 
 * @param	string	$class_name The class name of the auto-loaded class
 */
\spl_autoload_register( static function( $class_name ) {
	if ( \strpos( $class_name, __NAMESPACE__ . '\\' ) !== 0 ) {
		return;
	}
	
	$path = \explode( '\\', $class_name );
	$filename = \str_replace( '_', '-', \strtolower( \array_pop( $path ) ) );
	$class_name = \str_replace(
		[ 'epiphyt\impressum\\', '\\', '_' ],
		[ '', '/', '-' ],
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

// deprecated, don't use anymore
if ( ! \defined( 'IMPRESSUM_BASE' ) ) \define( 'IMPRESSUM_BASE', \plugin_basename( __FILE__ ) );

Impressum::get_instance()->set_plugin_file( __FILE__ );
Impressum::get_instance()->init();
