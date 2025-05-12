<?php
namespace epiphyt\Impressum;

/**
 * The main Impressum class.
 * 
 * @author		Epiphyt
 * @license		GPL2 <https://www.gnu.org/licenses/gpl-2.0.html>
 */
class Impressum {
	use Singleton;
	
	/**
	 * @var		\epiphyt\Impressum\Admin
	 */
	public $admin;
	
	/**
	 * @var		array Countries with their country codes in 3-digit ISO form
	 */
	protected $countries = [];
	
	/**
	 * @var		\epiphyt\Impressum\Frontend
	 */
	public $frontend;
	
	/**
	 * @var		array All legal entities we support
	 */
	protected $legal_entities = [];
	
	/**
	 * @var		string The full path to the main plugin file
	 */
	public $plugin_file = '';
	
	/**
	 * @var		array All settings fields.
	 */
	public $settings_fields = [];
	
	/**
	 * Impressum constructor.
	 */
	public function __construct() {
		// assign variables
		$this->admin = Admin::get_instance();
		$this->frontend = Frontend::get_instance();
	}
	
	/**
	 * Initialize the class.
	 */
	public function init() {
		\add_action( 'init', [ $this, 'load_settings' ] );
		\add_action( 'init', [ $this, 'load_textdomain' ], 5 );
		\add_action( 'pre_update_option_impressum_imprint_options', [ $this, 'twice_daily_cron_activation' ] );
		\register_activation_hook( $this->plugin_file, [ $this, 'twice_daily_cron_activation' ] );
		\register_deactivation_hook( $this->plugin_file, [ $this, 'twice_daily_cron_deactivation' ] );
		
		$this->admin->init();
		$this->frontend->init();
	}
	
	/**
	 * Get all fields from an option with their title.
	 * 
	 * @param	string	$option_name The name of the option
	 * @return	array The fields
	 */
	public function get_block_fields( $option_name ) {
		$fields = [];
		$option = Helper::get_option( $option_name, true );
		
		foreach ( $this->settings_fields as $name => $field ) {
			$fields[ $name ] = [
				'field_title' => ( ! empty( $field['field_title'] ) ? $field['field_title'] : '' ),
				'title' => $field['title'],
				'value' => ( ! empty( $option[ $name ] ) ? $option[ $name ] : '' ),
			];
		}
		
		return $fields;
	}
	
	/**
	 * Get a list of countries.
	 * 
	 * @return	array The country list
	 */
	public function get_countries() {
		return $this->countries;
	}
	
	/**
	 * Get a list of legal entities.
	 * 
	 * @return	array The legal entity list
	 */
	public function get_legal_entities() {
		return $this->legal_entities;
	}
	
