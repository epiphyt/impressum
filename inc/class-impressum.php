<?php
namespace epiphyt\Impressum;

// exit if ABSPATH is not defined
defined( 'ABSPATH' ) || exit;

/**
 * The main Impressum class.
 * 
 * @author		Epiphyt
 * @license		GPL3 <https://www.gnu.org/licenses/gpl-3.0.html>
 * @version		1.0.0
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
				'title' => __( 'Country', 'impressum-plus' ),
				'callback' => 'country_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'country',
					'class' => 'impressum_row',
				],
			],
			'legal_entity' => [
				'title' => __( 'Legal Entity', 'impressum-plus' ),
				'callback' => 'legal_entity_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'legal_entity',
					'class' => 'impressum_row',
				],
			],
			'name' => [
				'title' => __( 'Name', 'impressum-plus' ),
				'callback' => 'impressum_input_text_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'name',
					'class' => 'impressum_row',
				],
			],
			'address' => [
				'title' => __( 'Address', 'impressum-plus' ),
				'callback' => 'impressum_textarea_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'address',
					'class' => 'impressum_row',
				],
			],
			'address_alternative' => [
				'title' => __( 'Alternative Address', 'impressum-plus' ),
				'callback' => 'impressum_textarea_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'address_alternative',
					'class' => 'impressum_row',
				],
			],
			'email' => [
				'title' => __( 'Email Address', 'impressum-plus' ),
				'callback' => 'impressum_email_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'email',
					'class' => 'impressum_row',
				],
			],
			'phone' => [
				'title' => __( 'Telephone', 'impressum-plus' ),
				'callback' => 'impressum_phone_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'phone',
					'class' => 'impressum_row',
				],
			],
			'fax' => [
				'title' => __( 'Fax', 'impressum-plus' ),
				'callback' => 'impressum_phone_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'fax',
					'class' => 'impressum_row',
				],
			],
			'press_law_checkbox' => [
				'title' => __( 'Journalistic/Editorial Content', 'impressum-plus' ),
				'callback' => 'impressum_press_law_checkbox_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'press_law_checkbox',
					'class' => 'impressum_row',
				],
			],
			'press_law_person' => [
				'title' => __( 'Responsible for content according to § 55 paragraph 2 RStV', 'impressum-plus' ),
				'callback' => 'impressum_textarea_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'press_law_person',
					'class' => 'impressum_row impressum_press_law',
				],
			],
			'vat_id' => [
				'title' => __( 'VAT ID', 'impressum-plus' ),
				'callback' => 'impressum_input_text_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'vat_id',
					'class' => 'impressum_row vat_id',
				],
			],
			'coverage' => [
				'title' => __( 'Coverage', 'impressum-plus' ),
				'callback' => 'impressum_input_text_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'coverage',
					'class' => 'impressum_row coverage',
				],
			],
			'free_text' => [
				'title' => __( 'Free Text', 'impressum-plus' ),
				'callback' => 'impressum_textarea_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'free_text',
					'class' => 'impressum_row free_text',
				],
			],
			'inspecting_authority' => [
				'title' => __( 'Inspecting Authority', 'impressum-plus' ),
				'callback' => 'impressum_textarea_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'inspecting_authority',
					'class' => 'impressum_row impressum_inspecting_authority',
				],
			],
			'register' => [
				'title' => __( 'Register', 'impressum-plus' ),
				'callback' => 'impressum_input_text_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'register',
					'class' => 'impressum_row impressum_register',
				],
			],
			'business_id' => [
				'title' => __( 'Business ID', 'impressum-plus' ),
				'callback' => 'impressum_input_text_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'business_id',
					'class' => 'impressum_row impressum_business_id',
				],
			],
			'representative' => [
				'title' => __( 'Representative', 'impressum-plus' ),
				'callback' => 'impressum_textarea_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'representative',
					'class' => 'impressum_row impressum_representative',
				],
			],
			'capital_stock' => [
				'title' => __( 'Capital Stock', 'impressum-plus' ),
				'callback' => 'impressum_input_text_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'capital_stock',
					'class' => 'impressum_row impressum_capital_stock',
				],
			],
			'pending_deposits' => [
				'title' => __( 'Pending Deposits', 'impressum-plus' ),
				'callback' => 'impressum_input_text_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'pending_deposits',
					'class' => 'impressum_row impressum_pending_deposits',
				],
			],
			'professional_association' => [
				'title' => __( 'Professional Association', 'impressum-plus' ),
				'callback' => 'impressum_input_text_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'professional_association',
					'class' => 'impressum_row impressum_professional_association',
				],
			],
			'legal_job_title' => [
				'title' => __( 'Legal Job Title', 'impressum-plus' ),
				'callback' => 'impressum_input_text_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'legal_job_title',
					'class' => 'impressum_row impressum_legal_job_title',
				],
			],
			'professional_regulations' => [
				'title' => __( 'Professional Regulations', 'impressum-plus' ),
				'callback' => 'impressum_textarea_callback',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'professional_regulations',
					'class' => 'impressum_row impressum_professional_regulations',
				],
			],
			'license_email' => [
				'title' => __( 'Email Address', 'impressum-plus' ),
				'callback' => 'impressum_license_input_email_callback',
				'page' => 'impressum_license',
				'section' => 'impressum_section_license',
				'args' => [
					'label_for' => 'license_email',
					'class' => 'impressum_row impressum_license_email',
				],
			],
			'license_key' => [
				'title' => __( 'License Key', 'impressum-plus' ),
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
		load_plugin_textdomain( 'impressum-plus', false, dirname( plugin_basename( $this->plugin_file ) ) . '/languages' );
		
		// set the value of countries
		self::$countries = [
			'dza' => __( 'Algeria', 'impressum-plus' ),
			'arg' => __( 'Argentinia', 'impressum-plus' ),
			'aus' => __( 'Australia', 'impressum-plus' ),
			'aut' => __( 'Austria', 'impressum-plus' ),
			'bel' => __( 'Belgium', 'impressum-plus' ),
			'bra' => __( 'Brazil', 'impressum-plus' ),
			'bgr' => __( 'Bulgaria', 'impressum-plus' ),
			'can' => __( 'Canada', 'impressum-plus' ),
			'chl' => __( 'Chile', 'impressum-plus' ),
			'chn' => __( 'China', 'impressum-plus' ),
			'col' => __( 'Columbia', 'impressum-plus' ),
			'hrv' => __( 'Croatia', 'impressum-plus' ),
			'cze' => __( 'Czech Republic', 'impressum-plus' ),
			'dnk' => __( 'Denmark', 'impressum-plus' ),
			'est' => __( 'Estonia', 'impressum-plus' ),
			'fin' => __( 'Finland', 'impressum-plus' ),
			'fra' => __( 'France', 'impressum-plus' ),
			'deu' => __( 'Germany', 'impressum-plus' ),
			'grc' => __( 'Greece', 'impressum-plus' ),
			'hkg' => __( 'Hong Kong', 'impressum-plus' ),
			'hun' => __( 'Hungary', 'impressum-plus' ),
			'idn' => __( 'Indonesia', 'impressum-plus' ),
			'irl' => __( 'Ireland', 'impressum-plus' ),
			'isr' => __( 'Israel', 'impressum-plus' ),
			'ita' => __( 'Italy', 'impressum-plus' ),
			'jpn' => __( 'Japan', 'impressum-plus' ),
			'ltu' => __( 'Lithuania', 'impressum-plus' ),
			'nld' => __( 'Netherlands', 'impressum-plus' ),
			'nzl' => __( 'New Zealand', 'impressum-plus' ),
			'nor' => __( 'Norway', 'impressum-plus' ),
			'pol' => __( 'Poland', 'impressum-plus' ),
			'prt' => __( 'Portugal', 'impressum-plus' ),
			'rou' => __( 'Romania', 'impressum-plus' ),
			'rus' => __( 'Russia', 'impressum-plus' ),
			'srb' => __( 'Serbia', 'impressum-plus' ),
			'svn' => __( 'Slowenia', 'impressum-plus' ),
			'zaf' => __( 'South Africa', 'impressum-plus' ),
			'kor' => __( 'South Korea', 'impressum-plus' ),
			'esp' => __( 'Spain', 'impressum-plus' ),
			'swe' => __( 'Sweden', 'impressum-plus' ),
			'che' => __( 'Switzerland', 'impressum-plus' ),
			'twn' => __( 'Taiwan', 'impressum-plus' ),
			'tha' => __( 'Thailand', 'impressum-plus' ),
			'tur' => __( 'Turkey', 'impressum-plus' ),
			'gbr' => __( 'United Kingdom', 'impressum-plus' ),
			'usa' => __( 'United States', 'impressum-plus' ),
			'ven' => __( 'Venezuela', 'impressum-plus' ),
			'vnm' => __( 'Vietnam', 'impressum-plus' ),
			'other' => __( 'other', 'impressum-plus' ),
		];
		
		self::$legal_entities = [
			'ag' => __( 'AG', 'impressum-plus' ),
			'ev' => __( 'e.V.', 'impressum-plus' ),
			'ek' => __( 'e.K.', 'impressum-plus' ),
			'einzelkaufmann' => __( 'Einzelkaufmann', 'impressum-plus' ),
			'freelancer' => __( 'Freelancer', 'impressum-plus' ),
			'ggmbh' => __( 'gGmbH', 'impressum-plus' ),
			'gmbh' => __( 'GmbH', 'impressum-plus' ),
			'gbr' => __( 'GbR', 'impressum-plus' ),
			'gmbh_co_kg' => __( 'GmbH & Co. KG', 'impressum-plus' ),
			'kg' => __( 'KG', 'impressum-plus' ),
			'kgag' => __( 'KGaA', 'impressum-plus' ),
			'ohg' => __( 'OHG', 'impressum-plus' ),
			'individual' => __( 'Individual', 'impressum-plus' ),
			'self' => __( 'Self-employed', 'impressum-plus' ),
			'ug' => __( 'UG (haftungsbeschränkt)', 'impressum-plus' ),
			'ug_co_kg' => __( 'UG (haftungsbeschränkt) & Co. KG', 'impressum-plus' ),
		];
		
		// make sure the array is always sorted depending on localization
		asort( self::$countries );
		natcasesort( self::$legal_entities );
	}
	
	/**
	 * Get an option or a site option or both with the same name.
	 * The site option is received if there is no option
	 * with the same name, except they should be merged.
	 * 
	 * @param	string		$option The option you want to get
	 * @param	string		$merge Whether site option and option should be merged
	 * @return	mixed|void
	 */
	protected static function impressum_get_option( $option, $merge = false ) {
		if ( ! is_string( $option ) ) return;
		
		// get only local option if there is no multisite
		// or if we are on a multisite in the admin area of a single site
		if (
			( ! is_multisite() && ! is_network_admin() && is_admin() )
			|| ( ! is_multisite() && ! is_admin() )
		) {
			// try receive option
			$options = get_option( $option );
			$options['default'] = get_site_option( $option );
		}
		else {
			// get global elements
			$options_global = get_site_option( $option );
			// get local elements
			$options_local = get_option( $option );
			
			// get both global and local options
			if ( $merge === true && ! empty( $options_local ) ) {
				// remove empty elements
				$options_local = array_filter( $options_local );
				// merge global and local options
				$options = array_merge( $options_global, $options_local );
			}
			else if ( ! is_network_admin() ) {
				$options = $options_local;
				$options['default'] = $options_global;
			}
			else {
				$options = $options_global;
			}
		}
		
		return $options;
	}
}
