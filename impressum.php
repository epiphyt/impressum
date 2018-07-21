<?php
namespace epiphyt\Impressum;
use epiphyt\Update\Epiphyt_License;
use epiphyt\Update\Epiphyt_Update;

/*
Plugin Name:	Impressum
Plugin URI:		https://impressum.plus
Description:	Simple Impressum Generator
Version:		0.1
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

if ( ! class_exists( 'Impressum_Backend' ) ) {
	require plugin_dir_path( __FILE__ ) . '/inc/impressum_backend.class.php';
	new Impressum_Backend( __FILE__ );
}

if ( ! class_exists( 'Impressum_Frontend' ) ) {
	require plugin_dir_path( __FILE__ ) . '/inc/impressum_frontend.class.php';
	new Impressum_Frontend( __FILE__ );
}


if ( ! defined( 'IMPRESSUM_BASE' ) ) define( 'IMPRESSUM_BASE', plugin_basename( __FILE__ ) );

if ( ! class_exists( 'Epiphyt_Update' ) ) {
	require plugin_dir_path( __FILE__ ) . '/inc/lib/epiphyt_update.class.php';
	
	// get mutlisite or singlesite home URL
	$home = is_multisite() ? network_site_url() : home_url();
	
	new Epiphyt_Update( IMPRESSUM_BASE, 'impressum', 'Impressum Plus', $home );
	Epiphyt_Update::$update_slug = 'impressum';
}

if ( ! class_exists( 'Epiphyt_License' ) ) {
	require plugin_dir_path( __FILE__ ) . '/inc/lib/epiphyt_license.class.php';
	
	new Epiphyt_License( 'impressum_license_options', 'Impressum Plus', $home );
}
