<?php
namespace epiphyt\Impressum;

// exit if ABSPATH is not defined
defined( 'ABSPATH' ) || exit;

require_once( __DIR__ . '/class-impressum.php' );

/**
 * Impressum backend functions.
 * 
 * @version		0.1
 * @author		Epiphyt
 * @license		GPL3 <https://www.gnu.org/licenses/gpl-3.0.html>
 */
class Impressum_Backend extends Impressum {
	/**
	 * Impressum Backend constructor.
	 * 
	 * @param	string		$plugin_file The path of the main plugin file
	 */
	public function __construct( $plugin_file ) {
		parent::__construct( $plugin_file );
		
		// hooks
		add_filter( 'plugin_row_meta', [ $this, 'add_meta_link' ], 10, 2 );
		add_action( 'admin_init', [ $this, 'impressum_settings_init' ] );
		add_action( 'admin_menu', [ $this, 'impressum_options_page' ] );
		add_action( 'network_admin_menu', [ $this, 'impressum_network_options_page' ] );
		add_action( 'network_admin_edit_impressum_network_options_update', [ $this, 'impressum_network_options_update' ] );
		add_action( 'pre_update_option_impressum_license_options', [ $this, 'impressum_protect_license_key' ] );
	}
	
	/**
	 * Add plugin meta links.
	 * 
	 * @param array $input Registered links.
	 * @param string $file  Current plugin file.
	 * @return array Merged links
	 */
	public static function add_meta_link( $input, $file ) {
		// bail on other plugins
		if ( IMPRESSUM_BASE !== $file ) return $input;
		
		return array_merge(
			$input,
			[
				'<a href="https://impressum.plus/preise/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Get Plus', 'impressum' ) . '</a>',
				'<a href="https://impressum.plus/dokumentation/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Documentation', 'impressum' ) . '</a>',
			]
		);
	}
	
	
	/**
	 * Custom option and settings.
	 */
	public static function impressum_settings_init() {
		// register a new setting for "impressum" page
		register_setting( 'impressum_imprint', 'impressum_imprint_options' );
		register_setting( 'impressum_license', 'impressum_license_options' );
		register_setting( 'impressum_privacy', 'impressum_privacy_options' );
		
		// register a new section in the "impressum" page
		add_settings_section(
			'impressum_section_imprint',
			null,
			null,
			'impressum_imprint'
		);
		// register a new section in the "impressum" page
		add_settings_section(
			'impressum_section_license',
			null,
			null,
			'impressum_license'
		);
		// register a new section in the "impressum" page
		add_settings_section(
			'impressum_section_privacy',
			null,
			null,
			'impressum_privacy'
		);
		
		/**
		 * Register option fields
		 */
		
		// country
		add_settings_field(
			'country',
			__( 'Country', 'impressum' ),
			[ __CLASS__, 'country_callback' ],
			'impressum_imprint',
			'impressum_section_imprint',
			[
				'label_for' => 'country',
				'class' => 'impressum_row',
			]
		);
		
		// legal entity
		add_settings_field(
			'legal_entity',
			__( 'Legal Entity', 'impressum' ),
			[ __CLASS__, 'legal_entity_callback' ],
			'impressum_imprint',
			'impressum_section_imprint',
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
			'impressum_imprint',
			'impressum_section_imprint',
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
			'impressum_imprint',
			'impressum_section_imprint',
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
			'impressum_imprint',
			'impressum_section_imprint',
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
			'impressum_imprint',
			'impressum_section_imprint',
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
			'impressum_imprint',
			'impressum_section_imprint',
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
			'impressum_imprint',
			'impressum_section_imprint',
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
			'impressum_imprint',
			'impressum_section_imprint',
			[
				'label_for' => 'press_law_checkbox',
				'class' => 'impressum_row',
			]
		);
		
		// press law person
		add_settings_field(
			'press_law_person',
			__( 'Responsible for content according to § 55 paragraph 2 RStV', 'impressum' ),
			[ __CLASS__, 'impressum_textarea_callback' ],
			'impressum_imprint',
			'impressum_section_imprint',
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
			'impressum_imprint',
			'impressum_section_imprint',
			[
				'label_for' => 'vat_id',
				'class' => 'impressum_row vat_id',
			]
		);
		
		// coverage
		add_settings_field(
			'coverage',
			__( 'Coverage', 'impressum' ),
			[ __CLASS__, 'impressum_input_text_callback' ],
			'impressum_imprint',
			'impressum_section_imprint',
			[
				'label_for' => 'coverage',
				'class' => 'impressum_row coverage',
			]
		);
		
		// free text
		add_settings_field(
			'free_text',
			__( 'Free Text', 'impressum' ),
			[ __CLASS__, 'impressum_textarea_callback' ],
			'impressum_imprint',
			'impressum_section_imprint',
			[
				'label_for' => 'free_text',
				'class' => 'impressum_row free_text',
			]
		);
		
		// inspecting authority
		add_settings_field(
			'inspecting_authority',
			__( 'Inspecting Authority', 'impressum' ),
			[ __CLASS__, 'impressum_textarea_callback' ],
			'impressum_imprint',
			'impressum_section_imprint',
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
			'impressum_imprint',
			'impressum_section_imprint',
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
			'impressum_imprint',
			'impressum_section_imprint',
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
			'impressum_imprint',
			'impressum_section_imprint',
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
			'impressum_imprint',
			'impressum_section_imprint',
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
			'impressum_imprint',
			'impressum_section_imprint',
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
			'impressum_imprint',
			'impressum_section_imprint',
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
			'impressum_imprint',
			'impressum_section_imprint',
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
			'impressum_imprint',
			'impressum_section_imprint',
			[
				'label_for' => 'professional_regulations',
				'class' => 'impressum_row impressum_professional_regulations',
			]
		);
		
		// email
		add_settings_field(
			'license_email',
			__( 'Email Address', 'impressum' ),
			[ __CLASS__, 'impressum_license_input_email_callback' ],
			'impressum_license',
			'impressum_section_license',
			[
				'label_for' => 'license_email',
				'class' => 'impressum_row impressum_license_email',
			]
		);
		
		// license key
		add_settings_field(
			'license_key',
			__( 'License Key', 'impressum' ),
			[ __CLASS__, 'impressum_license_input_license_callback' ],
			'impressum_license',
			'impressum_section_license',
			[
				'label_for' => 'license_key',
				'class' => 'impressum_row impressum_license_key',
			]
		);
		
		// comment subscription checkbox
		add_settings_field(
			'comment_subscription_checkbox',
			__( 'Comment subscription', 'impressum' ),
			[ __CLASS__, 'comment_subscription_checkbox_callback' ],
			'impressum_privacy',
			'impressum_section_privacy',
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
			'impressum_privacy',
			'impressum_section_privacy',
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
			'impressum_privacy',
			'impressum_section_privacy',
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
			'impressum_privacy',
			'impressum_section_privacy',
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
			'impressum_privacy',
			'impressum_section_privacy',
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
			'impressum_privacy',
			'impressum_section_privacy',
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
			'impressum_privacy',
			'impressum_section_privacy',
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
			'impressum_privacy',
			'impressum_section_privacy',
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
			'impressum_privacy',
			'impressum_section_privacy',
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
			'impressum_privacy',
			'impressum_section_privacy',
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
			'impressum_privacy',
			'impressum_section_privacy',
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
			'impressum_privacy',
			'impressum_section_privacy',
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
			'impressum_privacy',
			'impressum_section_privacy',
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
			'impressum_privacy',
			'impressum_section_privacy',
			[
				'label_for' => 'amazon_partner_checkbox',
				'class' => 'impressum_row',
			]
		);
	}
	
	/**
	 * Country field callback.
	 * 
	 * @param	array		$args The arguments from the HTML field
	 */
	public static function country_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_imprint_options' );
		?>
