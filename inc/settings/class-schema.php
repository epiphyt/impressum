<?php
declare(strict_types = 1);

namespace epiphyt\Impressum\settings;

use epiphyt\Impressum\Helper;

/**
 * Serialization of the settings registry for the settings app.
 * 
 * This is a view model over the registry, not a transport: the same payload is
 * served through the REST API and injected into the settings page directly.
 * 
 * @since	3.1.0
 * 
 * @author	Epiphyt
 * @license	GPL2
 * @package	epiphyt\Impressum
 */
final class Schema {
	private const NATIVE_TYPES = [
		'checkbox',
		'country',
		'email',
		'legal_entity',
		'page',
		'phone',
		'select',
		'text',
		'textarea',
	];
	
	private const PAGE_STATUSES = [
		'draft',
		'publish',
	];
	
	/**
	 * @var		bool Whether the page choices have been looked up
	 */
	private bool $has_page_choices = false;
	
	/**
	 * @var		?mixed[] Memoized page choices
	 */
	private ?array $page_choices = null;
	
	/**
	 * @var		?\epiphyt\Impressum\settings\Legacy_Renderer Legacy renderer
	 */
	private ?Legacy_Renderer $legacy_renderer = null;
	
	/**
	 * @var		?\epiphyt\Impressum\settings\Registry Settings registry
	 */
	private ?Registry $registry = null;
	
	/**
	 * @var		?\epiphyt\Impressum\settings\Tabs Settings tabs
	 */
	private ?Tabs $tabs = null;
	
	/**
	 * @var		?\epiphyt\Impressum\settings\Validator Settings validator
	 */
	private ?Validator $validator = null;
	
	/**
	 * Schema constructor.
	 * 
	 * @param	\epiphyt\Impressum\settings\Registry		$registry Settings registry
	 * @param	\epiphyt\Impressum\settings\Validator		$validator Settings validator
	 * @param	\epiphyt\Impressum\settings\Legacy_Renderer	$legacy_renderer Legacy renderer
	 * @param	\epiphyt\Impressum\settings\Tabs			$tabs Settings tabs
	 */
	public function __construct( Registry $registry, Validator $validator, Legacy_Renderer $legacy_renderer, Tabs $tabs ) {
		$this->legacy_renderer = $legacy_renderer;
		$this->registry = $registry;
		$this->tabs = $tabs;
		$this->validator = $validator;
	}
	
	/**
	 * Get the reference to the shared choice list of a field type.
	 * 
	 * @param	string	$type Field type
	 * @return	?string Choice list name, or null if the type has no shared list
	 */
	private function get_choices_reference( string $type ): ?string {
		return match ( $type ) {
			'country' => 'country',
			'legal_entity' => 'legalEntity',
			'page' => 'pages',
			default => null,
		};
	}
	
	/**
	 * Get the control descriptor of a field.
	 * 
	 * A field type the settings app does not know can describe itself instead
	 * of rendering markup, which makes it look exactly like a built-in field.
	 * The callback instance is asked first, so that a field callback and its
	 * description stay together.
	 * 
	 * @param	\epiphyt\Impressum\settings\Setting	$setting Setting to describe
	 * @param	string								$type Field type
	 * @param	mixed[]								$attributes Field attributes
	 * @return	?mixed[] Control descriptor, or null if the field has none
	 */
	private function get_control( Setting $setting, string $type, array $attributes ): ?array {
		$id = \sprintf( '%1$s_%2$s', $setting->type, $setting->name );
		
		/**
		 * This filter is described in inc/class-admin-fields.php
		 */
		$callback_instance = \apply_filters(
			'impressum_admin_fields_callback_instance',
			\epiphyt\Impressum\Admin_Fields::get_instance(),
			$setting,
			$id
		);
		$control = null;
		
		if ( \is_callable( [ $callback_instance, 'get_field_descriptor' ] ) ) {
			$control = $callback_instance->get_field_descriptor( $type, $attributes, $setting );
		}
		
		/**
		 * Filter the control descriptor of a field.
		 * Returning a descriptor makes the settings app render the field itself
		 * instead of falling back to the markup of its callback.
		 * 
		 * @since	3.1.0
		 * 
		 * @param	?array								$control Control descriptor
		 * @param	\epiphyt\Impressum\settings\Setting	$setting Setting to describe
		 * @param	string								$type Field type
		 * @param	array								$attributes Field attributes
		 */
		$control = \apply_filters( 'impressum_settings_field_control', $control, $setting, $type, $attributes );
		
		return \is_array( $control ) && ! empty( $control['type'] ) ? $control : null;
	}
	
