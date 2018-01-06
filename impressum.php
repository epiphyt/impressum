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
	register_setting( 'impressum', 'impressum_imprint_options' );
	register_setting( 'impressum', 'impressum_privacy_options' );
	
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
		'legal_entity',
		__( 'Legal Entity', 'impressum' ),
		'legal_entity_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'legal_entity',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// name
	add_settings_field(
		'name',
		__( 'Name', 'impressum' ),
		'name_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'name',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// address
	add_settings_field(
		'address',
		__( 'Address', 'impressum' ),
		'address_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'address',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// address alternative
	add_settings_field(
		'address_alternative',
		__( 'Alternative Address', 'impressum' ),
		'address_alternative_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'address_alternative',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// email
	add_settings_field(
		'email',
		__( 'Email Address', 'impressum' ),
		'email_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'email',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// phone
	add_settings_field(
		'phone',
		__( 'Telephone', 'impressum' ),
		'phone_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'phone',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// fax
	add_settings_field(
		'fax',
		__( 'Fax', 'impressum' ),
		'fax_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'fax',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// press law checkbox
	add_settings_field(
		'press_law_checkbox',
		__( 'Journalistic/Editorial Content', 'impressum' ),
		'press_law_checkbox_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'press_law_checkbox',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// press law person
	add_settings_field(
		'press_law_person',
		__( 'Responsible according to the German Press Law', 'impressum' ),
		'press_law_person_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'press_law_person',
			'class' => 'impressum_row impressum_press_law',
			'impressum_custom_data' => 'custom',
		]
	);	
	
	// comment subscription checkbox
	add_settings_field(
		'comment_subscription_checkbox',
		__( 'Comment subscription', 'impressum' ),
		'comment_subscription_checkbox_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'comment_subscription_checkbox',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// newsletter checkbox
	add_settings_field(
		'newsletter_checkbox',
		__( 'Newsletter', 'impressum' ),
		'newsletter_checkbox_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'newsletter_checkbox',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// 3rd party content checkbox
	add_settings_field(
		'third_party_content_checkbox',
		__( '3rd party content', 'impressum' ),
		'third_party_content_checkbox_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'third_party_content_checkbox',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// cookie checkbox
	add_settings_field(
		'cookie_checkbox',
		__( 'Cookies', 'impressum' ),
		'cookie_checkbox_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'cookie_checkbox',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// sign up checkbox
	add_settings_field(
		'user_registration_checkbox',
		__( 'User registration', 'impressum' ),
		'user_registration_checkbox_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'user_registration_checkbox',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// google analytics checkbox
	add_settings_field(
		'google_analytics_checkbox',
		__( 'Google Analytics', 'impressum' ),
		'google_analytics_checkbox_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'google_analytics_checkbox',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// piwik checkbox
	add_settings_field(
		'piwik_checkbox',
		__( 'Piwik', 'impressum' ),
		'piwik_checkbox_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'piwik_checkbox',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// facebook checkbox
	add_settings_field(
		'facebook_checkbox',
		__( 'Facebook', 'impressum' ),
		'facebook_checkbox_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'facebook_checkbox',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// twitter checkbox
	add_settings_field(
		'twitter_checkbox',
		__( 'Twitter', 'impressum' ),
		'twitter_checkbox_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'twitter_checkbox',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// google plus checkbox
	add_settings_field(
		'google_plus_checkbox',
		__( 'Google+', 'impressum' ),
		'google_plus_checkbox_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'google_plus_checkbox',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// tumblr checkbox
	add_settings_field(
		'tumblr_checkbox',
		__( 'Tumblr', 'impressum' ),
		'tumblr_checkbox_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'tumblr_checkbox',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// jetpack checkbox
	add_settings_field(
		'jetpack_checkbox',
		__( 'Jetpack', 'impressum' ),
		'jetpack_checkbox_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'jetpack_checkbox',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// google adsense checkbox
	add_settings_field(
		'google_adsense_checkbox',
		__( 'Google Adsense', 'impressum' ),
		'google_adsense_checkbox_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'google_adsense_checkbox',
			'class' => 'impressum_row',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// amazon partner checkbox
	add_settings_field(
		'amazon_partner_checkbox',
		__( 'Amazon Partner', 'impressum' ),
		'amazon_partner_checkbox_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'amazon_partner_checkbox',
			'class' => 'impressum_row',
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
function legal_entity_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_imprint_options' );
	// output the field
	?>
<select id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
	<option value="individual" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'red', false ) ) : ( '' ); ?>><?php esc_html_e( 'Individual', 'impressum' ); ?></option>
</select>
	<?php
}

/**
 * Name field callback.
 * @param $args array
 */
function name_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_imprint_options' );
	// output the field
	?>
<input id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? 'value="' . $options[ $args['label_for'] ] . '"' : '' ); ?>>
	<?php
}

/**
 * Address field callback.
 * @param $args array
 */
function address_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_imprint_options' );
	// output the field
	?>
<textarea cols="50" rows="10" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]"><?php echo ( isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '' ); ?></textarea>
	<?php
}

/**
 * Address Alternative field callback.
 * @param $args array
 */
function address_alternative_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_imprint_options' );
	// output the field
	?>
<textarea cols="50" rows="10" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]"><?php echo ( isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '' ); ?></textarea>
	<?php
}

/**
 * Email field callback.
 * @param $args array
 */
function email_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_imprint_options' );
	// output the field
	?>
<input type="email" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . $options[ $args['label_for'] ] . '"' : '' ); ?>>
	<?php
}

/**
 * Phone field callback.
 * @param $args array
 */
function phone_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_imprint_options' );
	// output the field
	?>
<input type="tel" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . $options[ $args['label_for'] ] . '"' : '' ); ?>>
	<?php
}

/**
 * Fax field callback.
 * @param $args array
 */
function fax_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_imprint_options' );
	// output the field
	?>
<input type="tel" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . $options[ $args['label_for'] ] . '"' : '' ); ?>>
	<?php
}

/**
 * Press Law Checkbox field callback.
 * @param $args array
 */
function press_law_checkbox_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_imprint_options' );
	// output the field
	?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I have journalistic/editorial content on my website', 'impressum' ); ?>
</label>
	<?php
}

/**
 * Press Law Person field callback.
 * @param $args array
 */
function press_law_person_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_imprint_options' );
	// output the field
	?>
<input id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? 'value="' . $options[ $args['label_for'] ] . '"' : '' ); ?>>
	<?php
}

/**
 * Comment subscription Checkbox field callback.
 * @param $args array
 */
function comment_subscription_checkbox_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_privacy_options' );
	// output the field
	?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use a comment subscription plugin.', 'impressum' ); ?>
</label>
	<?php
}

/**
 * Newsletter Checkbox field callback.
 * @param $args array
 */
function newsletter_checkbox_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_privacy_options' );
	// output the field
	?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use a newsletter plugin or service.', 'impressum' ); ?>
</label>
	<?php
}