	/**
	 * Load our settings in an array.
	 */
	public function load_settings() {
		$this->settings_fields = [
			'address' => [
				'api' => [
					'description' => \esc_html__( 'The address of the responsible person.', 'impressum' ),
					'type' => 'string',
				],
				'args' => [
					'class' => 'impressum_row',
					'label_for' => 'address',
					'required' => true,
				],
				'callback' => 'textarea',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'title' => \__( 'Address', 'impressum' ),
			],
			'address_alternative' => [
				'api' => [
					'description' => \esc_html__( 'An alternative address.', 'impressum' ),
					'type' => 'string',
				],
				'args' => [
					'class' => 'impressum_row',
					'label_for' => 'address_alternative',
					'required' => false,
				],
				'callback' => 'textarea',
				'field_title' => \__( 'Address', 'impressum' ),
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'title' => \__( 'Alternative Address', 'impressum' ),
			],
			'country' => [
				'api' => [
					'description' => \esc_html__( 'The country of the legal entity according to ISO 639-2.', 'impressum' ),
					'enum' => \array_keys( $this->countries ),
					'type' => 'string',
				],
				'args' => [
					'class' => 'impressum_row',
					'label_for' => 'country',
					'required' => true,
				],
				'callback' => 'country',
				'no_output' => true,
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'title' => \__( 'Country', 'impressum' ),
			],
			'email' => [
				'api' => [
					'description' => \esc_html__( 'The email address of the responsible person.', 'impressum' ),
					'type' => 'string',
				],
				'args' => [
					'class' => 'impressum_row',
					'label_for' => 'email',
					'required' => true,
				],
				'callback' => 'email',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'title' => \__( 'Email Address', 'impressum' ),
			],
			'fax' => [
				'api' => [
					'description' => \esc_html__( 'The fax number of the responsible person.', 'impressum' ),
					'type' => 'string',
				],
				'args' => [
					'class' => 'impressum_row',
					'label_for' => 'fax',
					'required' => false,
				],
				'callback' => 'phone',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'title' => \__( 'Fax', 'impressum' ),
			],
			'legal_entity' => [
				'api' => [
					'description' => \esc_html__( 'The legal entity.', 'impressum' ),
					'enum' => \array_keys( $this->legal_entities ),
					'type' => 'string',
				],
				'args' => [
					'class' => 'impressum_row',
					'label_for' => 'legal_entity',
					'required' => true,
				],
				'callback' => 'legal_entity',
				'no_output' => true,
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'title' => \__( 'Legal Entity', 'impressum' ),
			],
			'name' => [
				'api' => [
					'description' => \esc_html__( 'The name of the responsible person.', 'impressum' ),
					'type' => 'string',
				],
				'args' => [
					'class' => 'impressum_row',
					'label_for' => 'name',
					'required' => true,
				],
				'callback' => 'text',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'title' => \__( 'Name', 'impressum' ),
			],
			'page' => [
				'args' => [
					'class' => 'impressum_row',
					'label_for' => 'page',
				],
				'callback' => 'page',
				'no_output' => true,
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'title' => \__( 'Imprint Page', 'impressum' ),
			],
			'phone' => [
				'api' => [
					'description' => \esc_html__( 'The phone number of the responsible person.', 'impressum' ),
					'type' => 'string',
				],
				'args' => [
					'class' => 'impressum_row',
					'label_for' => 'phone',
					'required' => true,
				],
				'callback' => 'phone',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'title' => \__( 'Telephone', 'impressum' ),
			],
			'press_law_checkbox' => [
				'api' => [
					'description' => \esc_html__( 'The checkbox whether a press law person is required.', 'impressum' ),
					'type' => 'boolean',
				],
				'args' => [
					'class' => 'impressum_row impressum_press_law_checkbox',
					'label_for' => 'press_law_checkbox',
					'required' => false,
				],
				'callback' => 'press_law_checkbox',
				'no_output' => true,
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'title' => \__( 'Journalistic/Editorial Content', 'impressum' ),
			],
			'press_law_person' => [
				'api' => [
					'description' => \esc_html__( 'The responsible press law person.', 'impressum' ),
					'type' => 'string',
				],
				'args' => [
					'class' => 'impressum_row impressum_press_law',
					'label_for' => 'press_law_person',
					'required' => false,
				],
				'callback' => 'textarea',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'title' => \__( 'Responsible for content', 'impressum' ),
			],
			'vat_id' => [
				'api' => [
					'description' => \esc_html__( 'The VAT ID of the responsible person.', 'impressum' ),
					'type' => 'string',
				],
				'args' => [
					'class' => 'impressum_row vat_id',
					'label_for' => 'vat_id',
					'required' => false,
				],
				'callback' => 'text',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'title' => \__( 'VAT ID', 'impressum' ),
			],
		];
		
		/**
		 * Filter the settings fields of Impressum.
		 * 
		 * @param	array	$settings_fields The current settings fields
		 */
		$this->settings_fields = \apply_filters( 'impressum_settings_fields', $this->settings_fields );
	}
	