	/**
	 * Get all selectable countries.
	 * 
	 * @return	mixed[] List of country choices
	 */
	public function get_country_choices(): array {
		$choices = [];
		
		foreach ( \epiphyt\Impressum\get_container()->get( 'plugin' )->get_countries() as $code => $country ) {
			$choices[] = [
				'label' => $country['title'] ?? $code,
				'locale' => $country['locale'] ?? '',
				'localePrimary' => $country['locale_primary'] ?? '',
				'value' => $code,
			];
		}
		
		return $choices;
	}
	
	/**
	 * Get the markup printed by the per field action.
	 * 
	 * Legacy fields fire the action themselves while being rendered, so it is
	 * only captured separately for natively rendered fields.
	 * 
	 * @param	\epiphyt\Impressum\settings\Setting	$setting Setting to get the markup of
	 * @param	string								$label_for Field identifier
	 * @param	mixed[]								$options Settings of this settings group
	 * @return	string Rendered markup
	 */
	private function get_description_html( Setting $setting, string $label_for, array $options ): string {
		\ob_start();
		
		/**
		 * This action is described in inc/class-admin-fields.php
		 */
		\do_action(
			"impressum_option_description_{$label_for}",
			$setting->type,
			(array) $setting->get_data( 'setting_attributes' ),
			$options
		);
		
		return (string) \ob_get_clean();
	}
	
	/**
	 * Serialize a single setting.
	 * 
	 * @param	\epiphyt\Impressum\settings\Setting	$setting Setting to serialize
	 * @param	mixed[]								$context Serialization context
	 * @return	mixed[] Serialized setting
	 */
	public function get_field( Setting $setting, array $context = [] ): array {
		$attributes = (array) $setting->get_data( 'setting_attributes' );
		$type = (string) ( $setting->get_data( 'setting_callback' ) ?? '' );
		$label_for = (string) ( $attributes['label_for'] ?? $setting->name );
		$is_network = ! empty( $context['is_network'] );
		$defaults = (array) ( $context['defaults'] ?? [] );
		$options = (array) ( $context['options'] ?? [] );
		$control = $this->get_control( $setting, $type, $attributes );
		// a field is rendered by the settings app if it either is a type the app
		// knows, or describes itself as one
		$is_native = $control !== null || \in_array( $type, $this->get_native_types(), true );
		
		return [
			'apiDescription' => (string) $setting->get_data( 'description' ),
			'autocomplete' => (string) ( $attributes['autocomplete'] ?? '' ),
			'choicesRef' => $this->get_choices_reference( $type ),
			'conditions' => (array) $setting->get_data( 'conditions' ),
			'control' => $control,
			'cssClass' => (string) ( $attributes['class'] ?? '' ),
			'customTitle' => $setting->get_data( 'custom_title' ),
			'dataType' => (array) $setting->get_data( 'data_type' ),
			'description' => $this->get_field_description( $type, $attributes ),
			'descriptionHtml' => $is_native ? $this->get_description_html( $setting, $label_for, $options ) : '',
			'fieldTitle' => $setting->get_data( 'field_title' ),
			'group' => $setting->type,
			'hideOutput' => (bool) $setting->get_data( 'hide_output' ),
			'inputName' => \sprintf( '%1$s[%2$s]', $setting->type, $label_for ),
			'isNative' => $is_native,
			'key' => \sprintf( '%1$s_%2$s', $setting->type, $setting->name ),
			'label' => (string) ( $attributes['label'] ?? '' ),
			'labelFor' => $label_for,
			'legacyHtml' => $is_native ? null : $this->legacy_renderer->render_field( $setting ),
			'name' => $setting->name,
			'networkSupported' => (bool) $setting->get_data( 'network_supported' ),
			'notices' => (array) $setting->get_data( 'notices' ),
			'options' => $this->get_field_options( $attributes ),
			'page' => (string) $setting->get_data( 'setting_page' ),
			'placeholder' => $this->get_placeholder( $type, $label_for, $defaults, $is_network ),
			'rows' => isset( $attributes['rows'] ) ? (int) $attributes['rows'] : null,
			'section' => (string) $setting->get_data( 'setting_section' ),
			'suggestedValue' => $this->get_suggested_value( $type, $label_for, $options ),
			// custom titles are only stored for the imprint
			'supportsCustomTitle' => ! empty( $context['field_data_writable'] ) && $setting->type === 'impressum_imprint_options',
			'title' => $setting->get_title(),
			'type' => $type,
			'validation' => (array) $setting->get_data( 'validation' ),
			'valueLabel' => $this->get_value_label( $type, $label_for, $options, $is_network ),
		];
	}
	