/**
 * 3rd party content Checkbox field callback.
 * @param $args array
 */
function third_party_content_checkbox_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_privacy_options' );
	// output the field
	?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I embed tweets, Youtube videos or other 3rd-party content.', 'impressum' ); ?>
</label>
	<?php
}

/**
 * Cookie Checkbox field callback.
 * @param $args array
 */
function cookie_checkbox_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_privacy_options' );
	// output the field
	?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use cookies on my site.', 'impressum' ); ?>
</label>
	<?php
}

/**
 * User Registration Checkbox field callback.
 * @param $args array
 */
function user_registration_checkbox_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_privacy_options' );
	// output the field
	?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'Users can register on my site.', 'impressum' ); ?>
</label>
	<?php
}

/**
 * Google Analytics Checkbox field callback.
 * @param $args array
 */
function google_analytics_checkbox_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_privacy_options' );
	// output the field
	?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use Google Analytics.', 'impressum' ); ?>
</label>
	<?php
}

/**
 * Piwik Checkbox field callback.
 * @param $args array
 */
function piwik_checkbox_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_privacy_options' );
	// output the field
	?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use Piwik.', 'impressum' ); ?>
</label>
	<?php
}

/**
 * Facebook Checkbox field callback.
 * @param $args array
 */
function facebook_checkbox_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_privacy_options' );
	// output the field
	?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use a Facebook social button on my website.', 'impressum' ); ?>
</label>
	<?php
}

/**
 * Twitter Checkbox field callback.
 * @param $args array
 */
function twitter_checkbox_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_privacy_options' );
	// output the field
	?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use a Twitter social button on my website.', 'impressum' ); ?>
</label>
	<?php
}

/**
 * Google Plus Checkbox field callback.
 * @param $args array
 */
function google_plus_checkbox_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_privacy_options' );
	// output the field
	?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use a Google+ social button on my website.', 'impressum' ); ?>
</label>
	<?php
}

/**
 * tumblr Checkbox field callback.
 * @param $args array
 */
function tumblr_checkbox_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_privacy_options' );
	// output the field
	?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use a tumblr social button on my website.', 'impressum' ); ?>
