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
		'impressum_input_text_callback',
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
		'impressum_textarea_callback',
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
		'impressum_textarea_callback',
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
		'impressum_email_callback',
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
		'impressum_phone_callback',
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
		'impressum_phone_callback',
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
		'impressum_press_law_checkbox_callback',
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
		'impressum_textarea_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'press_law_person',
			'class' => 'impressum_row impressum_press_law',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// vat id
	add_settings_field(
		'vat_id',
		__( 'VAT ID', 'impressum' ),
		'impressum_input_text_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'vat_id',
			'class' => 'impressum_row vat_id',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// inspecting authority
	add_settings_field(
		'inspecting_authority',
		__( 'Inspecting Authority', 'impressum' ),
		'impressum_textarea_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'inspecting_authority',
			'class' => 'impressum_row impressum_inspecting_authority',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// register
	add_settings_field(
		'register',
		__( 'Register', 'impressum' ),
		'impressum_input_text_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'register',
			'class' => 'impressum_row impressum_register',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// business id
	add_settings_field(
		'business_id',
		__( 'Business ID', 'impressum' ),
		'impressum_input_text_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'business_id',
			'class' => 'impressum_row impressum_business_id',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// representative
	add_settings_field(
		'representative',
		__( 'Representative', 'impressum' ),
		'impressum_textarea_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'representative',
			'class' => 'impressum_row impressum_representative',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// capital stock
	add_settings_field(
		'capital_stock',
		__( 'Capital Stock', 'impressum' ),
		'impressum_input_text_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'capital_stock',
			'class' => 'impressum_row impressum_capital_stock',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// pending deposits
	add_settings_field(
		'pending_deposits',
		__( 'Pending Deposits', 'impressum' ),
		'impressum_input_text_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'pending_deposits',
			'class' => 'impressum_row impressum_pending_deposits',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// professional association
	add_settings_field(
		'professional_association',
		__( 'Professional Association', 'impressum' ),
		'impressum_input_text_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'professional_association',
			'class' => 'impressum_row impressum_professional_association',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// legal job title
	add_settings_field(
		'legal_job_title',
		__( 'Legal Job Title', 'impressum' ),
		'impressum_input_text_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'legal_job_title',
			'class' => 'impressum_row impressum_legal_job_title',
			'impressum_custom_data' => 'custom',
		]
	);
	
	// professional regulations
	add_settings_field(
		'professional_regulations',
		__( 'Professional Regulations', 'impressum' ),
		'impressum_textarea_callback',
		'impressum',
		'impressum_section_developers',
		[
			'label_for' => 'professional_regulations',
			'class' => 'impressum_row impressum_professional_regulations',
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
	<option value="ag" <?php echo isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'ag', false ) ) : ( '' ); ?>><?php esc_html_e( 'AG', 'impressum' ); ?></option>
	<option value="ev" <?php echo isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'ev', false ) ) : ( '' ); ?>><?php esc_html_e( 'e.V.', 'impressum' ); ?></option>
	<option value="ek" <?php echo isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'ek', false ) ) : ( '' ); ?>><?php esc_html_e( 'e.K.', 'impressum' ); ?></option>
	<option value="einzelkaufmann" <?php echo isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'einzelkaufmann', false ) ) : ( '' ); ?>><?php esc_html_e( 'Einzelkaufmann', 'impressum' ); ?></option>
	<option value="freelancer" <?php echo isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'freiberufler', false ) ) : ( '' ); ?>><?php esc_html_e( 'Freelancer', 'impressum' ); ?></option>
	<option value="ggmbH" <?php echo isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'ggmbh', false ) ) : ( '' ); ?>><?php esc_html_e( 'gGmbH', 'impressum' ); ?></option>
	<option value="gmbh" <?php echo isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'gmbh', false ) ) : ( '' ); ?>><?php esc_html_e( 'GmbH', 'impressum' ); ?></option>
	<option value="gbr" <?php echo isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'gbr', false ) ) : ( '' ); ?>><?php esc_html_e( 'GbR', 'impressum' ); ?></option>
	<option value="gmbh_co_kg" <?php echo isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'gmbh_co_kg', false ) ) : ( '' ); ?>><?php esc_html_e( 'GmbH & Co. KG', 'impressum' ); ?></option>
	<option value="kg" <?php echo isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'kg', false ) ) : ( '' ); ?>><?php esc_html_e( 'KG', 'impressum' ); ?></option>
	<option value="kgag" <?php echo isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'kgag', false ) ) : ( '' ); ?>><?php esc_html_e( 'KGaA', 'impressum' ); ?></option>
	<option value="ohg" <?php echo isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'ohg', false ) ) : ( '' ); ?>><?php esc_html_e( 'OHG', 'impressum' ); ?></option>
	<option value="individual" <?php echo isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'individual', false ) ) : ( '' ); ?>><?php esc_html_e( 'Individual', 'impressum' ); ?></option>
	<option value="ug" <?php echo isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'ug', false ) ) : ( '' ); ?>><?php esc_html_e( 'UG (haftungsbesschränkt)', 'impressum' ); ?></option>
	<option value="ug_co_kg" <?php echo isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'ug_co_kg', false ) ) : ( '' ); ?>><?php esc_html_e( 'UG (haftungsbesschränkt) & Co. KG', 'impressum' ); ?></option>
