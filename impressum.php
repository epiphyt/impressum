<?php
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
require_once( __DIR__ . '/inc/impressum_backend.class.php' );
require_once( __DIR__ . '/inc/impressum_frontend.class.php' );

$impressum_backend = new Impressum_Backend( __FILE__ );
$impressum_frontend = new Impressum_Frontend( __FILE__ );

define( 'IMPRESSUM_BASE', plugin_basename( __FILE__ ) );