	/**
	 * Load translations.
	 */
	public function load_textdomain() {
		// set the value of countries
		$this->countries = [
			'arg' => [
				'locale' => 'es-ar',
				'title' => \__( 'Argentinia', 'impressum' ),
			],
			'aus' => [
				'locale' => 'en-au',
				'title' => \__( 'Australia', 'impressum' ),
			],
			'aut' => [
				'locale' => 'de-at',
				'title' => \__( 'Austria', 'impressum' ),
			],
			'bel' => [
				'locale' => 'fr-be',
				'title' => \__( 'Belgium', 'impressum' ),
			],
			'bgr' => [
				'locale' => 'bg',
				'title' => \__( 'Bulgaria', 'impressum' ),
			],
			'bra' => [
				'locale' => 'pt-br',
				'title' => \__( 'Brazil', 'impressum' ),
			],
			'can' => [
				'locale' => 'en-ca',
				'title' => \__( 'Canada', 'impressum' ),
			],
			'che' => [
				'locale' => 'de-ch',
				'title' => \__( 'Switzerland', 'impressum' ),
			],
			'chl' => [
				'locale' => 'es-cl',
				'title' => \__( 'Chile', 'impressum' ),
			],
			'chn' => [
				'locale' => 'zh',
				'title' => \__( 'China', 'impressum' ),
			],
			'col' => [
				'locale' => 'es-co',
				'title' => \__( 'Columbia', 'impressum' ),
			],
			'cze' => [
				'locale' => 'cs',
				'title' => \__( 'Czech Republic', 'impressum' ),
			],
			'deu' => [
				'locale' => 'de-de',
				'locale_primary' => 'de',
				'title' => \__( 'Germany', 'impressum' ),
			],
			'dnk' => [
				'locale' => 'da',
				'title' => \__( 'Denmark', 'impressum' ),
			],
			'dza' => [
				'locale' => 'ar-dz',
				'title' => \__( 'Algeria', 'impressum' ),
			],
			'esp' => [
				'locale' => 'es',
				'locale_primary' => 'es',
				'title' => \__( 'Spain', 'impressum' ),
			],
			'est' => [
				'locale' => 'et',
				'title' => \__( 'Estonia', 'impressum' ),
			],
			'fin' => [
				'locale' => 'fi',
				'title' => \__( 'Finland', 'impressum' ),
			],
			'fra' => [
				'locale' => 'fr-fr',
				'locale_primary' => 'fr',
				'title' => \__( 'France', 'impressum' ),
			],
			'gbr' => [
				'locale' => 'en-gb',
				'title' => \__( 'United Kingdom', 'impressum' ),
			],
			'grc' => [
				'locale' => 'gr',
				'title' => \__( 'Greece', 'impressum' ),
			],
			'hkg' => [
				'locale' => 'zh-hans-hk',
				'title' => \__( 'Hong Kong', 'impressum' ),
			],
			'hrv' => [
				'locale' => 'hr',
				'title' => \__( 'Croatia', 'impressum' ),
			],
			'hun' => [
				'locale' => 'hu',
				'title' => \__( 'Hungary', 'impressum' ),
			],
			'idn' => [
				'locale' => 'id',
				'title' => \__( 'Indonesia', 'impressum' ),
			],
			'irl' => [
				'locale' => 'en-ie',
				'title' => \__( 'Ireland', 'impressum' ),
			],
			'isr' => [
				'locale' => 'ar-il',
				'title' => \__( 'Israel', 'impressum' ),
			],
			'ita' => [
				'locale' => 'it',
				'title' => \__( 'Italy', 'impressum' ),
			],
			'jpn' => [
				'locale' => 'ja',
				'title' => \__( 'Japan', 'impressum' ),
			],
			'kor' => [
				'locale' => 'ko-kr',
				'locale_primary' => 'ko',
				'title' => \__( 'South Korea', 'impressum' ),
			],
			'ltu' => [
				'locale' => 'lt',
				'title' => \__( 'Lithuania', 'impressum' ),
			],
			'nld' => [
				'locale' => 'nl',
				'title' => \__( 'Netherlands', 'impressum' ),
			],
			'nor' => [
				'locale' => 'nn',
				'locale_primary' => 'nb',
				'title' => \__( 'Norway', 'impressum' ),
			],
			'nzl' => [
				'locale' => 'en-nz',
				'title' => \__( 'New Zealand', 'impressum' ),
			],
			'other' => [
				'locale' => 'none',
				'title' => \__( 'other', 'impressum' ),
			],
			'pol' => [
				'locale' => 'pl',
				'title' => \__( 'Poland', 'impressum' ),
			],
			'prt' => [
				'locale' => 'pt-pt',
				'locale_primary' => 'pt',
				'title' => \__( 'Portugal', 'impressum' ),
			],
			'rou' => [
				'locale' => 'ro',
				'title' => \__( 'Romania', 'impressum' ),
			],
			'rus' => [
				'locale' => 'ru',
				'title' => \__( 'Russia', 'impressum' ),
			],
			'srb' => [
				'locale' => 'sr',
				'title' => \__( 'Serbia', 'impressum' ),
			],
			'svn' => [
				'locale' => 'sl',
				'title' => \__( 'Slowenia', 'impressum' ),
			],
			'swe' => [
				'locale' => 'sv',
				'title' => \__( 'Sweden', 'impressum' ),
			],
			'tha' => [
				'locale' => 'th',
				'title' => \__( 'Thailand', 'impressum' ),
			],
			'tur' => [
				'locale' => 'tr',
				'title' => \__( 'Turkey', 'impressum' ),
			],
			'twn' => [
				'locale' => 'zh-hant-tw',
				'title' => \__( 'Taiwan', 'impressum' ),
			],
			'usa' => [
				'locale' => 'en-us',
				'locale_primary' => 'en',
				'title' => \__( 'United States', 'impressum' ),
			],
			'ven' => [
				'locale' => 'es-ve',
				'title' => \__( 'Venezuela', 'impressum' ),
			],
			'vnm' => [
				'locale' => 'vi',
				'title' => \__( 'Vietnam', 'impressum' ),
			],
			'zaf' => [
				'locale' => 'en-za',
				'title' => \__( 'South Africa', 'impressum' ),
			],
		];
		
		/**
		 * Filter the countries before localized alphabetical sorting.
		 * 
		 * @param	array	$countries The current countries
		 */
		$this->countries = \apply_filters( 'impressum_country_pre_sort', $this->countries );
		
		$this->legal_entities = [
			'ag' => \__( 'AG', 'impressum' ),
			'eg' => \__( 'eG', 'impressum' ),
			'einzelkaufmann' => \__( 'Einzelkaufmann', 'impressum' ),
			'ek' => \__( 'e.K.', 'impressum' ),
			'ev' => \__( 'e.V.', 'impressum' ),
			'freelancer' => \__( 'Freelancer', 'impressum' ),
			'gbr' => \__( 'GbR', 'impressum' ),
			'ggmbh' => \__( 'gGmbH', 'impressum' ),
			'gmbh' => \__( 'GmbH', 'impressum' ),
			'gmbh_co_kg' => \__( 'GmbH & Co. KG', 'impressum' ),
			'individual' => \__( 'Individual', 'impressum' ),
			'kg' => \__( 'KG', 'impressum' ),
			'kgag' => \__( 'KGaA', 'impressum' ),
			'ohg' => \__( 'OHG', 'impressum' ),
			'partnership' => \__( 'Partnership', 'impressum' ),
			'self' => \__( 'Self-employed', 'impressum' ),
			'ug' => \__( 'UG (haftungsbeschränkt)', 'impressum' ),
			'ug_co_kg' => \__( 'UG (haftungsbeschränkt) & Co. KG', 'impressum' ),
		];
		
		/**
		 * Filter the legal entities before localized alphabetical sorting.
		 * 
		 * @param	array	$countries The current countries
		 */
		$this->legal_entities = \apply_filters( 'impressum_legal_entity_pre_sort', $this->legal_entities );
		
		// make sure the array is always sorted depending on localization
		\uasort( $this->countries, static function( $a, $b ) {
			// always sort 'other' country as last element
			if ( $a['locale'] === 'none' ) {
				return 1;
			}
			else if ( $b['locale'] === 'none' ) {
				return -1;
			}
			
			return \strcasecmp( $a['title'], $b['title'] );
		} );
		\natcasesort( $this->legal_entities );
		
		/**
		 * Filter the countries after localized alphabetical sorting.
		 * 
		 * @param	array	$countries The current countries
		 */
		$this->countries = \apply_filters( 'impressum_country_after_sort', $this->countries );
		
		/**
		 * Filter the legal entities after localized alphabetical sorting.
		 * 
		 * @param	array	$countries The current countries
		 */
		$this->legal_entities = \apply_filters( 'impressum_legal_entity_after_sort', $this->legal_entities );
	}
	