</select>
	<?php
}

/**
 * Email field callback.
 * @param $args array
 */
function impressum_email_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_imprint_options' );
	// output the field
	?>
<input type="email" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . $options[ $args['label_for'] ] . '"' : '' ); ?>>
	<?php
}

/**
 * Text input field callback.
 * @param $args array
 */
function impressum_input_text_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_imprint_options' );
	// output the field
	?>
<input type="tel" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . $options[ $args['label_for'] ] . '"' : '' ); ?>>
	<?php
}

/**
 * Phone field callback.
 * @param $args array
 */
function impressum_phone_callback( $args ) {
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
function impressum_press_law_checkbox_callback( $args ) {
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
 * Textarea callback.
 * @param $args array
 */
function impressum_textarea_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_imprint_options' );
	// output the field
	?>
<textarea cols="50" rows="10" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]"><?php echo ( isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '' ); ?></textarea>
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
	<?php _e( 'I embed tweets, YouTube videos or other 3rd-party content.', 'impressum' ); ?>
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
 * Matomo/Piwik Checkbox field callback.
 * @param $args array
 */
function piwik_checkbox_callback( $args ) {
	// get the value of the setting we've registered with register_setting()
	$options = get_option( 'impressum_privacy_options' );
	// output the field
	?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" data-custom="<?php echo esc_attr( $args['impressum_custom_data'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use Matomo/Piwik.', 'impressum' ); ?>
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
	<?php _e( 'I use Google AdSense on my website.', 'impressum' ); ?>
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
 * Imprint Shortcode.
 */
function impressum_imprint_shortcode() {
	$output = impressum_get_imprint_output();
	
	return $output;
}

add_shortcode( 'impressum', 'impressum_imprint_shortcode' );


/**
 * Privacy Shortcode.
 */
function impressum_privacy_shortcode() {
	$output = impressum_get_privacy_output();
	
	return $output;
}

add_shortcode( 'privacy', 'impressum_privacy_shortcode' );


/**
 * Generate the output for the imprint shortcode.
 * 
 * @param array $atts All attributes to configure the output.
 * @return string
 */
function impressum_get_imprint_output( $atts = [] ) {
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
	if ( ! isset( $atts['output']['vat_id'] ) ) $atts['output']['vat_id'] = true;
	if ( ! isset( $atts['output']['inspecting_authority'] ) ) $atts['output']['inspecting_authority'] = true;
	if ( ! isset( $atts['output']['register'] ) ) $atts['output']['register'] = true;
	if ( ! isset( $atts['output']['business_id'] ) ) $atts['output']['business_id'] = true;
	if ( ! isset( $atts['output']['representative'] ) ) $atts['output']['representative'] = true;
	if ( ! isset( $atts['output']['capital_stock'] ) ) $atts['output']['capital_stock'] = true;
	if ( ! isset( $atts['output']['pending_deposits'] ) ) $atts['output']['pending_deposits'] = true;
	if ( ! isset( $atts['output']['professional_association'] ) ) $atts['output']['professional_association'] = true;
	if ( ! isset( $atts['output']['legal_job_title'] ) ) $atts['output']['legal_job_title'] = true;
	if ( ! isset( $atts['output']['professional_regulations'] ) ) $atts['output']['professional_regulations'] = true;
	if ( ! isset( $atts['markup'] ) ) $atts['markup'] = true;
	
	// map displaying a field by its entity
	$field_mapping = [
		'address' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbH',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'individual',
			'ug',
			'ug_co_kg'
		],
		'address_alternative' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbH',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'individual',
			'ug',
			'ug_co_kg'
		],
		'email' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbH',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'individual',
			'ug',
			'ug_co_kg'
		],
		'fax' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbH',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'individual',
			'ug',
			'ug_co_kg'
		],
		'legal_entity' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbH',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'individual',
			'ug',
			'ug_co_kg'
		],
		'name' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbH',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'individual',
			'ug',
			'ug_co_kg'
		],
		'phone' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbH',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'individual',
			'ug',
			'ug_co_kg'
		],
		'press_law_person' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbH',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'individual',
			'ug',
			'ug_co_kg'
		],
		'vat_id' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbH',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'individual',
			'ug',
			'ug_co_kg'
		],
		'inspecting_authority' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbH',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'ug',
			'ug_co_kg'
		],
		'register' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbH',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'ug',
			'ug_co_kg'
		],
		'business_id' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbH',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'ug',
			'ug_co_kg'
		],
		'representative' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbH',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'ug',
			'ug_co_kg'
		],
		'capital_stock' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbH',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'ug',
			'ug_co_kg'
		],
		'pending_deposits' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbH',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'ug',
			'ug_co_kg'
		],
		'professional_association' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbH',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'ug',
			'ug_co_kg'
		],
		'legal_job_title' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbH',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'ug',
			'ug_co_kg'
		],
		'professional_regulations' => [
			'ag',
			'ev',
			'ek',
			'einzelkaufmann',
			'freelancer',
			'ggmbH',
			'gmbh',
			'gbr',
			'gmbh_co_kg',
			'kg',
			'kgag',
			'ohg',
			'ug',
			'ug_co_kg'
		]
	];
	
	// check the state if we generate markup
	$do_markup = boolval( $atts['markup'] );
	// get every imprint option
	$options = get_option( 'impressum_imprint_options' );
	// get entity
	$entity = $options['legal_entity'];
	// prepare the output
	$output = '';
	
	// abort if there are no valid options
	if ( empty( $options ) ) return '';
	
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
		
		// check if the given field should be displayed for this legal entity
		if ( ! in_array( $entity, $field_mapping[$field] ) ) continue;
		
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
			case 'vat_id':
				$title = __( 'VAT ID', 'impressum' );
				break;
			case 'inspecting_authority':
				$title = __( 'Inspecting Authority', 'impressum' );
				break;
			case 'register':
				$title = __( 'Register', 'impressum' );
				break;
			case 'business_id':
				$title = __( 'Business ID', 'impressum' );
				break;
			case 'representative':
				$title = __( 'Representative', 'impressum' );
				break;
			case 'capital_stock':
				$title = __( 'Capital Stock', 'impressum' );
				break;
			case 'pending_deposits':
				$title = __( 'Pending Deposits', 'impressum' );
				break;
			case 'professional_association':
				$title = __( 'Professional Association', 'impressum' );
				break;
			case 'legal_job_title':
				$title = __( 'Legal Job Title', 'impressum' );
				break;
			case 'professional_regulations':
				$title = __( 'Professional Regulations', 'impressum' );
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
<dd>' . nl2br( esc_html( $options[$field] ) ) . '</dd>
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
 * Generate the output for the privacy shortcode.
 * 
 * @param array $atts All attributes to configure the output.
 * @return string
 */