	/**
	 * Get the description of a field.
	 * 
	 * Country and legal entity carry a description that is part of the field
	 * type rather than of the field definition.
	 * 
	 * @param	string	$type Field type
	 * @param	mixed[]	$attributes Field attributes
	 * @return	string Field description
	 */
	private function get_field_description( string $type, array $attributes ): string {
		if ( ! empty( $attributes['description'] ) ) {
			return (string) $attributes['description'];
		}
		
		return match ( $type ) {
			'country' => \__( 'In order to determine the needed fields for your imprint we need to know your country.', 'impressum' ),
			'legal_entity' => \__( 'In order to guide you the needed fields we need to know what kind of legal entity you are.', 'impressum' ),
			default => '',
		};
	}
	
	/**
	 * Get the choices of a field with its own option list.
	 * 
	 * @param	mixed[]	$attributes Field attributes
	 * @return	?mixed[] List of choices, or null if the field has none
	 */
	private function get_field_options( array $attributes ): ?array {
		if ( empty( $attributes['options'] ) ) {
			return null;
		}
		
		return \array_values( (array) $attributes['options'] );
	}
	
	/**
	 * Get the strings the settings app shares with the server side rendering.
	 * 
	 * @return	array<string, string> List of strings
	 */
	private function get_translations(): array {
		return [
			'networkUnavailable' => \__( 'This setting is not available in network options.', 'impressum' ),
			'noPages' => \__( 'There are no pages. Please create a page first.', 'impressum' ),
			'requiredNotice' => \__( 'This is a required field.', 'impressum' ),
			'saveSettings' => \__( 'Save Settings', 'impressum' ),
			'selectPlaceholder' => \__( '— Select —', 'impressum' ),
			'selectPlaceholderShort' => \__( '- Select -', 'impressum' ),
		];
	}
	
	/**
	 * Get every submitted value that cannot be stored.
	 * 
	 * The REST API would reject a value against the schema on its own, but it
	 * reports it by naming the parameter it belongs to, which is a path rather
	 * than something to put in front of a person. Validating here instead keeps
	 * the message about the value.
	 * 
	 * @param	string	$group Settings group
	 * @param	mixed[]	$values Submitted values of the group
	 * @return	array<string, array{code: string, message: string}> Errors, keyed by setting name
	 */
	public function get_invalid_values( string $group, array $values ): array {
		$invalid = [];
		$settings = $this->registry->get_settings( $group );
		
		foreach ( $values as $name => $value ) {
			$setting = $settings[ $group . '_' . $name ] ?? null;
			
			// an unregistered value is dropped while saving rather than rejected
			if ( $setting === null ) {
				continue;
			}
			
			$error = $this->get_value_error(
				$value,
				(array) $setting->get_data( 'data_type' ),
				(string) ( $setting->get_data( 'setting_callback' ) ?? '' )
			);
			
			if ( $error !== null ) {
				$invalid[ $name ] = $error;
			}
		}
		
		return $invalid;
	}
	
