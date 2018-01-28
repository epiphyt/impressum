<?php
// exit if ABSPATH is not defined
defined( 'ABSPATH' ) || exit;

require_once( __DIR__ . '/impressum.class.php' );

/**
 * Impressum backend functions.
 * 
 * @version		0.1
 * @author		Matthias Kittsteiner, Simon Kraft
 * @license		GPL2 <https://www.gnu.org/licenses/gpl-3.0.html>
 */
class Impressum_Backend extends Impressum {
	/**
	 * Impressum Backend constructor.
	 * 
	 * @param string $plugin_file The path of the main plugin file
	 */
	public function __construct( $plugin_file ) {
		parent::__construct( $plugin_file );
		
		// hooks
		add_action( 'admin_init', [ $this, 'settings_init' ] );
		add_action( 'admin_menu', [ $this, 'options_page' ] );
	}
	
	/**
	 * Custom option and settings.
	 */
	public static function settings_init() {
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
			[ __CLASS__, 'legal_entity_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'legal_entity',
				'class' => 'impressum_row',
			]
		);
		
		// name
		add_settings_field(
			'name',
			__( 'Name', 'impressum' ),
			[ __CLASS__, 'impressum_input_text_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'name',
				'class' => 'impressum_row',
			]
		);
		
		// address
		add_settings_field(
			'address',
			__( 'Address', 'impressum' ),
			[ __CLASS__, 'impressum_textarea_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'address',
				'class' => 'impressum_row',
			]
		);
		
		// address alternative
		add_settings_field(
			'address_alternative',
			__( 'Alternative Address', 'impressum' ),
			[ __CLASS__, 'impressum_textarea_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'address_alternative',
				'class' => 'impressum_row',
			]
		);
		
		// email
		add_settings_field(
			'email',
			__( 'Email Address', 'impressum' ),
			[ __CLASS__, 'impressum_email_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'email',
				'class' => 'impressum_row',
			]
		);
		
		// phone
		add_settings_field(
			'phone',
			__( 'Telephone', 'impressum' ),
			[ __CLASS__, 'impressum_phone_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'phone',
				'class' => 'impressum_row',
			]
		);
		
		// fax
		add_settings_field(
			'fax',
			__( 'Fax', 'impressum' ),
			[ __CLASS__, 'impressum_phone_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'fax',
				'class' => 'impressum_row',
			]
		);
		
		// press law checkbox
		add_settings_field(
			'press_law_checkbox',
			__( 'Journalistic/Editorial Content', 'impressum' ),
			[ __CLASS__, 'impressum_press_law_checkbox_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'press_law_checkbox',
				'class' => 'impressum_row',
			]
		);
		
		// press law person
		add_settings_field(
			'press_law_person',
			__( 'Responsible according to the German Press Law', 'impressum' ),
			[ __CLASS__, 'impressum_textarea_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'press_law_person',
				'class' => 'impressum_row impressum_press_law',
			]
		);
		
		// vat id
		add_settings_field(
			'vat_id',
			__( 'VAT ID', 'impressum' ),
			[ __CLASS__, 'impressum_input_text_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'vat_id',
				'class' => 'impressum_row vat_id',
			]
		);
		
		// inspecting authority
		add_settings_field(
			'inspecting_authority',
			__( 'Inspecting Authority', 'impressum' ),
			[ __CLASS__, 'impressum_textarea_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'inspecting_authority',
				'class' => 'impressum_row impressum_inspecting_authority',
			]
		);
		
		// register
		add_settings_field(
			'register',
			__( 'Register', 'impressum' ),
			[ __CLASS__, 'impressum_input_text_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'register',
				'class' => 'impressum_row impressum_register',
			]
		);
		
		// business id
		add_settings_field(
			'business_id',
			__( 'Business ID', 'impressum' ),
			[ __CLASS__, 'impressum_input_text_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'business_id',
				'class' => 'impressum_row impressum_business_id',
			]
		);
		
		// representative
		add_settings_field(
			'representative',
			__( 'Representative', 'impressum' ),
			[ __CLASS__, 'impressum_textarea_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'representative',
				'class' => 'impressum_row impressum_representative',
			]
		);
		
