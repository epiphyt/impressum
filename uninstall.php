<?php
namespace epiphyt\Impressum;

// if uninstall.php is not called by WordPress, die
if ( ! \defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

// do nothing if Impressum Plus is also installed
if ( \is_plugin_active( 'impressum-plus/impressum-plus.php' ) ) {
	return;
}

$options = [
	'dismissed-impressum_validation_notice',
	'dismissed-impressum_welcome_notice',
	'impressum_field_data',
	'impressum_imprint_options',
];

foreach ( $options as $option ) {
	\delete_option( $option );
	// for site options in multisite
	\delete_site_option( $option );
}

// remove the scheduled cron event
\wp_clear_scheduled_hook( 'impressum_twice_daily_cron' );