	/**
	 * Get the JSON schema of a setting type.
	 * 
	 * @param	string	$group Setting type
	 * @return	array<string, mixed[]> JSON schema properties
	 */
	public function get_json_schema( string $group ): array {
		$properties = [];
		
		foreach ( $this->registry->get_settings( $group ) as $setting ) {
			$properties[ $setting->name ] = [
				'description' => (string) $setting->get_data( 'description' ),
			] + $this->get_value_schema(
				(array) $setting->get_data( 'data_type' ),
				(string) ( $setting->get_data( 'setting_callback' ) ?? '' )
			);
		}
		
		return $properties;
	}
	
	/**
	 * Get all selectable legal entities.
	 * 
	 * @return	mixed[] List of legal entity choices
	 */
	public function get_legal_entity_choices(): array {
		$choices = [];
		
		foreach ( \epiphyt\Impressum\get_container()->get( 'plugin' )->get_legal_entities() as $abbreviation => $entity ) {
			$choices[] = [
				'label' => $entity,
				'value' => $abbreviation,
			];
		}
		
		return $choices;
	}
	
	/**
	 * Get all field types the settings app renders natively.
	 * 
	 * @return	string[] List of field types
	 */
	private function get_native_types(): array {
		/**
		 * Filter the field types rendered natively by the settings app.
		 * Types that are not listed are rendered server side instead.
		 * 
		 * @since	3.1.0
		 * 
		 * @param	string[]	$native_types List of field types
		 */
		return (array) \apply_filters( 'impressum_native_field_types', self::NATIVE_TYPES );
	}
	
	/**
	 * Get the validation errors of the imprint.
	 * 
	 * Empty values fall back to the network wide ones, as they do when the
	 * imprint is output.
	 * 
	 * @param	mixed[]	$values Values, keyed by settings group
	 * @param	mixed[]	$defaults Network wide values, keyed by settings group
	 * @return	mixed[] Validation errors
	 */
	private function get_imprint_errors( array $values, array $defaults ): array {
		return $this->validator->get_errors(
			'impressum_imprint_options',
			\array_merge(
				(array) ( $defaults['impressum_imprint_options'] ?? [] ),
				\array_filter( (array) ( $values['impressum_imprint_options'] ?? [] ) )
			)
		);
	}
	
	/**
	 * Get the network wide values of a settings group, used as placeholders.
	 * 
	 * Impressum Plus already merges them into the option as 'default' outside
	 * of the network scope, otherwise they are read from the network option.
	 * 
	 * @param	string	$option Option name
	 * @param	mixed[]	$options Values of the option in the current scope
	 * @param	bool	$is_network Whether the network wide settings are edited
	 * @return	mixed[] Network wide values
	 */
	private function get_network_defaults( string $option, array $options, bool $is_network ): array {
		if ( $is_network ) {
			return [];
		}
		
		if ( isset( $options['default'] ) ) {
			return (array) $options['default'];
		}
		
		if ( ! \is_multisite() ) {
			return [];
		}
		
		return $this->get_option_values( $option, true );
	}
	
	/**
	 * Get the values of an option within a scope.
	 * 
	 * The scope is set explicitly, since it cannot be determined during a
	 * REST request.
	 * 
	 * @param	string	$option Option name
	 * @param	bool	$is_network Whether to get the network wide option
	 * @return	mixed[] Option values
	 */
	private function get_option_values( string $option, bool $is_network ): array {
		$values = Helper::with_scope(
			$is_network ? 'network' : 'site',
			static fn (): mixed => Helper::get_option( $option, ! $is_network )
		);
		
		return \is_array( $values ) ? $values : [];
	}
	