		// capital stock
		add_settings_field(
			'capital_stock',
			__( 'Capital Stock', 'impressum' ),
			[ __CLASS__, 'impressum_input_text_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'capital_stock',
				'class' => 'impressum_row impressum_capital_stock',
			]
		);
		
		// pending deposits
		add_settings_field(
			'pending_deposits',
			__( 'Pending Deposits', 'impressum' ),
			[ __CLASS__, 'impressum_input_text_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'pending_deposits',
				'class' => 'impressum_row impressum_pending_deposits',
			]
		);
		
		// professional association
		add_settings_field(
			'professional_association',
			__( 'Professional Association', 'impressum' ),
			[ __CLASS__, 'impressum_input_text_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'professional_association',
				'class' => 'impressum_row impressum_professional_association',
			]
		);
		
		// legal job title
		add_settings_field(
			'legal_job_title',
			__( 'Legal Job Title', 'impressum' ),
			[ __CLASS__, 'impressum_input_text_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'legal_job_title',
				'class' => 'impressum_row impressum_legal_job_title',
			]
		);
		
		// professional regulations
		add_settings_field(
			'professional_regulations',
			__( 'Professional Regulations', 'impressum' ),
			[ __CLASS__, 'impressum_textarea_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'professional_regulations',
				'class' => 'impressum_row impressum_professional_regulations',
			]
		);
		
		// comment subscription checkbox
		add_settings_field(
			'comment_subscription_checkbox',
			__( 'Comment subscription', 'impressum' ),
			[ __CLASS__, 'comment_subscription_checkbox_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'comment_subscription_checkbox',
				'class' => 'impressum_row',
			]
		);
		
		// newsletter checkbox
		add_settings_field(
			'newsletter_checkbox',
			__( 'Newsletter', 'impressum' ),
			[ __CLASS__, 'newsletter_checkbox_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'newsletter_checkbox',
				'class' => 'impressum_row',
			]
		);
		
		// 3rd party content checkbox
		add_settings_field(
			'third_party_content_checkbox',
			__( '3rd party content', 'impressum' ),
			[ __CLASS__, 'third_party_content_checkbox_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'third_party_content_checkbox',
				'class' => 'impressum_row',
			]
		);
		
		// cookie checkbox
		add_settings_field(
			'cookie_checkbox',
			__( 'Cookies', 'impressum' ),
			[ __CLASS__, 'cookie_checkbox_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'cookie_checkbox',
				'class' => 'impressum_row',
			]
		);
		
		// sign up checkbox
		add_settings_field(
			'user_registration_checkbox',
			__( 'User registration', 'impressum' ),
			[ __CLASS__, 'user_registration_checkbox_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'user_registration_checkbox',
				'class' => 'impressum_row',
			]
		);
		
		// google analytics checkbox
		add_settings_field(
			'google_analytics_checkbox',
			__( 'Google Analytics', 'impressum' ),
			[ __CLASS__, 'google_analytics_checkbox_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'google_analytics_checkbox',
				'class' => 'impressum_row',
			]
		);
		
		// piwik checkbox
		add_settings_field(
			'piwik_checkbox',
			__( 'Matomo/Piwik', 'impressum' ),
			[ __CLASS__, 'piwik_checkbox_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'piwik_checkbox',
				'class' => 'impressum_row',
			]
		);
		
		// facebook checkbox
		add_settings_field(
			'facebook_checkbox',
			__( 'Facebook', 'impressum' ),
			[ __CLASS__, 'facebook_checkbox_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'facebook_checkbox',
				'class' => 'impressum_row',
			]
		);
		
		// twitter checkbox
		add_settings_field(
			'twitter_checkbox',
			__( 'Twitter', 'impressum' ),
			[ __CLASS__, 'twitter_checkbox_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'twitter_checkbox',
				'class' => 'impressum_row',
			]
		);
		
		// google plus checkbox
		add_settings_field(
			'google_plus_checkbox',
			__( 'Google+', 'impressum' ),
			[ __CLASS__, 'google_plus_checkbox_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'google_plus_checkbox',
				'class' => 'impressum_row',
			]
		);
		
