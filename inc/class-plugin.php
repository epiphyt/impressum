<?php
declare(strict_types = 1);

namespace epiphyt\Impressum;

use epiphyt\Impressum\settings\Registry;

/**
 * The main plugin class.
 * 
 * @author	Epiphyt
 * @license	GPL2
 * @package	epiphyt\Impressum
 */
final class Plugin {
	/**
	 * @var		array Countries with their country codes in 3-digit ISO form
	 */
	protected array $countries = [];
	
	/**
	 * @var		array All legal entities we support
	 */
	protected array $legal_entities = [];
	
	/**
	 * @var		array All settings fields.
	 */
	public array $settings_fields = [];
	
	/**
	 * @var		?\epiphyt\Impressum\settings\Registry Settings registry
	 */
	public ?\epiphyt\Impressum\settings\Registry $settings_registry = null;
	
	/**
	 * Impressum constructor.
	 * 
	 * @param	\epiphyt\Impressum\settings\Registry	$settings_registry Settings registry
	 */
	public function __construct( Registry $settings_registry ) {
		$this->settings_registry = $settings_registry;
	}
	
	/**
	 * Initialize the class.
	 */
	public function init(): void {
		\add_action( 'init', [ $this, 'load_settings' ], 10 );
		\add_action( 'init', [ $this, 'load_textdomain' ], 5 );
		\add_action( 'pre_update_option_impressum_imprint_options', [ $this, 'activate' ] );
	}
	
	/**
	 * Activate the twice-daily cron.
	 * 
	 * @param	mixed	$value The value on updating option
	 * @return	mixed The (untouched) value on updating option
	 */
	public function activate( mixed $value = [] ): mixed {
		if ( ! \wp_next_scheduled( 'impressum_twice_daily_cron' ) ) {
			\wp_schedule_event( \time(), 'twicedaily', 'impressum_twice_daily_cron' );
		}
		
		return $value;
	}
	
	/**
	 * Deactivate the twice-daily cron.
	 * This should be called only while deactivating the plugin.
	 */
	public function deactivate(): void {
		if ( \wp_next_scheduled( 'impressum_twice_daily_cron' ) ) {
			\wp_clear_scheduled_hook( 'impressum_twice_daily_cron' );
		}
	}
	
	/**
	 * Get all fields from an option with their title.
	 * 
	 * @param	string	$option_name The name of the option
	 * @return	array{array{custom_title: string, field_title: ?string, hide_output: bool, title: string, value: mixed}} The fields
	 */
	public function get_block_fields( string $option_name ): array {
		$fields = [];
		$settings = $this->settings_registry->get_settings( $option_name );
		/** @var \epiphyt\Impressum\settings\Validator $validator */
		$validator = \epiphyt\Impressum\get_container()->get( 'settings-validator' );
		$values = [];
		
		foreach ( $settings as $field ) {
			$values[ $field->name ] = $field->get_value( true );
		}
		
		foreach ( $settings as $field ) {
			$fields[ $field->name ] = [
				'custom_title' => $field->get_title(),
				'field_title' => $field->get_data( 'field_title' ),
				// fields hidden in the settings, e.g. because they don't apply to
				// the current legal entity or country, must not be displayed either
				'hide_output' => $field->get_data( 'hide_output' ) || ! $validator->is_visible( $field, $values ),
				'title' => $field->get_data( 'title' ),
				'value' => $values[ $field->name ],
			];
		}
		
		return $fields;
	}
	
	/**
	 * Get a list of countries.
	 * 
	 * @return	array The country list
	 */
	public function get_countries(): array {
		return $this->countries;
	}
	
	/**
	 * Get a list of legal entities.
	 * 
	 * @return	array The legal entity list
	 */
	public function get_legal_entities(): array {
		return $this->legal_entities;
	}
	