</label>
	<?php
}

/**
 * Jetpack Checkbox field callback.
 * @param $args array
 */
function jetpack_checkbox_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_privacy_options' );
	// output the field
	?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use the Jetpack plugin.', 'impressum' ); ?>
</label>
	<?php
}

/**
 * Google Adsense Checkbox field callback.
 * @param $args array
 */
function google_adsense_checkbox_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_privacy_options' );
	// output the field
	?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use Google Adsense on my website.', 'impressum' ); ?>
</label>
	<?php
}

/**
 * Amazon Partner Checkbox field callback.
 * @param $args array
 */
function amazon_partner_checkbox_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_privacy_options' );
	// output the field
	?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use Amazon affiliate links on my website.', 'impressum' ); ?>
</label>
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
 * Shortcode.
 */
function impressum_shortcode() {
	$output = impressum_get_output();
	
	return $output;
}

add_shortcode( 'impressum', 'impressum_shortcode' );

/**
 * @param array $atts All attributes to configure the output.
 * @return string
 */
function impressum_get_output( $atts = [] ) {
	// create an empty output array if there isn’t any
	if ( ! isset ( $atts['output'] ) ) $atts['output'] = [];
	
	// default values to configure output
	if ( ! isset( $atts['output']['address'] ) ) $atts['output']['address'] = true;
	if ( ! isset( $atts['output']['address_alternative'] ) ) $atts['output']['address_alternative'] = true;
	if ( ! isset( $atts['output']['email'] ) ) $atts['output']['email'] = true;
	if ( ! isset( $atts['output']['fax'] ) ) $atts['output']['fax'] = true;
	if ( ! isset( $atts['output']['legal_entity'] ) ) $atts['output']['legal_entity'] = false;
	if ( ! isset( $atts['output']['name'] ) ) $atts['output']['name'] = true;
	if ( ! isset( $atts['output']['phone'] ) ) $atts['output']['phone'] = true;
	if ( ! isset( $atts['output']['press_law_checkbox'] ) ) $atts['output']['press_law_checkbox'] = false;
	if ( ! isset( $atts['output']['press_law_person'] ) ) $atts['output']['press_law_person'] = true;
	if ( ! isset( $atts['markup'] ) ) $atts['markup'] = true;
	
	// check the state if we generate markup
	$do_markup = boolval( $atts['markup'] );
	// get every imprint option
	$options = get_option( 'impressum_imprint_options' );
	// prepare the output
	$output = '';
	
	if ( $do_markup ) {
		// open imprint container
		$output .= '<div class="imprint-container">';
		// open definition list
		$output .= '<dl>';
	}
	
	foreach ( $options as $field => $value ) {
		// check if we output this value
		$do_output = boolval( $atts['output'][$field] ) && $value;
		
		if ( ! $do_output ) continue;
		
		// the field title
		$title = '';
		
		// get title according to field name
		switch ( $field ) {
			case 'address':
			case 'address_alternative':
				$title = __( 'Address', 'impressum' );
				break;
			case 'email':
				$title = __( 'Email Address', 'impressum' );
				break;
			case 'fax':
				$title = __( 'Fax', 'impressum' );
				break;
			case 'legal_entity':
				$title = __( 'Legal Entity', 'impressum' );
				break;
			case 'name':
				$title = __( 'Name', 'impressum' );
				break;
			case 'phone':
				$title = __( 'Phone', 'impressum' );
				break;
			case 'press_law_person':
				$title = __( 'Responsible according to the German Press Law', 'impressum' );
				break;
		}
		
		// check if field should be displayed
		if ( $field === 'press_law_person' && ! isset( $options['press_law_checkbox'] ) ) {
			continue;
		}
	
		// generate output for this field
		if ( $do_markup ) {
			// definition term and description
			$output .= '
<dt>' . $title . '</dt>
<dd>' . esc_html( $options[$field] ) . '</dd>
';
		}
		else {
			// comma separated list
			$output .= esc_html( $options[$field] ) . ', ';
		}
	}
	
	if ( $do_markup ) {
		// close definition list
		$output .= '</dl>';
		// close imprint container
		$output .= '</div>';
	}
	else {
		// remove last comma
		$output = rtrim( $output, ', ' );
	}
	
	return $output;
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
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	$version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : get_plugin_data( __FILE__ )['Version'];
	
	// enqueue scripts
	wp_enqueue_script( 'admin-options', plugins_url( '/assets/js/admin-options' . $suffix . '.js', __FILE__ ), [], $version );
}
add_action( 'admin_enqueue_scripts', 'impressum_enqueue_assets' );