	/**
	 * Get all selectable pages.
	 * 
	 * Returns null if there are more pages than the settings app should embed,
	 * in which case it searches for pages on demand instead.
	 * 
	 * @return	?mixed[] List of page choices, or null if there are too many
	 */
	public function get_page_choices(): ?array {
		if ( $this->has_page_choices ) {
			return $this->page_choices;
		}
		
		$this->has_page_choices = true;
		$this->page_choices = $this->load_page_choices();
		
		return $this->page_choices;
	}
	
	/**
	 * Get the accepted values of a page setting.
	 * 
	 * Both forms of an ID are accepted, since a value is matched against the
	 * enum before it is converted to the type it belongs to.
	 * 
	 * @return	?mixed[] Accepted values, or null if the pages cannot be enumerated
	 */
	private function get_page_enum(): ?array {
		$pages = $this->get_page_choices();
		
		if ( $pages === null ) {
			return null;
		}
		
		return \array_merge( [ '' ], \array_column( $pages, 'value' ) );
	}
	
	/**
	 * Get the error of a page value.
	 * 
	 * @param	mixed	$value Value to check
	 * @return	?array{code: string, message: string} Error, or null if the page can be stored
	 */
	private function get_page_error( mixed $value ): ?array {
		$error = [
			'code' => 'impressum_invalid_page',
			'message' => \__( 'Select a page from the list.', 'impressum' ),
		];
		
		if ( ! $this->is_integer_value( $value ) ) {
			return $error;
		}
		
		$pages = $this->get_page_choices();
		
		if ( $pages === null ) {
			return $this->get_selectable_page( (int) $value ) !== null ? null : $error;
		}
		
		return \in_array( (int) $value, \array_column( $pages, 'value' ), true ) ? null : $error;
	}
	
	/**
	 * Get the full settings payload.
	 * 
	 * @param	array{current_tab?: string, default_tab?: string, form_action?: string, group?: string, is_network?: bool, with_legacy?: bool, with_pages?: bool}	$args Payload arguments
	 * @return	mixed[] Settings payload
	 */
	public function get_payload( array $args = [] ): array {
		$is_network = ! empty( $args['is_network'] );
		$group = (string) ( $args['group'] ?? '' );
		$with_pages = ! \array_key_exists( 'with_pages', $args ) || ! empty( $args['with_pages'] );
		$field_data_writable = $this->is_field_data_writable();
		$groups = [];
		$fields = [];
		$values = [];
		$defaults = [];
		$types = $this->registry->get_setting_types();
		
		if ( $group !== '' ) {
			$types = \in_array( $group, $types, true ) ? [ $group ] : [];
		}
		
		foreach ( $types as $type ) {
			$options = $this->get_option_values( $type, $is_network );
			$type_defaults = $this->get_network_defaults( $type, $options, $is_network );
			unset( $options['default'] );
			
			$defaults[ $type ] = $type_defaults;
			$groups[ $type ] = [
				'name' => $type,
				'networkSupported' => true,
			];
			$values[ $type ] = $options;
			
			foreach ( $this->registry->get_settings( $type ) as $key => $setting ) {
				$fields[ $key ] = $this->get_field( $setting, [
					'defaults' => $type_defaults,
					'field_data_writable' => $field_data_writable,
					'is_network' => $is_network,
					'options' => $options,
				] );
			}
		}
		
		$payload = [
			'choices' => [
				'country' => $this->get_country_choices(),
				'legalEntity' => $this->get_legal_entity_choices(),
				'pages' => $with_pages && ! $is_network ? $this->get_page_choices() : null,
			],
			'defaults' => $defaults,
			'defaultTab' => (string) ( $args['default_tab'] ?? 'imprint' ),
			'fieldData' => $this->get_option_values( 'impressum_field_data', $is_network ),
			'fieldDataWritable' => $field_data_writable,
			'fields' => $fields,
			'formAction' => (string) ( $args['form_action'] ?? '' ),
			'groups' => $groups,
			'i18n' => $this->get_translations(),
			'isNetwork' => $is_network,
			'legacy' => ! empty( $args['with_legacy'] ) ? $this->legacy_renderer->get_legacy_payload( $args ) : null,
			'nonce' => (string) ( $args['nonce'] ?? '' ),
			'optionPage' => (string) ( $args['option_page'] ?? 'impressum_imprint' ),
			'referer' => (string) ( $args['referer'] ?? '' ),
			'tabs' => $this->tabs->get_tabs( $args ),
			'validation' => $this->get_imprint_errors( $values, $defaults ),
			'values' => $values,
			'version' => \EPI_IMPRESSUM_VERSION,
		];
		
		/**
		 * Filter the settings payload.
		 * 
		 * @since	3.1.0
		 * 
		 * @param	array	$payload Settings payload
		 * @param	array	$args Payload arguments
		 */
		return (array) \apply_filters( 'impressum_settings_payload', $payload, $args );
	}
	
