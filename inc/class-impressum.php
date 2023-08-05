<?php
namespace epiphyt\Impressum;
use function array_keys;
use function esc_html__;
use function file_exists;
use function natcasesort;
use function register_activation_hook;
use function register_deactivation_hook;
use function strcasecmp;
use function time;
use function uasort;
use function wp_clear_scheduled_hook;
use function wp_next_scheduled;
use function wp_schedule_event;

/**
 * The main Impressum class.
 * 
 * @author		Epiphyt
 * @license		GPL2 <https://www.gnu.org/licenses/gpl-2.0.html>
 */
class Impressum {
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
	 * @var		\epiphyt\Impressum\Impressum
	 */
	private static $instance;
	
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
		self::$instance = $this;
		$this->admin = Admin::get_instance();
		$this->frontend = Frontend::get_instance();
	}
	
	/**
	 * Initialize the class.
	 */
	public function init() {
		// actions
		add_action( 'plugins_loaded', [ $this, 'load_settings' ] );
		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ], 5 );
		
		// cron jobs
		add_action( 'pre_update_option_impressum_imprint_options', [ $this, 'twice_daily_cron_activation' ] );
		
		register_activation_hook( $this->plugin_file, [ $this, 'twice_daily_cron_activation' ] );
		register_deactivation_hook( $this->plugin_file, [ $this, 'twice_daily_cron_deactivation' ] );
		
		// initialize classes
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
	 * Get a unique instance of the class.
	 * 
	 * @return	\epiphyt\Impressum\Impressum
	 */
	public static function get_instance() {
		if ( static::$instance === null ) {
			static::$instance = new static();
		}
		
		return static::$instance;
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
			'page' => [
				'title' => __( 'Imprint Page', 'impressum' ),
				'callback' => 'page',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'page',
					'class' => 'impressum_row',
				],
				'no_output' => true,
			],
			'country' => [
				'title' => __( 'Country', 'impressum' ),
				'callback' => 'country',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'country',
					'class' => 'impressum_row',
					'required' => true,
				],
				'api' => [
					'description' => esc_html__( 'The country of the legal entity according to ISO 639-2.', 'impressum' ),
					'enum' => array_keys( $this->countries ),
					'type' => 'string',
				],
				'no_output' => true,
			],
			'legal_entity' => [
				'title' => __( 'Legal Entity', 'impressum' ),
				'callback' => 'legal_entity',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'legal_entity',
					'class' => 'impressum_row',
					'required' => true,
				],
				'api' => [
					'description' => esc_html__( 'The legal entity.', 'impressum' ),
					'enum' => array_keys( $this->legal_entities ),
					'type' => 'string',
				],
				'no_output' => true,
			],
			'name' => [
				'title' => __( 'Name', 'impressum' ),
				'callback' => 'text',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'name',
					'class' => 'impressum_row',
					'required' => true,
				],
				'api' => [
					'description' => esc_html__( 'The name of the responsible person.', 'impressum' ),
					'type' => 'string',
				],
			],
			'address' => [
				'title' => __( 'Address', 'impressum' ),
				'callback' => 'textarea',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'address',
					'class' => 'impressum_row',
					'required' => true,
				],
				'api' => [
					'description' => esc_html__( 'The address of the responsible person.', 'impressum' ),
					'type' => 'string',
				],
			],
			'address_alternative' => [
				'title' => __( 'Alternative Address', 'impressum' ),
				'field_title' => __( 'Address', 'impressum' ),
				'callback' => 'textarea',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'address_alternative',
					'class' => 'impressum_row',
					'required' => false,
				],
				'api' => [
					'description' => esc_html__( 'An alternative address.', 'impressum' ),
					'type' => 'string',
				],
			],
			'email' => [
				'title' => __( 'Email Address', 'impressum' ),
				'callback' => 'email',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'email',
					'class' => 'impressum_row',
					'required' => true,
				],
				'api' => [
					'description' => esc_html__( 'The email address of the responsible person.', 'impressum' ),
					'type' => 'string',
				],
			],
			'phone' => [
				'title' => __( 'Telephone', 'impressum' ),
				'callback' => 'phone',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'phone',
					'class' => 'impressum_row',
					'required' => true,
				],
				'api' => [
					'description' => esc_html__( 'The phone number of the responsible person.', 'impressum' ),
					'type' => 'string',
				],
			],
			'fax' => [
				'title' => __( 'Fax', 'impressum' ),
				'callback' => 'phone',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'fax',
					'class' => 'impressum_row',
					'required' => false,
				],
				'api' => [
					'description' => esc_html__( 'The fax number of the responsible person.', 'impressum' ),
					'type' => 'string',
				],
			],
			'press_law_checkbox' => [
				'title' => __( 'Journalistic/Editorial Content', 'impressum' ),
				'callback' => 'press_law_checkbox',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'press_law_checkbox',
					'class' => 'impressum_row impressum_press_law_checkbox',
					'required' => false,
				],
				'api' => [
					'description' => esc_html__( 'The checkbox whether a press law person is required.', 'impressum' ),
					'type' => 'boolean',
				],
				'no_output' => true,
			],
			'press_law_person' => [
				'title' => __( 'Responsible for content', 'impressum' ),
				'callback' => 'textarea',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'press_law_person',
					'class' => 'impressum_row impressum_press_law',
					'required' => false,
				],
				'api' => [
					'description' => esc_html__( 'The responsible press law person.', 'impressum' ),
					'type' => 'string',
				],
			],
			'vat_id' => [
				'title' => __( 'VAT ID', 'impressum' ),
				'callback' => 'text',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'args' => [
					'label_for' => 'vat_id',
					'class' => 'impressum_row vat_id',
					'required' => false,
				],
				'api' => [
					'description' => esc_html__( 'The VAT ID of the responsible person.', 'impressum' ),
					'type' => 'string',
				],
			],
		];
		
		/**
		 * Filter the settings fields of Impressum.
		 * 
		 * @param	array	$settings_fields The current settings fields
		 */
		$this->settings_fields = apply_filters( 'impressum_settings_fields', $this->settings_fields );
	}
	
	/**
	 * Load translations.
	 */
	public function load_textdomain() {
		// set the value of countries
		$this->countries = [
			'dza' => [
				'locale' => 'ar-dz',
				'title' => __( 'Algeria', 'impressum' ),
			],
			'arg' => [
				'locale' => 'es-ar',
				'title' => __( 'Argentinia', 'impressum' ),
			],
			'aus' => [
				'locale' => 'en-au',
				'title' => __( 'Australia', 'impressum' ),
			],
			'aut' => [
				'locale' => 'de-at',
				'title' => __( 'Austria', 'impressum' ),
			],
			'bel' => [
				'locale' => 'fr-be',
				'title' => __( 'Belgium', 'impressum' ),
			],
			'bra' => [
				'locale' => 'pt-br',
				'title' => __( 'Brazil', 'impressum' ),
			],
			'bgr' => [
				'locale' => 'bg',
				'title' => __( 'Bulgaria', 'impressum' ),
			],
			'can' => [
				'locale' => 'en-ca',
				'title' => __( 'Canada', 'impressum' ),
			],
			'chl' => [
				'locale' => 'es-cl',
				'title' => __( 'Chile', 'impressum' ),
			],
			'chn' => [
				'locale' => 'zh',
				'title' => __( 'China', 'impressum' ),
			],
			'col' => [
				'locale' => 'es-co',
				'title' => __( 'Columbia', 'impressum' ),
			],
			'hrv' => [
				'locale' => 'hr',
				'title' => __( 'Croatia', 'impressum' ),
			],
			'cze' => [
				'locale' => 'cs',
				'title' => __( 'Czech Republic', 'impressum' ),
			],
			'dnk' => [
				'locale' => 'da',
				'title' => __( 'Denmark', 'impressum' ),
			],
			'est' => [
				'locale' => 'et',
				'title' => __( 'Estonia', 'impressum' ),
			],
			'fin' => [
				'locale' => 'fi',
				'title' => __( 'Finland', 'impressum' ),
			],
			'fra' => [
				'locale' => 'fr-fr',
				'locale_primary' => 'fr',
				'title' => __( 'France', 'impressum' ),
			],
			'deu' => [
				'locale' => 'de-de',
				'locale_primary' => 'de',
				'title' => __( 'Germany', 'impressum' ),
			],
			'grc' => [
				'locale' => 'gr',
				'title' => __( 'Greece', 'impressum' ),
			],
			'hkg' => [
				'locale' => 'zh-hans-hk',
				'title' => __( 'Hong Kong', 'impressum' ),
			],
			'hun' => [
				'locale' => 'hu',
				'title' => __( 'Hungary', 'impressum' ),
			],
			'idn' => [
				'locale' => 'id',
				'title' => __( 'Indonesia', 'impressum' ),
			],
			'irl' => [
				'locale' => 'en-ie',
				'title' => __( 'Ireland', 'impressum' ),
			],
			'isr' => [
				'locale' => 'ar-il',
				'title' => __( 'Israel', 'impressum' ),
			],
			'ita' => [
				'locale' => 'it',
				'title' => __( 'Italy', 'impressum' ),
			],
			'jpn' => [
				'locale' => 'ja',
				'title' => __( 'Japan', 'impressum' ),
			],
			'ltu' => [
				'locale' => 'lt',
				'title' => __( 'Lithuania', 'impressum' ),
			],
			'nld' => [
				'locale' => 'nl',
				'title' => __( 'Netherlands', 'impressum' ),
			],
			'nzl' => [
				'locale' => 'en-nz',
				'title' => __( 'New Zealand', 'impressum' ),
			],
			'nor' => [
				'locale' => 'nn',
				'locale_primary' => 'nb',
				'title' => __( 'Norway', 'impressum' ),
			],
			'pol' => [
				'locale' => 'pl',
				'title' => __( 'Poland', 'impressum' ),
			],
			'prt' => [
				'locale' => 'pt-pt',
				'locale_primary' => 'pt',
				'title' => __( 'Portugal', 'impressum' ),
			],
			'rou' => [
				'locale' => 'ro',
				'title' => __( 'Romania', 'impressum' ),
			],
			'rus' => [
				'locale' => 'ru',
				'title' => __( 'Russia', 'impressum' ),
			],
			'srb' => [
				'locale' => 'sr',
				'title' => __( 'Serbia', 'impressum' ),
			],
			'svn' => [
				'locale' => 'sl',
				'title' => __( 'Slowenia', 'impressum' ),
			],
			'zaf' => [
				'locale' => 'en-za',
				'title' => __( 'South Africa', 'impressum' ),
			],
			'kor' => [
				'locale' => 'ko-kr',
				'locale_primary' => 'ko',
				'title' => __( 'South Korea', 'impressum' ),
			],
			'esp' => [
				'locale' => 'es',
				'locale_primary' => 'es',
				'title' => __( 'Spain', 'impressum' ),
			],
			'swe' => [
				'locale' => 'sv',
				'title' => __( 'Sweden', 'impressum' ),
			],
			'che' => [
				'locale' => 'de-ch',
				'title' => __( 'Switzerland', 'impressum' ),
			],
			'twn' => [
				'locale' => 'zh-hant-tw',
				'title' => __( 'Taiwan', 'impressum' ),
			],
			'tha' => [
				'locale' => 'th',
				'title' => __( 'Thailand', 'impressum' ),
			],
			'tur' => [
				'locale' => 'tr',
				'title' => __( 'Turkey', 'impressum' ),
			],
			'gbr' => [
				'locale' => 'en-gb',
				'title' => __( 'United Kingdom', 'impressum' ),
			],
			'usa' => [
				'locale' => 'en-us',
				'locale_primary' => 'en',
				'title' => __( 'United States', 'impressum' ),
			],
			'ven' => [
				'locale' => 'es-ve',
				'title' => __( 'Venezuela', 'impressum' ),
			],
			'vnm' => [
				'locale' => 'vi',
				'title' => __( 'Vietnam', 'impressum' ),
			],
			'other' => [
				'locale' => 'none',
				'title' => __( 'other', 'impressum' ),
			],
		];
		
		/**
		 * Filter the countries before localized alphabetical sorting.
		 * 
		 * @param	array	$countries The current countries
		 */
		$this->countries = apply_filters( 'impressum_country_pre_sort', $this->countries );
		
		$this->legal_entities = [
			'ag' => __( 'AG', 'impressum' ),
			'eg' => __( 'eG', 'impressum' ),
			'ek' => __( 'e.K.', 'impressum' ),
			'ev' => __( 'e.V.', 'impressum' ),
			'einzelkaufmann' => __( 'Einzelkaufmann', 'impressum' ),
			'freelancer' => __( 'Freelancer', 'impressum' ),
			'ggmbh' => __( 'gGmbH', 'impressum' ),
			'gmbh' => __( 'GmbH', 'impressum' ),
			'gbr' => __( 'GbR', 'impressum' ),
			'gmbh_co_kg' => __( 'GmbH & Co. KG', 'impressum' ),
			'kg' => __( 'KG', 'impressum' ),
			'kgag' => __( 'KGaA', 'impressum' ),
			'individual' => __( 'Individual', 'impressum' ),
			'ohg' => __( 'OHG', 'impressum' ),
			'partnership' => __( 'Partnership', 'impressum' ),
			'self' => __( 'Self-employed', 'impressum' ),
			'ug' => __( 'UG (haftungsbeschränkt)', 'impressum' ),
			'ug_co_kg' => __( 'UG (haftungsbeschränkt) & Co. KG', 'impressum' ),
		];
		
		/**
		 * Filter the legal entities before localized alphabetical sorting.
		 * 
		 * @param	array	$countries The current countries
		 */
		$this->legal_entities = apply_filters( 'impressum_legal_entity_pre_sort', $this->legal_entities );
		
		// make sure the array is always sorted depending on localization
		uasort( $this->countries, function( $a, $b ) {
			// always sort 'other' country as last element
			if ( $a['locale'] === 'none' ) {
				return 1;
			}
			else if ( $b['locale'] === 'none' ) {
				return -1;
			}
			
			return strcasecmp( $a['title'], $b['title'] );
		} );
		natcasesort( $this->legal_entities );
		
		/**
		 * Filter the countries after localized alphabetical sorting.
		 * 
		 * @param	array	$countries The current countries
		 */
		$this->countries = apply_filters( 'impressum_country_after_sort', $this->countries );
		
		/**
		 * Filter the legal entities after localized alphabetical sorting.
		 * 
		 * @param	array	$countries The current countries
		 */
		$this->legal_entities = apply_filters( 'impressum_legal_entity_after_sort', $this->legal_entities );
	}
	
	/**
	 * Set the plugin file.
	 * 
	 * @param	string	$file The path to the file
	 */
	public function set_plugin_file( $file ) {
		if ( file_exists( $file ) ) {
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
		if ( ! wp_next_scheduled( 'impressum_twice_daily_cron' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'impressum_twice_daily_cron' );
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
		if ( wp_next_scheduled( 'impressum_twice_daily_cron' ) ) {
			wp_clear_scheduled_hook( 'impressum_twice_daily_cron' );
		}
	}
}