function impressum_get_privacy_output( $atts = [] ) {
	// create an empty output array if there isn’t any
	if ( ! isset ( $atts['output'] ) ) $atts['output'] = [];
	
	// default output values
	if ( ! isset( $atts['output']['comment_subscription_checkbox'] ) ) $atts['output']['comment_subscription_checkbox'] = true;
	if ( ! isset( $atts['output']['newsletter_checkbox'] ) ) $atts['output']['newsletter_checkbox'] = true;
	if ( ! isset( $atts['output']['third_party_content_checkbox'] ) ) $atts['output']['third_party_content_checkbox'] = true;
	if ( ! isset( $atts['output']['cookie_checkbox'] ) ) $atts['output']['cookie_checkbox'] = true;
	if ( ! isset( $atts['output']['user_registration_checkbox'] ) ) $atts['output']['user_registration_checkbox'] = true;
	if ( ! isset( $atts['output']['google_analytics_checkbox'] ) ) $atts['output']['google_analytics_checkbox'] = true;
	if ( ! isset( $atts['output']['piwik_checkbox'] ) ) $atts['output']['piwik_checkbox'] = true;
	if ( ! isset( $atts['output']['facebook_checkbox'] ) ) $atts['output']['facebook_checkbox'] = true;
	if ( ! isset( $atts['output']['twitter_checkbox'] ) ) $atts['output']['twitter_checkbox'] = true;
	if ( ! isset( $atts['output']['google_plus_checkbox'] ) ) $atts['output']['google_plus_checkbox'] = true;
	if ( ! isset( $atts['output']['tumblr_checkbox'] ) ) $atts['output']['tumblr_checkbox'] = true;
	if ( ! isset( $atts['output']['jetpack_checkbox'] ) ) $atts['output']['jetpack_checkbox'] = true;
	if ( ! isset( $atts['output']['google_adsense_checkbox'] ) ) $atts['output']['google_adsense_checkbox'] = true;
	if ( ! isset( $atts['output']['amazon_partner_checkbox'] ) ) $atts['output']['amazon_partner_checkbox'] = true;
	if ( ! isset( $atts['output'][''] ) ) $atts['output']['amazon_partner'] = true;
	if ( ! isset( $atts['markup'] ) ) $atts['markup'] = true;
	
	// check the state if we generate markup
	$do_markup = boolval( $atts['markup'] );
	// get all privacy options
	$options = get_option( 'impressum_privacy_options' );
	// prepare the output
	$output = '';
	
	// abort if there are no valid options
	if ( empty( $options ) ) return '';
	
	foreach ( $options as $field => $value ) {
		// check if we output this value
		$do_output = boolval( $atts['output'][$field] ) && $value;
		
		if ( ! $do_output ) continue;
		
		// the field content
		$content = '';
		// the field title
		$title = '';
		
		// get title according to field name
		switch ( $field ) {
			case 'comment_subscription_checkbox':
				$content .= '<p>' . __( 'This is the privacy description for the comment subscription.', 'impressum' ) . '</p>' . PHP_EOL;
				$content .= '<p>' . __( 'Line 2', 'impressum' ) . '</p>';
				$title = '<h2>' . __( 'Comment subscription', 'impressum' ) . '</h2>';
				break;
			case 'newsletter_checkbox':
				$content .= '<p>' . __( 'This is the privacy description for the newsletter.', 'impressum' ) . '</p>';
				$title = '<h2>' . __( 'Newsletter', 'impressum' ) . '</h2>';
				break;
			case 'third_party_content_checkbox':
				$content .= '<p>' . __( 'This is the privacy description for 3rd party content.', 'impressum' ) . '</p>';
				$title = '<h2>' . __( '3rd party content', 'impressum' ) . '</h2>';
				break;
			case 'cookie_checkbox':
				$content .= '<p>' . __( 'This is the privacy description for cookies.', 'impressum' ) . '</p>';
				$title = '<h2>' . __( 'Cookies', 'impressum' ) . '</h2>';
				break;
			case 'user_registration_checkbox':
				$content .= '<p>' . __( 'This is the privacy description for user registration.', 'impressum' ) . '</p>';
				$title = '<h2>' . __( 'User registration', 'impressum' ) . '</h2>';
				break;
			case 'google_analytics_checkbox':
				$content .= '<p>' . __( 'This is the privacy description for Google Analytics.', 'impressum' ) . '</p>';
				$title = '<h2>' . __( 'Google Analytics', 'impressum' ) . '</h2>';
				break;
			case 'piwik_checkbox':
				$content .= '<p>' . __( 'This is the privacy description for Matomo/Piwik.', 'impressum' ) . '</p>';
				$title = '<h2>' . __( 'Matomo/Piwik', 'impressum' ) . '</h2>';
				break;
			case 'facebook_checkbox':
				$content .= '<p>' . __( 'This is the privacy description for Facebook.', 'impressum' ) . '</p>';
				$title = '<h2>' . __( 'Facebook', 'impressum' ) . '</h2>';
				break;
			case 'twitter_checkbox':
				$content .= '<p>' . __( 'This is the privacy description for Twitter.', 'impressum' ) . '</p>';
				$title = '<h2>' . __( 'Twitter', 'impressum' ) . '</h2>';
				break;
			case 'google_plus_checkbox':
				$content .= '<p>' . __( 'This is the privacy description for Google Plus.', 'impressum' ) . '</p>';
				$title = '<h2>' . __( 'Google Plus', 'impressum' ) . '</h2>';
				break;
			case 'tumblr_checkbox':
				$content .= '<p>' . __( 'This is the privacy description for Tumbler.', 'impressum' ) . '</p>';
				$title = '<h2>' . __( 'Tumbler', 'impressum' ) . '</h2>';
				break;
			case 'jetpack_checkbox':
				$content .= '<p>' . __( 'This is the privacy description for Jetpack.', 'impressum' ) . '</p>';
				$title = '<h2>' . __( 'Jetpack', 'impressum' ) . '</h2>';
				break;
			case 'google_adsense_checkbox':
				$content .= '<p>' . __( 'This is the privacy description for Google AdSense.', 'impressum' ) . '</p>';
				$title = '<h2>' . __( 'Google AdSense', 'impressum' ) . '</h2>';
				break;
			case 'amazon_partner_checkbox':
				$content .= '<p>' . __( 'This is the privacy description for Amazon Partner.', 'impressum' ) . '</p>';
				$title = '<h2>' . __( 'Amazon Partner', 'impressum' ) . '</h2>';
				break;
		}
		
		if ( $do_markup ) {
			$output .= $title . PHP_EOL . $content . PHP_EOL . PHP_EOL;
		}
		else {
			if ( empty( $output ) ) $output .= PHP_EOL;
			
			// remove HTML tags if we don't want a markup
			$output .= strip_tags( $title ) . PHP_EOL;
			$output .= strip_tags( $content );
		}
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