		// tumblr checkbox
		add_settings_field(
			'tumblr_checkbox',
			__( 'Tumblr', 'impressum' ),
			[ __CLASS__, 'tumblr_checkbox_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'tumblr_checkbox',
				'class' => 'impressum_row',
			]
		);
		
		// jetpack checkbox
		add_settings_field(
			'jetpack_checkbox',
			__( 'Jetpack', 'impressum' ),
			[ __CLASS__, 'jetpack_checkbox_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'jetpack_checkbox',
				'class' => 'impressum_row',
			]
		);
		
		// google adsense checkbox
		add_settings_field(
			'google_adsense_checkbox',
			__( 'Google AdSense', 'impressum' ),
			[ __CLASS__, 'google_adsense_checkbox_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'google_adsense_checkbox',
				'class' => 'impressum_row',
			]
		);
		
		// amazon partner checkbox
		add_settings_field(
			'amazon_partner_checkbox',
			__( 'Amazon Partner', 'impressum' ),
			[ __CLASS__, 'amazon_partner_checkbox_callback' ],
			'impressum',
			'impressum_section_developers',
			[
				'label_for' => 'amazon_partner_checkbox',
				'class' => 'impressum_row',
			]
		);
	}
	
	/**
	 * Legal Entity field callback.
	 * @param $args array
	 */
	public static function legal_entity_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_imprint_options' );
		
		// check for selected option
		$select_ag = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'ag', false ) ) : ( '' );
		$select_ev = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'ev', false ) ) : ( '' );
		$select_ek = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'ek', false ) ) : ( '' );
		$select_einzelkaufmann = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'einzelkaufmann', false ) ) : ( '' );
		$select_freelancer = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'freelancer', false ) ) : ( '' );
		$select_ggmbH = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'ggmbH', false ) ) : ( '' );
		$select_gmbh = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'gmbh', false ) ) : ( '' );
		$select_gbr = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'gbr', false ) ) : ( '' );
		$select_gmbh_co_kg = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'gmbh_co_kg', false ) ) : ( '' );
		$select_kg = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'kg', false ) ) : ( '' );
		$select_kgag = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'kgag', false ) ) : ( '' );
		$select_ohg = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'ohg', false ) ) : ( '' );
		$select_individual = ! isset( $options['legal_entity'] ) ? ' selected' : ( isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'individual', false ) ) : ( '' ) );
		$select_ug = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'ug', false ) ) : ( '' );
		$select_ug_co_kg = isset( $options['legal_entity'] ) ? ( selected( $options['legal_entity'], 'ug_co_kg', false ) ) : ( '' );
		
		// output the field
		?>
<select id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
	<option value="ag" <?php echo $select_ag; ?>><?php esc_html_e( 'AG', 'impressum' ); ?></option>
	<option value="ev" <?php echo $select_ev; ?>><?php esc_html_e( 'e.V.', 'impressum' ); ?></option>
	<option value="ek" <?php echo $select_ek; ?>><?php esc_html_e( 'e.K.', 'impressum' ); ?></option>
	<option value="einzelkaufmann" <?php echo $select_einzelkaufmann; ?>><?php esc_html_e( 'Einzelkaufmann', 'impressum' ); ?></option>
	<option value="freelancer" <?php echo $select_freelancer; ?>><?php esc_html_e( 'Freelancer', 'impressum' ); ?></option>
	<option value="ggmbH" <?php echo $select_ggmbH; ?>><?php esc_html_e( 'gGmbH', 'impressum' ); ?></option>
	<option value="gmbh" <?php echo $select_gmbh; ?>><?php esc_html_e( 'GmbH', 'impressum' ); ?></option>
	<option value="gbr" <?php echo $select_gbr; ?>><?php esc_html_e( 'GbR', 'impressum' ); ?></option>
	<option value="gmbh_co_kg" <?php echo $select_gmbh_co_kg; ?>><?php esc_html_e( 'GmbH & Co. KG', 'impressum' ); ?></option>
	<option value="kg" <?php echo $select_kg; ?>><?php esc_html_e( 'KG', 'impressum' ); ?></option>
	<option value="kgag" <?php echo $select_kgag; ?>><?php esc_html_e( 'KGaA', 'impressum' ); ?></option>
	<option value="ohg" <?php echo $select_ohg; ?>><?php esc_html_e( 'OHG', 'impressum' ); ?></option>
	<option value="individual" <?php echo $select_individual; ?>><?php esc_html_e( 'Individual', 'impressum' ); ?></option>
	<option value="ug" <?php echo $select_ug; ?>><?php esc_html_e( 'UG (haftungsbeschränkt)', 'impressum' ); ?></option>
	<option value="ug_co_kg" <?php echo $select_ug_co_kg; ?>><?php esc_html_e( 'UG (haftungsbeschränkt) & Co. KG', 'impressum' ); ?></option>
