<?php
/*
Plugin Name:	Impressum
Plugin URI:		https://impressum.plus
Description:	Simple Impressum Generator
Version:		0.1
Author:			Matthias Kittsteiner, Simon Kraft
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

/**
 * @internal never define functions inside callbacks.
 * these functions could be run multiple times; this would result in a fatal error.
 */
 
/**
 * custom option and settings
 */
function impressum_settings_init() {
	// register a new setting for "impressum" page
	register_setting( 'impressum', 'impressum_options' );
	
	// register a new section in the "impressum" page
	add_settings_section(
		'impressum_section_developers',
		__( '', 'impressum' ),
		null,
		'impressum'
	);
	
	/**
	 * Register option fields
	 */
	
	// legal entity
	add_settings_field(
		'impressum_field_legal_entity',
		__( 'Legal Entity', 'impressum' ),
		'impressum_field_legal_entity_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'impressum_field_legal_entity',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// name
	add_settings_field(
		'impressum_field_name',
		__( 'Name', 'impressum' ),
		'impressum_field_name_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'impressum_field_name',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// address
	add_settings_field(
		'impressum_field_address',
		__( 'Address', 'impressum' ),
		'impressum_field_address_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'impressum_field_address',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// address alternative
	add_settings_field(
		'impressum_field_address_alternative',
		__( 'Alternative Address', 'impressum' ),
		'impressum_field_address_alternative_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'impressum_field_address_alternative',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// email
	add_settings_field(
		'impressum_field_email',
		__( 'Email Address', 'impressum' ),
		'impressum_field_email_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'impressum_field_email',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// phone
	add_settings_field(
		'impressum_field_phone',
		__( 'Telephone', 'impressum' ),
		'impressum_field_phone_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'impressum_field_phone',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// fax
	add_settings_field(
		'impressum_field_fax',
		__( 'Fax', 'impressum' ),
		'impressum_field_fax_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'impressum_field_fax',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// press law checkbox
	add_settings_field(
		'impressum_field_press_law_checkbox',
		__( 'Journalistic/Editorial Content', 'impressum' ),
		'impressum_field_press_law_checkbox_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'impressum_field_press_law_checkbox',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// press law person
	add_settings_field(
		'impressum_field_press_law_person',
		__( 'Responsible according to the German Press Law', 'impressum' ),
		'impressum_field_press_law_person_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'impressum_field_press_law_person',
			'class' => 'impressum_row impressum_press_law',
			'impressum_custom_data' => 'custom',
		]
	);
}

/**
* register our impressum_settings_init to the admin_init action hook
*/
add_action( 'admin_init', 'impressum_settings_init' );

/**
 * Legal Entity field callback.
 * @param $args array
 */
function impressum_field_legal_entity_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_options' );
	// output the field
	?>
<select id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
	<option value="individual" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'red', false ) ) : ( '' ); ?>><?php esc_html_e( 'Individual', 'impressum' ); ?></option>
</select>
	<?php
}

/**
 * Name field callback.
 * @param $args array
 */
function impressum_field_name_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_options' );
	// output the field
	?>
<input id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? 'value="' . $options[ $args['label_for'] ] . '"' : '' ); ?>>
	<?php
}

/**
 * Address field callback.
 * @param $args array
 */
function impressum_field_address_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_options' );
	// output the field
	?>
<textarea cols="50" rows="10" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_options[<?php echo esc_attr( $args['label_for'] ); ?>]"><?php echo ( isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '' ); ?></textarea>
	<?php
}

/**
 * Address Alternative field callback.
 * @param $args array
 */
function impressum_field_address_alternative_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_options' );
	// output the field
	?>
<textarea cols="50" rows="10" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_options[<?php echo esc_attr( $args['label_for'] ); ?>]"><?php echo ( isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '' ); ?></textarea>
	<?php
}

/**
 * Email field callback.
 * @param $args array
 */
function impressum_field_email_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_options' );
	// output the field
	?>
<input type="email" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . $options[ $args['label_for'] ] . '"' : '' ); ?>>
	<?php
}

/**
 * Phone field callback.
 * @param $args array
 */
function impressum_field_phone_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_options' );
	// output the field
	?>
<input type="tel" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . $options[ $args['label_for'] ] . '"' : '' ); ?>>
	<?php
}

/**
 * Fax field callback.
 * @param $args array
 */
function impressum_field_fax_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_options' );
	// output the field
	?>
<input type="tel" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . $options[ $args['label_for'] ] . '"' : '' ); ?>>
	<?php
}

/**
 * Press Law Checkbox field callback.
 * @param $args array
 */
function impressum_field_press_law_checkbox_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_options' );
	// output the field
	?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I have journalistic/editorial content on my website', 'impressum' ); ?>
</label>
	<?php
}

/**
 * Press Law Person field callback.
 * @param $args array
 */
function impressum_field_press_law_person_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_options' );
	// output the field
	?>
<input id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? 'value="' . $options[ $args['label_for'] ] . '"' : '' ); ?>>
	<?php
}

/**
* Add sub menu item in options menu.
*/
function impressum_options_page() {
	// add top level menu page
	add_submenu_page(
		'options-general.php',
		'Impressum',
		'Impressum',
		'manage_options',
		'impressum',
		'impressum_options_page_html'
	);
}
add_action( 'admin_menu', 'impressum_options_page' );

/**
* Sub menu item:
* callback functions
*/
function impressum_options_page_html() {
	// check user capabilities
	if ( ! current_user_can( 'manage_options' ) ) return;
	
	// show error/update messages
	settings_errors( 'impressum_messages' );
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	
	<form action="options.php" method="post">
	<?php
	// output security fields for the registered setting "impressum"
	settings_fields( 'impressum' );
	// output setting sections and their fields
	// (sections are registered for "impressum", each field is registered to a specific section)
	do_settings_sections( 'impressum' );
	// output save settings button
	submit_button( 'Save Settings' );
	?>
	</form>
</div>
<?php
}

/**
 * Enqueue scripts.
 * 
 * @param $hook The current admin page.
 */
function impressum_enqueue_assets( $hook ) {
	// check for settings page
	if ( 'settings_page_impressum' != $hook ) return;
	
	// Check for SCRIPT_DEBUG
	$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
	$version = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? time() : get_plugin_data( __FILE__ )['Version'];
	
	// enqueue scripts
	wp_enqueue_script( 'admin-options', plugins_url( '/assets/js/admin-options' . $suffix . '.js', __FILE__ ), [], $version );
}
add_action( 'admin_enqueue_scripts', 'impressum_enqueue_assets' );