<select id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
	<option value=""><?php esc_html_e( 'Please select &hellip;', 'impressum' ); ?></option>
		<?php
		foreach ( self::$countries as $country_code => $country ) {
			$is_selected = ( isset( $options['country'] ) ? selected( $options['country'], $country_code, false ) : '' );
			
			echo '<option value="' . esc_attr( $country_code ) . '"' . ( $is_selected ?: '' ) . '>' . esc_html( $country ) . '</option>';
		}
		?>
</select>
<p><?php esc_html_e( 'In order to determine the needed fields for your imprint we need to know your country.', 'impressum' ); ?></p>
		<?php
	}
	
	/**
	 * Legal Entity field callback.
	 * 
	 * @param	array		$args The arguments from the HTML field
	 */
	public static function legal_entity_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_imprint_options' );
		
		// output the field
		?>
<select id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
		<?php
		foreach ( self::$legal_entities as $abbr => $entity ) {
			$is_selected = ( isset( $options['legal_entity'] ) ? selected( $options['legal_entity'], $abbr, false ) : '' );
			
			echo '<option value="' . esc_attr( $abbr ) . '"' . ( $is_selected ?: '' ) . '>' . esc_html( $entity ) . '</option>';
		}
		?>
</select>
<p><?php esc_html_e( 'In order to guide you the needed fields we need to know what kind of legal entity you are.', 'impressum' ); ?></p>
		<?php
	}
	
	/**
	 * Email field callback.
	 * 
	 * @param	array		$args The arguments from the HTML field
	 */
	public static function impressum_email_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_imprint_options' );
		// output the field
		?>