</select>
		<?php
	}
	
	/**
	 * Email field callback.
	 * @param $args array
	 */
	public static function impressum_email_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_imprint_options' );
		// output the field
		?>
<input type="email" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . $options[ $args['label_for'] ] . '"' : '' ); ?>>
		<?php
	}
	
	/**
	 * Text input field callback.
	 * @param $args array
	 */
	public static function impressum_input_text_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_imprint_options' );
		// output the field
		?>
<input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . $options[ $args['label_for'] ] . '"' : '' ); ?>>
		<?php
	}
	
	/**
	 * Phone field callback.
	 * @param $args array
	 */
	public static function impressum_phone_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_imprint_options' );
		// output the field
		?>
<input type="tel" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . $options[ $args['label_for'] ] . '"' : '' ); ?>>
		<?php
	}
	
	/**
	 * Press Law Checkbox field callback.
	 * @param $args array
	 */
	public static function impressum_press_law_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_imprint_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I have journalistic/editorial content on my website', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * Textarea callback.
	 * @param $args array
	 */
	public static function impressum_textarea_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_imprint_options' );
		// output the field
		?>
<textarea cols="50" rows="10" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]"><?php echo ( isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '' ); ?></textarea>
		<?php
	}
	
	/**
	 * Comment subscription Checkbox field callback.
	 * @param $args array
	 */
	public static function comment_subscription_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use a comment subscription plugin.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * Newsletter Checkbox field callback.
	 * @param $args array
	 */
	public static function newsletter_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use a newsletter plugin or service.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * 3rd party content Checkbox field callback.
	 * @param $args array
	 */
	public static function third_party_content_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I embed tweets, YouTube videos or other 3rd-party content.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * Cookie Checkbox field callback.
	 * @param $args array
	 */
	public static function cookie_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use cookies on my site.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * User Registration Checkbox field callback.
	 * @param $args array
	 */
	public static function user_registration_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'Users can register on my site.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * Google Analytics Checkbox field callback.
	 * @param $args array
	 */
	public static function google_analytics_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use Google Analytics.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * Matomo/Piwik Checkbox field callback.
	 * @param $args array
	 */
	public static function piwik_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use Matomo/Piwik.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * Facebook Checkbox field callback.
	 * @param $args array
	 */
	public static function facebook_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use a Facebook social button on my website.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * Twitter Checkbox field callback.
	 * @param $args array
	 */
	public static function twitter_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use a Twitter social button on my website.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * Google Plus Checkbox field callback.
	 * @param $args array
	 */
	public static function google_plus_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use a Google+ social button on my website.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * tumblr Checkbox field callback.
	 * @param $args array
	 */
	public static function tumblr_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use a Tumblr social button on my website.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * Jetpack Checkbox field callback.
	 * @param $args array
	 */
	public static function jetpack_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use the Jetpack plugin.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * Google Adsense Checkbox field callback.
	 * @param $args array
	 */
	public static function google_adsense_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use Google AdSense on my website.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * Amazon Partner Checkbox field callback.
	 * @param $args array
	 */
	public static function amazon_partner_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php _e( 'I use Amazon affiliate links on my website.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	* Add sub menu item in options menu.
	*/
	public static function options_page() {
		// add top level menu page
		add_submenu_page(
			'options-general.php',
			'Impressum',
			'Impressum',
			'manage_options',
			'impressum',
			[ __CLASS__, 'options_page_html' ]
		);
	}
	
	/**
	* Sub menu item:
	* callback functions
	*/
	public static function options_page_html() {
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
}