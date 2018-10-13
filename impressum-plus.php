<?php
namespace epiphyt\Impressum;
use epiphyt\Update\Epiphyt_License;
use epiphyt\Update\Epiphyt_Update;

/*
Plugin Name:	Impressum Plus
Plugin URI:		https://impressum.plus/
Description:	Enhanced Impressum Generator
Version:		1.0.0
Author:			Epiphyt
Author URI:		https://epiph.yt/
License:		GPL3
License URI:	https://www.gnu.org/licenses/gpl-3.0.html
Text Domain:	impressum
Domain Path:	/languages


Impressum is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
any later version.

Impressum is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Impressum. If not, see https://www.gnu.org/licenses/gpl-3.0.html.
*/

// get multisite or singlesite home URL
$home = is_multisite() ? network_site_url() : home_url();


/**
 * Autoload all necessary classes.
 * 
 * @param	string		$class The class name of the autoloaded class
 */
\spl_autoload_register( function( $class ) {
	$path = \explode( '\\', $class );
	$filename = \str_replace( '_', '-', \strtolower( \array_pop( $path ) ) );
	$class = \str_replace(
		[ 'epiphyt\impressum\\', 'epiphyt\update\\', '\\', '_' ],
		[ '', 'lib\\', '/', '-' ],
		\strtolower( $class )
	);
	$class = \str_replace( $filename, 'class-' . $filename, $class );
	$maybe_file = __DIR__ . '/inc/' . $class . '.php';
	
	if ( \file_exists( $maybe_file ) ) {
		require_once( $maybe_file );
	}
} );

new Impressum_Backend( __FILE__ );
new Impressum_Frontend( __FILE__ );

if ( ! defined( 'IMPRESSUM_BASE' ) ) define( 'IMPRESSUM_BASE', plugin_basename( __FILE__ ) );

new Epiphyt_Update( IMPRESSUM_BASE, 'impressum', 'Impressum Plus', $home );
Epiphyt_Update::$update_slug = 'impressum';
new Epiphyt_License( 'impressum_license_options', 'Impressum Plus', $home );
Epiphyt_License::$update_slug = 'impressum';