	/**
	 * Load our settings in an array.
	 */
	public function load_settings(): void {
		// fields that only apply to German imprints
		$german_only = [
			'all' => [
				[
					'field' => 'country',
					'operator' => 'in',
					'value' => [ 'de-de', 'deu' ],
				],
			],
		];
		$business_or_vat_id_notice = [
			'id' => 'business-or-vat-id',
			'status' => 'warning',
			'text' => \__( 'Either the business ID or the VAT ID have to be set, if one of them is available.', 'impressum' ),
			'when' => [
				'all' => \array_merge(
					$german_only['all'],
					[
						[
							'field' => 'business_id',
							'operator' => 'empty',
						],
						[
							'field' => 'vat_id',
							'operator' => 'empty',
						],
					]
				),
			],
		];
		$this->settings_fields = [ // phpcs:ignore SlevomatCodingStandard.Arrays.AlphabeticallySortedByKeys.IncorrectKeyOrder
			'page' => [
				'api' => [
					'description' => \esc_html__( 'The imprint page ID.', 'impressum' ),
					'type' => 'integer',
				],
				'args' => [
					'class' => 'impressum_row',
					'label_for' => 'page',
					'setting' => 'impressum_imprint_options',
				],
				'callback' => 'page',
				'no_output' => true,
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'title' => \__( 'Imprint Page', 'impressum' ),
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
					'setting' => 'impressum_imprint_options',
				],
				'callback' => 'country',
				'no_output' => true,
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'title' => \__( 'Country', 'impressum' ),
				'validation' => [
					// the country has never been enforced server side
					'client_only' => true,
					'messages' => [
						'required' => \__( 'You need to select a country.', 'impressum' ),
					],
					'required' => true,
				],
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
					'setting' => 'impressum_imprint_options',
				],
				'callback' => 'legal_entity',
				'notices' => [
					[
						'id' => 'legal-entity-plus',
						'status' => 'warning',
						'text' => \__( 'The Free version doesn’t contain the needed features for your selection. If your legal entity is not “Individual” or “Self-employed”, you need to purchase the Plus version.', 'impressum' ),
						'when' => [
							'all' => [
								[
									'field' => 'legal_entity',
									'operator' => 'not_empty',
								],
								[
									'field' => 'legal_entity',
									'operator' => 'not_in',
									'value' => [ 'individual', 'self' ],
								],
							],
						],
					],
				],
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
					'autocomplete' => 'name',
					'class' => 'impressum_row',
					'label_for' => 'name',
					'required' => true,
					'rows' => 3,
					'setting' => 'impressum_imprint_options',
				],
				'callback' => 'textarea',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'title' => \__( 'Name', 'impressum' ),
				'validation' => [
					'messages' => [
						'required' => \__( 'You need to enter a name.', 'impressum' ),
					],
					'required' => true,
				],
			],
			'address' => [
				'api' => [
					'description' => \esc_html__( 'The address of the responsible person.', 'impressum' ),
					'type' => 'string',
				],
				'args' => [
					'autocomplete' => 'street-address',
					'class' => 'impressum_row',
					'label_for' => 'address',
					'required' => true,
					'rows' => 3,
					'setting' => 'impressum_imprint_options',
				],
				'callback' => 'textarea',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'title' => \__( 'Address', 'impressum' ),
				'validation' => [
					'messages' => [
						'required' => \__( 'You need to enter an address.', 'impressum' ),
					],
					'required' => true,
				],
			],
			'address_alternative' => [
				'api' => [
					'description' => \esc_html__( 'An alternative address.', 'impressum' ),
					'type' => 'string',
				],
				'args' => [
					'autocomplete' => 'street-address',
					'class' => 'impressum_row',
					'label_for' => 'address_alternative',
					'required' => false,
					'rows' => 3,
					'setting' => 'impressum_imprint_options',
				],
				'callback' => 'textarea',
				'field_title' => \__( 'Address', 'impressum' ),
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'title' => \__( 'Alternative Address', 'impressum' ),
			],
			'email' => [
				'api' => [
					'description' => \esc_html__( 'The email address of the responsible person.', 'impressum' ),
					'type' => 'string',
				],
				'args' => [
					'autocomplete' => 'email',
					'class' => 'impressum_row',
					'label_for' => 'email',
					'required' => true,
					'setting' => 'impressum_imprint_options',
				],
				'callback' => 'email',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'title' => \__( 'Email Address', 'impressum' ),
				'validation' => [
					'messages' => [
						'required' => \__( 'You need to enter an email address.', 'impressum' ),
					],
					'required' => true,
				],
			],
			'phone' => [
				'api' => [
					'description' => \esc_html__( 'The phone number of the responsible person.', 'impressum' ),
					'type' => 'string',
				],
				'args' => [
					'autocomplete' => 'tel',
					'class' => 'impressum_row',
					'label_for' => 'phone',
					'required' => false,
					'setting' => 'impressum_imprint_options',
				],
				'callback' => 'phone',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'title' => \__( 'Phone', 'impressum' ),
				'validation' => [
					// server side this pair is reported as a single combined error, not per field
					'client_only' => true,
					'messages' => [
						'required' => \__( 'You need to enter a phone number or a contact form page.', 'impressum' ),
					],
					'pair_key' => 'phone_contact_form',
					/* translators: 1: a field title, 2: a field title */
					'pair_title' => \__( '%1$s or %2$s', 'impressum' ),
					'pair_with' => 'contact_form_page',
					'required_unless' => [
						'any' => [
							[
								'field' => 'contact_form_page',
								'operator' => 'not_empty',
							],
						],
					],
				],
			],
			'contact_form_page' => [
				'api' => [
					'description' => \esc_html__( 'The contact form page ID.', 'impressum' ),
					'type' => 'integer',
				],
				'args' => [
					'class' => 'impressum_row',
					'description' => \__( 'Since you need a fast contact possibility, you either have to publish your phone number or have a contact form where you can respond within 1 hour.', 'impressum' ),
					'label_for' => 'contact_form_page',
					'setting' => 'impressum_imprint_options',
				],
				'callback' => 'page',
				'field_title' => \__( 'Contact', 'impressum' ),
				'option' => 'impressum_imprint_options',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'title' => \__( 'Contact Form Page', 'impressum' ),
				'validation' => [
					// the combined error is declared on the phone field
					'client_only' => true,
					'messages' => [
						'required' => \__( 'You need to enter a phone number or a contact form page.', 'impressum' ),
					],
					'required_unless' => [
						'any' => [
							[
								'field' => 'phone',
								'operator' => 'not_empty',
							],
						],
					],
				],
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
					'setting' => 'impressum_imprint_options',
				],
				'callback' => 'phone',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'title' => \__( 'Fax', 'impressum' ),
			],
			'press_law_checkbox' => [
				'api' => [
					'description' => \esc_html__( 'The checkbox whether a press law person is required.', 'impressum' ),
					'type' => 'boolean',
				],
				'args' => [
					'class' => 'impressum_row impressum_press_law_checkbox',
					'label' => \__( 'I have journalistic/editorial content on my website', 'impressum' ),
					'label_for' => 'press_law_checkbox',
					'required' => false,
					'setting' => 'impressum_imprint_options',
				],
				'callback' => 'checkbox',
				'conditions' => [
					'visible' => $german_only,
				],
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
					'rows' => 6,
					'setting' => 'impressum_imprint_options',
				],
				'callback' => 'textarea',
				'conditions' => [
					'visible' => [
						'all' => [
							[
								'field' => 'country',
								'operator' => 'in',
								'value' => [ 'de-de', 'deu' ],
							],
							[
								'field' => 'press_law_checkbox',
								'operator' => 'truthy',
							],
						],
					],
				],
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
					'description' => \__( 'Your VAT ID in format XX123456789, which means at least two letters by following some numbers (the amount depends on your country).', 'impressum' ),
					'label_for' => 'vat_id',
					'required' => false,
					'setting' => 'impressum_imprint_options',
				],
				'callback' => 'text',
				'notices' => [
					$business_or_vat_id_notice,
				],
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'title' => \__( 'VAT ID', 'impressum' ),
				'validation' => [
					'messages' => [
						'pattern' => \__( 'The entered value is not valid. Please use a valid format for your VAT ID.', 'impressum' ),
					],
					'pattern' => '^(|ATU[0-9]{8}|BE0[0-9]{9}|BG[0-9]{9,10}|CY[0-9]{8}L|CZ[0-9]{8,10}|DE[0-9]{9}|DK[0-9]{8}|EE[0-9]{9}|(EL|GR)[0-9]{9}|ES[0-9A-Z][0-9]{7}[0-9A-Z]|FI[0-9]{8}|FR[0-9A-Z]{2}[0-9]{9}|GB([0-9]{9}([0-9]{3})?|[A-Z]{2}[0-9]{3})|HU[0-9]{8}|IE[0-9]S[0-9]{5}L|IT[0-9]{11}|LT([0-9]{9}|[0-9]{12})|LU[0-9]{8}|LV[0-9]{11}|MT[0-9]{8}|NL[0-9+*]{9}B[0-9]{2}|PL[0-9]{10}|PT[0-9]{9}|RO[0-9]{2,10}|SE[0-9]{12}|SI[0-9]{8}|SK[0-9]{10})$',
					'sanitize_pattern' => '[^A-Za-z0-9*+]+',
				],
			],
			'business_id' => [
				'api' => [
					'description' => \esc_html__( 'The business ID of the responsible person.', 'impressum' ),
					'type' => 'string',
				],
				'args' => [
					'class' => 'impressum_row impressum_business_id',
					'description' => \__( 'Your business ID in format DE123456789-00001, which means two letters by following nine numbers, a dash and five additional numbers.', 'impressum' ),
					'label_for' => 'business_id',
					'required' => false,
					'setting' => 'impressum_imprint_options',
				],
				'callback' => 'text',
				'conditions' => [
					'visible' => $german_only,
				],
				'notices' => [
					$business_or_vat_id_notice,
				],
				'option' => 'impressum_imprint_options',
				'page' => 'impressum_imprint',
				'section' => 'impressum_section_imprint',
				'title' => \__( 'Business ID', 'impressum' ),
				'validation' => [
					'messages' => [
						'pattern' => \__( 'The entered value is not valid. Please use a valid format for your business ID.', 'impressum' ),
					],
					'pattern' => '^(|DE[0-9]{9}-[0-9]{5})$',
					'sanitize_pattern' => '[^A-Za-z0-9*+\-]+',
				],
			],
		];
		
		/**
		 * Filter the settings fields of Impressum.
		 * 
		 * @param	array	$settings_fields The current settings fields
		 */
		$this->settings_fields = \apply_filters( 'impressum_settings_fields', $this->settings_fields );
		
		$this->settings_registry->register_multiple( $this->settings_fields );
	}
	
	/**
	 * Load translations.
	 */
	public function load_textdomain(): void {
		// set the value of countries
		$this->countries = [
			'arg' => [
				'locale' => 'es-ar',
				'title' => \__( 'Argentina', 'impressum' ),
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
			'cyp' => [
				'locale' => 'el',
				'title' => \__( 'Cyprus', 'impressum' ),
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
			'lux' => [
				'locale' => 'lb',
				'title' => \__( 'Luxembourg', 'impressum' ),
			],
			'lva' => [
				'locale' => 'lv',
				'title' => \__( 'Latvia', 'impressum' ),
			],
			'mlt' => [
				'locale' => 'mt',
				'title' => \__( 'Malta', 'impressum' ),
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
			'svk' => [
				'locale' => 'sk',
				'title' => \__( 'Slovakia', 'impressum' ),
			],
			'svn' => [
				'locale' => 'sl',
				'title' => \__( 'Slovenia', 'impressum' ),
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
		 * @param	string[][]	$countries The current countries
		 */
		$this->countries = (array) \apply_filters( 'impressum_country_pre_sort', $this->countries );
		
		$this->legal_entities = [
			'ag' => \__( 'AG', 'impressum' ),
			'eg' => \__( 'eG', 'impressum' ),
			'einzelkaufmann' => \__( 'Einzelkaufmann', 'impressum' ),
			'ek' => \__( 'e.K.', 'impressum' ),
			'ev' => \__( 'e.V.', 'impressum' ),
			'freelancer' => \__( 'Freelancer', 'impressum' ),
			'freelancer-medical' => \__( 'Freelancer (medical)', 'impressum' ),
			'gbr' => \__( 'GbR', 'impressum' ),
			'gesbr' => \__( 'GesbR', 'impressum' ),
			'ggmbh' => \__( 'gGmbH', 'impressum' ),
			'gmbh' => \__( 'GmbH', 'impressum' ),
			'gmbh_co_kg' => \__( 'GmbH & Co. KG', 'impressum' ),
			'individual' => \__( 'Individual', 'impressum' ),
			'kdoer' => \__( 'KdöR', 'impressum' ),
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
		 * @param	string[]	$countries The current countries
		 */
		$this->legal_entities = (array) \apply_filters( 'impressum_legal_entity_pre_sort', $this->legal_entities );
		
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
}