	/**
	 * Get the placeholder of a field.
	 * 
	 * The placeholder shows the network wide value, which is not relevant while
	 * editing the network wide settings themselves.
	 * 
	 * @param	string	$type Field type
	 * @param	string	$label_for Field identifier
	 * @param	mixed[]	$defaults Network wide values
	 * @param	bool	$is_network Whether the network settings are edited
	 * @return	string Placeholder
	 */
	private function get_placeholder( string $type, string $label_for, array $defaults, bool $is_network ): string {
		if ( $is_network || empty( $defaults[ $label_for ] ) ) {
			return '';
		}
		
		$placeholder = (string) $defaults[ $label_for ];
		
		if ( $type === 'textarea' ) {
			return \str_replace( "\r\n", ', ', $placeholder );
		}
		
		return $placeholder;
	}
	
	/**
	 * Get a page that can be selected.
	 * 
	 * @param	int		$page_id Page ID
	 * @return	?\WP_Post Page, or null if there is no selectable page with this ID
	 */
	private function get_selectable_page( int $page_id ): ?\WP_Post {
		$page = \get_post( $page_id );
		
		if (
			! $page instanceof \WP_Post
			|| $page->post_type !== 'page'
			|| ! \in_array( $page->post_status, self::PAGE_STATUSES, true )
		) {
			return null;
		}
		
		return $page;
	}
	
	/**
	 * Get the value suggested for an empty field.
	 * 
	 * @param	string	$type Field type
	 * @param	string	$label_for Field identifier
	 * @param	mixed[]	$options Settings of this settings group
	 * @return	?string Suggested value, or null if there is none
	 */
	private function get_suggested_value( string $type, string $label_for, array $options ): ?string {
		if ( $type !== 'country' || ! empty( $options[ $label_for ] ) ) {
			return null;
		}
		
		return self::guess_country( \epiphyt\Impressum\get_container()->get( 'plugin' )->get_countries() );
	}
	
	/**
	 * Get the error of a single value.
	 * 
	 * @param	mixed	$value Value to check
	 * @param	mixed[]	$data_type Declared data type of the setting
	 * @param	string	$field_type Field type of the setting
	 * @return	?array{code: string, message: string} Error, or null if the value can be stored
	 */
	private function get_value_error( mixed $value, array $data_type, string $field_type ): ?array {
		// every setting may be empty, which is how it is unset
		if ( $value === '' || $value === null || $value === [] ) {
			return null;
		}
		
		if ( $field_type === 'page' ) {
			return $this->get_page_error( $value );
		}
		
		if ( ! empty( $data_type['enum'] ) ) {
			$allowed = \array_map( '\strval', (array) $data_type['enum'] );
			
			if ( \is_array( $value ) || ! \in_array( (string) $value, $allowed, true ) ) {
				return [
					'code' => 'impressum_invalid_choice',
					'message' => \__( 'Select a value from the list, the currently selected value is invalid.', 'impressum' ),
				];
			}
			
			return null;
		}
		
		return match ( $data_type['type'] ?? 'string' ) {
			'boolean' => $this->is_boolean_value( $value ) ? null : [
				'code' => 'impressum_invalid_boolean',
				'message' => \__( 'This value has to be either on or off.', 'impressum' ),
			],
			'integer' => $this->is_integer_value( $value ) ? null : [
				'code' => 'impressum_invalid_integer',
				'message' => \__( 'Enter a whole number.', 'impressum' ),
			],
			'number' => \is_numeric( $value ) ? null : [
				'code' => 'impressum_invalid_number',
				'message' => \__( 'Enter a number.', 'impressum' ),
			],
			default => null,
		};
	}
	
