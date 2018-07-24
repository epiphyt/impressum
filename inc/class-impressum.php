<?php
namespace epiphyt\Impressum;

// exit if ABSPATH is not defined
defined( 'ABSPATH' ) || exit;

/**
 * The main Impressum class.
 * 
 * @version		0.1
 * @author		Epiphyt
 * @license		GPL3 <https://www.gnu.org/licenses/gpl-3.0.html>
 */
class Impressum {
	/**
	 * @var		array Countries with their country codes in 3-digit ISO form
	 */
	protected static $countries = [];
	
	/**
	 * @var		array All legal entities we support
	 */
	protected static $legal_entities = [];
	
	/**
	 * @var		string The full path to the main plugin file
	 */
	public $plugin_file = '';
	
	/**
	 * @var		array All settings fields for the backend.
	 */
	protected static $settings_fields = [];
	
	/**
	 * Impressum constructor.
	 * 
	 * @param	string		$plugin_file The path of the main plugin file
	 */
	public function __construct( $plugin_file ) {
		// return on Ajax or autosave
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}
		
		// assign variables
		$this->plugin_file = $plugin_file;
		
		add_action( 'init', [ $this, 'load_textdomain' ] );
		// need to run after text domain has been loaded
		add_action( 'init', [ __CLASS__, 'load_settings' ] );
	}
	
	/**
	 * Load our settings in an array.
	 */
	public static function load_settings() {
		self::$settings_fields = [
			'country' => [
				'title' => __( 'Country', 'impressum' ),
				'callback' => 'country_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'country',
					'class' => 'impressum_row',
				],
			],
			'legal_entity' => [
				'title' => __( 'Legal Entity', 'impressum' ),
				'callback' => 'legal_entity_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'legal_entity',
					'class' => 'impressum_row',
				],
			],
			'name' => [
				'title' => __( 'Name', 'impressum' ),
				'callback' => 'impressum_input_text_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'name',
					'class' => 'impressum_row',
				],
			],
			'address' => [
				'title' => __( 'Address', 'impressum' ),
				'callback' => 'impressum_textarea_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'address',
					'class' => 'impressum_row',
				],
			],
			'address_alternative' => [
				'title' => __( 'Alternative Address', 'impressum' ),
				'callback' => 'impressum_textarea_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'address_alternative',
					'class' => 'impressum_row',
				],
			],
			'email' => [
				'title' => __( 'Email Address', 'impressum' ),
				'callback' => 'impressum_email_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'email',
					'class' => 'impressum_row',
				],
			],
			'phone' => [
				'title' => __( 'Telephone', 'impressum' ),
				'callback' => 'impressum_phone_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'phone',
					'class' => 'impressum_row',
				],
			],
			'fax' => [
				'title' => __( 'Fax', 'impressum' ),
				'callback' => 'impressum_phone_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'fax',
					'class' => 'impressum_row',
				],
			],
			'press_law_checkbox' => [
				'title' => __( 'Journalistic/Editorial Content', 'impressum' ),
				'callback' => 'impressum_press_law_checkbox_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'press_law_checkbox',
					'class' => 'impressum_row',
				],
			],
			'press_law_person' => [
				'title' => __( 'Responsible for content according to § 55 paragraph 2 RStV', 'impressum' ),
				'callback' => 'impressum_textarea_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'press_law_person',
					'class' => 'impressum_row impressum_press_law',
				],
			],
			'vat_id' => [
				'title' => __( 'VAT ID', 'impressum' ),
				'callback' => 'impressum_input_text_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'vat_id',
					'class' => 'impressum_row vat_id',
				],
			],
			'coverage' => [
				'title' => __( 'Coverage', 'impressum' ),
				'callback' => 'impressum_input_text_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'coverage',
					'class' => 'impressum_row coverage',
				],
			],
			'free_text' => [
				'title' => __( 'Free Text', 'impressum' ),
				'callback' => 'impressum_textarea_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'free_text',
					'class' => 'impressum_row free_text',
				],
			],
			'inspecting_authority' => [
				'title' => __( 'Inspecting Authority', 'impressum' ),
				'callback' => 'impressum_textarea_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'inspecting_authority',
					'class' => 'impressum_row impressum_inspecting_authority',
				],
			],
			'register' => [
				'title' => __( 'Register', 'impressum' ),
				'callback' => 'impressum_input_text_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'register',
					'class' => 'impressum_row impressum_register',
				],
			],
			'business_id' => [
				'title' => __( 'Business ID', 'impressum' ),
				'callback' => 'impressum_input_text_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'business_id',
					'class' => 'impressum_row impressum_business_id',
				],
			],
			'representative' => [
				'title' => __( 'Representative', 'impressum' ),
				'callback' => 'impressum_textarea_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'representative',
					'class' => 'impressum_row impressum_representative',
				],
			],
			'capital_stock' => [
				'title' => __( 'Capital Stock', 'impressum' ),
				'callback' => 'impressum_input_text_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'capital_stock',
					'class' => 'impressum_row impressum_capital_stock',
				],
			],
			'pending_deposits' => [
				'title' => __( 'Pending Deposits', 'impressum' ),
				'callback' => 'impressum_input_text_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'pending_deposits',
					'class' => 'impressum_row impressum_pending_deposits',
				],
			],
			'professional_association' => [
				'title' => __( 'Professional Association', 'impressum' ),
				'callback' => 'impressum_input_text_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'professional_association',
					'class' => 'impressum_row impressum_professional_association',
				],
			],
			'legal_job_title' => [
				'title' => __( 'Legal Job Title', 'impressum' ),
				'callback' => 'impressum_input_text_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'legal_job_title',
					'class' => 'impressum_row impressum_legal_job_title',
				],
			],
			'professional_regulations' => [
				'title' => __( 'Professional Regulations', 'impressum' ),
				'callback' => 'impressum_textarea_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'professional_regulations',
					'class' => 'impressum_row impressum_professional_regulations',
				],
			],
			'license_email' => [
				'title' => __( 'Email Address', 'impressum' ),
				'callback' => 'impressum_license_input_email_callback',
				'page' => 'impressum_license',
				'section' => 'impressum_section_license',
				'args' => [
					'label_for' => 'license_email',
					'class' => 'impressum_row impressum_license_email',
				],
			],
			'license_key' => [
				'title' => __( 'License Key', 'impressum' ),
				'callback' => 'impressum_license_input_license_callback',
				'page' => 'impressum_license',
				'section' => 'impressum_section_license',
				'args' => [
					'label_for' => 'license_key',
					'class' => 'impressum_row impressum_license_key',
				],
			],
		];
	}
	
	/**
	 * Load translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'impressum', false, dirname( plugin_basename( $this->plugin_file ) ) . '/languages' );
		
		// set the value of countries
		self::$countries = [
			'dza' => __( 'Algeria', 'impressum' ),
			'arg' => __( 'Argentinia', 'impressum' ),
			'aus' => __( 'Australia', 'impressum' ),
			'aut' => __( 'Austria', 'impressum' ),
			'bel' => __( 'Belgium', 'impressum' ),
			'bra' => __( 'Brasil', 'impressum' ),
			'bgr' => __( 'Bulgaria', 'impressum' ),
			'can' => __( 'Canada', 'impressum' ),
			'chl' => __( 'Chile', 'impressum' ),
			'chn' => __( 'China', 'impressum' ),
			'col' => __( 'Columbia', 'impressum' ),
			'hrv' => __( 'Croatia', 'impressum' ),
			'cze' => __( 'Czech Republic', 'impressum' ),
			'dnk' => __( 'Denmark', 'impressum' ),
			'est' => __( 'Estonia', 'impressum' ),
			'fin' => __( 'Finland', 'impressum' ),
			'fra' => __( 'France', 'impressum' ),
			'deu' => __( 'Germany', 'impressum' ),
			'grc' => __( 'Greece', 'impressum' ),
			'hkg' => __( 'Hong Kong', 'impressum' ),
			'hun' => __( 'Hungary', 'impressum' ),
			'idn' => __( 'Indonesia', 'impressum' ),
			'irl' => __( 'Ireland', 'impressum' ),
			'isr' => __( 'Israel', 'impressum' ),
			'ita' => __( 'Italy', 'impressum' ),
			'jpn' => __( 'Japan', 'impressum' ),
			'ltu' => __( 'Lithuania', 'impressum' ),
			'nld' => __( 'Netherlands', 'impressum' ),
			'nzl' => __( 'New Zealand', 'impressum' ),
			'nor' => __( 'Norway', 'impressum' ),
			'pol' => __( 'Poland', 'impressum' ),
			'prt' => __( 'Portugal', 'impressum' ),
			'rou' => __( 'Romania', 'impressum' ),
			'rus' => __( 'Russia', 'impressum' ),
			'srb' => __( 'Serbia', 'impressum' ),
			'svn' => __( 'Slowenia', 'impressum' ),
			'zaf' => __( 'South Africa', 'impressum' ),
			'kor' => __( 'South Korea', 'impressum' ),
			'esp' => __( 'Spain', 'impressum' ),
			'swe' => __( 'Sweden', 'impressum' ),
			'che' => __( 'Switzerland', 'impressum' ),
			'twn' => __( 'Taiwan', 'impressum' ),
			'tha' => __( 'Thailand', 'impressum' ),
			'tur' => __( 'Turky', 'impressum' ),
			'gbr' => __( 'United Kingdom', 'impressum' ),
			'usa' => __( 'United States', 'impressum' ),
			'ven' => __( 'Venezuela', 'impressum' ),
			'vnm' => __( 'Vietnam', 'impressum' ),
			'other' => __( 'other', 'impressum' ),
		];
		
		self::$legal_entities = [
			'ag' => __( 'AG', 'impressum' ),
			'ev' => __( 'e.V.', 'impressum' ),
			'ek' => __( 'e.K.', 'impressum' ),
			'einzelkaufmann' => __( 'Einzelkaufmann', 'impressum' ),
			'freelancer' => __( 'Freelancer', 'impressum' ),
			'ggmbh' => __( 'gGmbH', 'impressum' ),
			'gmbh' => __( 'GmbH', 'impressum' ),
			'gbr' => __( 'GbR', 'impressum' ),
			'gmbh_co_kg' => __( 'GmbH & Co. KG', 'impressum' ),
			'kg' => __( 'KG', 'impressum' ),
			'kgag' => __( 'KGaA', 'impressum' ),
			'ohg' => __( 'OHG', 'impressum' ),
			'individual' => __( 'Individual', 'impressum' ),
			'self' => __( 'Self-employed', 'impressum' ),
			'ug' => __( 'UG (haftungsbeschränkt)', 'impressum' ),
			'ug_co_kg' => __( 'UG (haftungsbeschränkt) & Co. KG', 'impressum' ),
		];
		
		// make sure the array is always sorted depending on localization
		asort( self::$countries );
		natcasesort( self::$legal_entities );
	}
	
	/**
	 * Get an option or a site option with the same name.
	 * The site option is received if there is no option
	 * with the same name.
	 * 
	 * @param	string		$option The option you want to get
	 * @return	mixed|void
	 */
	protected static function impressum_get_option( $option ) {
		if ( ! is_string( $option ) ) return;
		
		if ( ! is_network_admin() ) {
			// try receive option
			$options = get_option( $option );
			
			if ( ! $options ) {
				$options = get_site_option( $option );
			}
		}
		else {
			$options = get_site_option( $option );
		}
		
		return $options;
	}
}