<input type="email" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . esc_attr( $options[ $args['label_for'] ] ) . '"' : '' ); ?>>
		<?php
	}
	
	/**
	 * Text input field callback.
	 * 
	 * @param	array		$args The arguments from the HTML field
	 */
	public static function impressum_input_text_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_imprint_options' );
		// output the field
		?>
<input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . esc_attr( $options[ $args['label_for'] ] ) . '"' : '' ); ?>>
		<?php
		switch ( $args['label_for'] ) {
			case 'coverage':
				echo '<p>' . esc_html__( 'If you link to this imprint from several other domains, enter them here.', 'impressum' ) . '</p>';
				break;
			case 'register':
				echo '<p>' . esc_html__( 'You need at least enter your register number and the register where your company is registered.', 'impressum' ) . '</p>';
				break;
			case 'vat_id':
				echo '<p>' . esc_html__( 'Your VAT ID in format XX123456789, which means at least two letters by following some numbers (the amount depends on your country).', 'impressum' ) . '</p>';
				break;
		}
	}
	
	/**
	 * Email input field callback.
	 * @param $args array
	 */
	public static function impressum_license_input_email_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_license_options' );
		// output the field
		?>
<input type="email" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_license_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . esc_attr( $options[ $args['label_for'] ] ) . '"' : '' ); ?>>
		<?php
	}
	
	/**
	 * Text input field callback.
	 * @param $args array
	 */
	public static function impressum_license_input_text_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_imprint_options' );
		// output the field
		?>
<input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_license_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . esc_attr( $options[ $args['label_for'] ] ) . '"' : '' ); ?>>
		<?php
	}
	
	/**
	 * License (password) input field callback.
	 * @param $args array
	 */
	public static function impressum_license_input_license_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_license_options' );
		// output the field
		?>
<input type="password" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_license_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . esc_attr( str_repeat( '*', strlen( $options[ $args['label_for'] ] ) ) ) . '"' : '' ); ?>>
		<?php
	}
	
	/**
	 * Phone field callback.
	 * 
	 * @param	array		$args The arguments from the HTML field
	 */
	public static function impressum_phone_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_imprint_options' );
		// output the field
		?>