	/**
	 * Set the plugin file.
	 * 
	 * @deprecated	2.1.0 Use \EPI_IMPRESSUM_FILE instead
	 * 
	 * @param	string	$file The path to the file
	 */
	public function set_plugin_file( $file ) {
		if ( \file_exists( $file ) ) {
			$this->plugin_file = $file;
			$this->admin->set_plugin_file( $this->plugin_file );
		}
	}
	
	/**
	 * Activate the twice-daily cron.
	 * 
	 * @param	array	$value The value on updating option
	 * @return	array The (untouched) value on updating option
	 */
	public function twice_daily_cron_activation( $value = [] ) {
		if ( ! \wp_next_scheduled( 'impressum_twice_daily_cron' ) ) {
			\wp_schedule_event( \time(), 'twicedaily', 'impressum_twice_daily_cron' );
		}
		
		// if running before updating option, this represents the value
		// just pass it
		if ( $value ) {
			return $value;
		}
		
		return $value;
	}
	
	/**
	 * Deactivate the twice-daily cron.
	 * This should be called only while deactivating the plugin.
	 */
	public function twice_daily_cron_deactivation() {
		if ( \wp_next_scheduled( 'impressum_twice_daily_cron' ) ) {
			\wp_clear_scheduled_hook( 'impressum_twice_daily_cron' );
		}
	}
}