	/**
	 * Get the label of the stored value of a page field.
	 * 
	 * Without an embedded list of pages, the settings app could otherwise not
	 * show which page is selected.
	 * 
	 * @param	string	$type Field type
	 * @param	string	$label_for Field identifier
	 * @param	mixed[]	$options Settings of this settings group
	 * @param	bool	$is_network Whether the network settings are edited
	 * @return	?string Label of the stored value, or null if there is none
	 */
	private function get_value_label( string $type, string $label_for, array $options, bool $is_network ): ?string {
		if ( $type !== 'page' || $is_network || empty( $options[ $label_for ] ) ) {
			return null;
		}
		
		$page = $this->get_selectable_page( (int) $options[ $label_for ] );
		
		if ( $page === null ) {
			return null;
		}
		
		return $page->post_title !== '' ? $page->post_title : \sprintf( '#%d', $page->ID );
	}
	
	/**
	 * Get the schema a submitted value is validated against.
	 * 
	 * Values arrive the way a form submits them: as strings, and empty whenever
	 * a setting is not set. A setting therefore accepts the type it declares
	 * and a string, where the pattern constrains that string to the declared
	 * type again while still allowing it to be empty.
	 * 
	 * A value matching the declared type is stored as that type, since the REST
	 * API sanitizes a value against the type it matched: a setting declared as
	 * an integer and submitted as '5' is stored as 5.
	 * 
	 * @param	mixed[]	$data_type Declared data type of the setting
	 * @param	string	$field_type Field type of the setting
	 * @return	mixed[] Value schema
	 */
	private function get_value_schema( array $data_type, string $field_type = '' ): array {
		$type = (string) ( $data_type['type'] ?? 'string' );
		$page_enum = $field_type === 'page' ? $this->get_page_enum() : null;
		
		// a page setting only accepts a page that exists
		if ( $page_enum !== null ) {
			return [
				'enum' => $page_enum,
				'type' => [ 'integer', 'string' ],
			];
		}
		
		if ( ! empty( $data_type['enum'] ) && $type === 'string' ) {
			return [
				'enum' => \array_merge( [ '' ], \array_values( (array) $data_type['enum'] ) ),
				'type' => 'string',
			];
		}
		
		// the string branch only exists to allow an empty value, so its pattern
		// has to reject everything the declared type would reject; for a plain
		// string it comes before 'array', since rest_is_array() accepts a scalar
		// as a separated list and would turn a single value into one
		return match ( $type ) {
			'boolean' => [
				'pattern' => '^(0|1)?$',
				'type' => [ 'boolean', 'string' ],
			],
			'integer' => [
				'pattern' => '^[0-9]*$',
				'type' => [ 'integer', 'string' ],
			],
			'number' => [
				'pattern' => '^-?[0-9]*(\.[0-9]+)?$',
				'type' => [ 'number', 'string' ],
			],
			default => [
				'type' => [ 'string', 'array' ],
			],
		};
	}
	