<input type="tel" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" class="regular-text"<?php echo ( isset( $options[ $args['label_for'] ] ) ? ' value="' . esc_attr( $options[ $args['label_for'] ] ) . '"' : '' ); ?>>
		<?php
	}
	
	/**
	 * Press Law Checkbox field callback.
	 * 
	 * @param	array		$args The arguments from the HTML field
	 */
	public static function impressum_press_law_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_imprint_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php esc_html__( 'I have journalistic/editorial content on my website', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * Textarea callback.
	 * 
	 * @param	array		$args The arguments from the HTML field
	 */
	public static function impressum_textarea_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_imprint_options' );
		// output the field
		?>
<textarea cols="50" rows="10" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_imprint_options[<?php echo esc_attr( $args['label_for'] ); ?>]"><?php echo ( isset( $options[ $args['label_for'] ] ) ? esc_html( $options[ $args['label_for'] ] ) : '' ); ?></textarea>
		<?php
		switch ( $args['label_for'] ) {
			case 'address':
				echo '<p>' . esc_html__( 'You need to set at least your street with number, your zip code and your city.', 'impressum' ) . '</p>';
				break;
			case 'address_alternative':
				echo '<p>' . esc_html__( 'You can set an alternative address to be displayed in your imprint.', 'impressum' ) . '</p>';
				break;
			case 'free_text':
				echo '<p>' . esc_html__( 'You can add some additional free text if the predefined input fields don’t suite your needs.', 'impressum' ) . '</p>';
				break;
		}
	}
	
	/**
	 * Comment subscription Checkbox field callback.
	 * @param $args array
	 */
	public static function comment_subscription_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php esc_html_e( 'I use a comment subscription plugin.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * Newsletter Checkbox field callback.
	 * @param $args array
	 */
	public static function newsletter_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php esc_html_e( 'I use a newsletter plugin or service.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * 3rd party content Checkbox field callback.
	 * @param $args array
	 */
	public static function third_party_content_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php esc_html_e( 'I embed tweets, YouTube videos or other 3rd-party content.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * Cookie Checkbox field callback.
	 * @param $args array
	 */
	public static function cookie_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php esc_html_e( 'I use cookies on my site.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * User Registration Checkbox field callback.
	 * @param $args array
	 */
	public static function user_registration_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php esc_html_e( 'Users can register on my site.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * Google Analytics Checkbox field callback.
	 * @param $args array
	 */
	public static function google_analytics_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php esc_html_e( 'I use Google Analytics.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * Matomo/Piwik Checkbox field callback.
	 * @param $args array
	 */
	public static function piwik_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php esc_html_e( 'I use Matomo/Piwik.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * Facebook Checkbox field callback.
	 * @param $args array
	 */
	public static function facebook_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php esc_html_e( 'I use a Facebook social button on my website.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * Twitter Checkbox field callback.
	 * @param $args array
	 */
	public static function twitter_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php esc_html_e( 'I use a Twitter social button on my website.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * Google Plus Checkbox field callback.
	 * @param $args array
	 */
	public static function google_plus_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php esc_html_e( 'I use a Google+ social button on my website.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * tumblr Checkbox field callback.
	 * @param $args array
	 */
	public static function tumblr_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php esc_html_e( 'I use a Tumblr social button on my website.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * Jetpack Checkbox field callback.
	 * @param $args array
	 */
	public static function jetpack_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php esc_html_e( 'I use the Jetpack plugin.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * Google Adsense Checkbox field callback.
	 * @param $args array
	 */
	public static function google_adsense_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php esc_html_e( 'I use Google AdSense on my website.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	 * Amazon Partner Checkbox field callback.
	 * @param $args array
	 */
	public static function amazon_partner_checkbox_callback( array $args ) {
		// get the value of the setting we've registered with register_setting()
		$options = self::impressum_get_option( 'impressum_privacy_options' );
		// output the field
		?>
<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="impressum_privacy_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="1"<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
	<?php esc_html_e( 'I use Amazon affiliate links on my website.', 'impressum' ); ?>
</label>
		<?php
	}
	
	/**
	* Add sub menu item in options menu.
	*/
	public static function impressum_options_page() {
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
	* Add sub menu item in network options menu.
	*/
	public static function impressum_network_options_page() {
		// add top level menu page
		add_submenu_page(
			'settings.php',
			'Impressum',
			'Impressum',
			'manage_network_options',
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
		
		// get current tab
		// phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification
		$current_tab = isset( $_GET['imprint_tab'] ) ? sanitize_text_field( wp_unslash( $_GET['imprint_tab'] ) ) : 'imprint';
		// phpcs:enable
		
		// set form action
		$form_action = 'options.php';
		
		if ( is_network_admin() ) {
			// phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification
			if ( isset( $_GET['updated'] ) ) {
			// phpcs:enable
				?>
				<div id="message" class="updated notice is-dismissible">
					<p><?php esc_html_e( 'Options saved.', 'impressum' ) ?></p>
				</div>
				<?php
			}
			
			// modify form action
			$form_action = 'edit.php?action=impressum_network_options_update';
		}
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<h2 class="nav-tab-wrapper">
		<a href="?page=impressum&imprint_tab=imprint" class="nav-tab <?php echo $current_tab === 'imprint' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Imprint', 'impressum' ); ?></a>
		<a href="?page=impressum&imprint_tab=privacy" class="nav-tab <?php echo $current_tab === 'privacy' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Privacy', 'impressum' ); ?></a>
		<a href="?page=impressum&imprint_tab=license" class="nav-tab <?php echo $current_tab === 'license' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'License', 'impressum' ); ?></a>
	</h2>
	<?php // phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found ?>
	
	<?php // phpcs:enable ?>
	<div class="impressum-content-wrapper">
	<?php
	switch ( $current_tab ) {
		case 'imprint':
			echo '<form action="' . esc_html( $form_action ) . '" method="post">';
			// output security fields for the registered setting "impressum"
			settings_fields( 'impressum_' . $current_tab );
			// output setting sections and their fields
			// (sections are registered for "impressum", each field is registered to a specific section)
			do_settings_sections( 'impressum_' . $current_tab );
			// disclaimer
			echo '<h3>' . esc_html__( 'Disclaimer', 'impressum' ) . '</h3>';
			echo '<p>' . esc_html__( 'Please keep in mind that this plugin does not guarantee any legal compliance. You are responsible for the data you enter here. “Impressum” helps you to fill all necessary fields.', 'impressum' ) . '</p>';
			// output save settings button
			submit_button( esc_html__( 'Save Settings', 'impressum' ) );
			echo '</form>';
			// usage description
			echo '<h3>' . esc_html__( 'Usage', 'impressum' ) . '</h3>';
			/* translators: the shortcode */
			echo '<p>' . sprintf( esc_html__( 'Add the %1$s shortcode wherever you want to output your imprint. It works on pages, posts and even widgets (anywhere shortcodes work).', 'impressum' ), '<code>[impressum]</code>' ) . '</p>';
			break;
		case 'license':
		case 'privacy':
			echo '<form action="' . esc_html( $form_action ) . '" method="post">';
			// output security fields for the registered setting "impressum"
			settings_fields( 'impressum_' . $current_tab );
			// output setting sections and their fields
			// (sections are registered for "impressum", each field is registered to a specific section)
			do_settings_sections( 'impressum_' . $current_tab );
			// output save settings button
			submit_button( __( 'Save Settings', 'impressum' ) );
			echo '</form>';
			break;
	}
	?>
	</div>
</div>
	<?php
	}
	
	/**
	 * Update network options.
	 */
	public static function impressum_network_options_update() {
		if (
			! isset( $_POST['option_page'], $_POST['option_page_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['option_page_nonce'] ) )
		) return;
		
		// get most recent active tab
		$tab = substr( strstr( sanitize_text_field( wp_unslash( $_POST['option_page'] ) ), '_' ), 1 );
		
		// make sure we are posting from our options page
		check_admin_referer( 'impressum_' . $tab . '-options' );
		
		// list of registered options
		global $new_whitelist_options;
		$options = array_merge(
			$new_whitelist_options['impressum_imprint'],
			$new_whitelist_options['impressum_license'],
			$new_whitelist_options['impressum_privacy']
		);
		
		foreach ( $options as $option ) {
			if ( isset( $_POST[ $option ] ) ) {
				update_site_option( $option, sanitize_text_field( wp_unslash( $_POST[ $option ] ) ) );
			}
		}
		
		// redirect to network options page
		wp_redirect( add_query_arg( [
			'page' => 'impressum&imprint_tab=' . $tab,
			'updated' => 'true',
		], network_admin_url( 'settings.php' ) ) );
		exit;
	}
	
	/**
	 * Protect the license key against source code sniffing.
	 * 
	 * @param $value The new value
	 * @return mixed|void
	 */
	public function impressum_protect_license_key( $value ) {
		// if license key contains an asterisk, take the previous value
		if ( strpos( $value['license_key'], '*' ) !== false ) {
			$old_value = self::impressum_get_option( 'impressum_license_options' )['license_key'];
			$value['license_key'] = $old_value;
		}
		
		return $value;
	}
}
