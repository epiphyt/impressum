<?php
namespace epiphyt\Impressum;
use function array_pop;
use function define;
use function defined;
use function explode;
use function file_exists;
use function in_array;
use function plugin_basename;
use function preg_quote;
use function preg_replace;
use function spl_autoload_register;
use function str_replace;
use function strpos;
use function strrpos;
use function strtolower;
use function substr;

/*
Plugin Name:		Impressum
Plugin URI:			https://wordpress.org/plugins/impressum/
Description:		Simple Imprint Generator
Version:			2.0.5
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

/**
 * Autoload all necessary classes.
 * 
 * @param	string	$class The class name of the autoloaded class
 */
spl_autoload_register( function( $class ) {
	$path = explode( '\\', $class );
	$filename = str_replace( '_', '-', strtolower( array_pop( $path ) ) );
	$class = str_replace(
		[ 'epiphyt\impressum\\', '\\', '_' ],
		[ '', '/', '-' ],
		strtolower( $class )
	);
	$file_type = ( strpos( $filename, '-' ) !== false ? substr( $filename, strrpos( $filename, '-' ) + 1 ) : 'class' );
	
	if ( ! in_array( $file_type, [ 'class', 'controller', 'interface' ], true ) ) {
		$file_type = 'class';
	}
	
	if ( $file_type === 'class' ) {
		$class = preg_replace( '/' . preg_quote( $filename, '/' ) . '$/', $file_type . '-' . $filename, $class );
	}
	else {
		$filename = str_replace( '-' . $file_type, '', $filename );
		$class = str_replace( $filename . '-' . $file_type, $file_type . '-' . $filename, $class );
	}
	
	$maybe_file = __DIR__ . '/inc/' . $class . '.php';
	
	if ( file_exists( $maybe_file ) ) {
		require_once $maybe_file ;
	}
} );

if ( ! defined( 'IMPRESSUM_BASE' ) ) define( 'IMPRESSUM_BASE', plugin_basename( __FILE__ ) );

Impressum::get_instance()->set_plugin_file( __FILE__ );
Impressum::get_instance()->init();
