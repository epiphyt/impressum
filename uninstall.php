<?php
namespace epiphyt\Impressum;

// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) die;

$options = [
	'impressum_imprint_options',
	'impressum_privacy_options',
];

foreach ( $options as $option ) {
	delete_option( $option );
	
	// for site options in multisite
	delete_site_option( $option );
}