	/**
	 * Get the part of the payload a write can change.
	 * 
	 * The settings themselves cannot change through a write, so repeating the
	 * whole payload – every field, every choice and every tab – after one would
	 * only resend what the settings app already has.
	 * 
	 * @param	array{is_network?: bool}	$args Payload arguments
	 * @return	mixed[] Values payload
	 */
	public function get_values_payload( array $args = [] ): array {
		$is_network = ! empty( $args['is_network'] );
		$defaults = [];
		$values = [];
		
		foreach ( $this->registry->get_setting_types() as $type ) {
			$options = $this->get_option_values( $type, $is_network );
			$defaults[ $type ] = $this->get_network_defaults( $type, $options, $is_network );
			
			unset( $options['default'] );
			
			$values[ $type ] = $options;
		}
		
		return [
			'defaults' => $defaults,
			'fieldData' => $this->get_option_values( 'impressum_field_data', $is_network ),
			'validation' => $this->get_imprint_errors( $values, $defaults ),
			'values' => $values,
		];
	}
	
	/**
	 * Guess the country from the browser's accept language header.
	 * 
	 * @param	mixed[]	$countries List of countries
	 * @return	?string Country code, or null if none matches
	 */
	public static function guess_country( array $countries ): ?string {
		if ( empty( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
			return null;
		}
		
		$accept_language = \strtolower( \sanitize_text_field( \wp_unslash( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) );
		
		foreach ( $countries as $code => $country ) {
			if ( \str_contains( $accept_language, (string) $code ) ) {
				return (string) $code;
			}
			
			if (
				! empty( $country['locale_primary'] )
				&& \str_contains( $accept_language, (string) $country['locale_primary'] )
			) {
				return (string) $code;
			}
		}
		
		return null;
	}
	
	/**
	 * Check whether a value represents a boolean.
	 * 
	 * @param	mixed	$value Value to check
	 * @return	bool Whether the value represents a boolean
	 */
	private function is_boolean_value( mixed $value ): bool {
		return \is_bool( $value ) || \in_array( $value, [ 0, 1, '0', '1' ], true );
	}
	
	/**
	 * Check whether custom field titles can be stored.
	 * 
	 * The option is only registered by Impressum Plus, which hooks its
	 * normalization early enough to be detectable during a REST request, where
	 * the allowed options are not populated.
	 * 
	 * @return	bool Whether custom field titles are writable
	 */
	public function is_field_data_writable(): bool {
		global $new_allowed_options;
		
		if (
			isset( $new_allowed_options['impressum_imprint'] )
			&& \in_array( 'impressum_field_data', (array) $new_allowed_options['impressum_imprint'], true )
		) {
			return true;
		}
		
		return \has_filter( 'pre_update_option_impressum_field_data' ) !== false;
	}
	
	/**
	 * Check whether a value represents a whole number.
	 * 
	 * @param	mixed	$value Value to check
	 * @return	bool Whether the value represents a whole number
	 */
	private function is_integer_value( mixed $value ): bool {
		return \is_int( $value ) || ( \is_string( $value ) && \preg_match( '/^[0-9]+$/', $value ) === 1 );
	}
	
	/**
	 * Look up all selectable pages.
	 * 
	 * @return	?mixed[] List of page choices, or null if there are too many
	 */
	private function load_page_choices(): ?array {
		/**
		 * Filter the maximum number of pages embedded in the settings payload.
		 * 
		 * @since	3.1.0
		 * 
		 * @param	int		$limit Maximum number of pages
		 */
		$limit = (int) \apply_filters( 'impressum_page_choices_limit', 300 );
		$counts = \wp_count_posts( 'page' );
		$count = 0;
		
		foreach ( self::PAGE_STATUSES as $status ) {
			$count += (int) ( $counts->{$status} ?? 0 );
		}
		
		// counted first, so that a large number of pages is never loaded
		if ( $count > $limit ) {
			return null;
		}
		
		$pages = \get_pages( [
			'post_status' => self::PAGE_STATUSES,
		] );
		
		if ( ! \is_array( $pages ) ) {
			return [];
		}
		
		$choices = [];
		
		foreach ( $pages as $page ) {
			$choices[] = [
				'label' => $page->post_title,
				'value' => (int) $page->ID,
			];
		}
		
		return $choices;
	}
}
