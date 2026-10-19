<?php
declare(strict_types = 1);

namespace epiphyt\Impressum\api;

use epiphyt\Impressum\Helper;
use epiphyt\Impressum\settings\Registry;
use epiphyt\Impressum\settings\Schema;
use epiphyt\Impressum\settings\Validator;

/**
 * REST controller for the settings.
 * 
 * Values are written through update_option(), so that every pre_update_option
 * filter – and with it the sanitizing of the settings data – applies exactly as
 * it does for the classic form submission. This controller intentionally
 * sanitizes nothing on its own.
 * 
 * @since	3.1.0
 * 
 * @author	Epiphyt
 * @license	GPL2
 * @package	epiphyt\Impressum
 */
final class Settings_Controller extends \WP_REST_Controller {
	/**
	 * @var		?\epiphyt\Impressum\settings\Registry Settings registry
	 */
	private ?Registry $registry = null;
	
	/**
	 * @var		?\epiphyt\Impressum\settings\Schema Settings schema
	 */
	private ?Schema $settings_schema = null;
	
	/**
	 * @var		?\epiphyt\Impressum\settings\Validator Settings validator
	 */
	private ?Validator $validator = null;
	
	/**
	 * Settings controller constructor.
	 * 
	 * @param	\epiphyt\Impressum\settings\Registry	$registry Settings registry
	 * @param	\epiphyt\Impressum\settings\Schema		$schema Settings schema
	 * @param	\epiphyt\Impressum\settings\Validator	$validator Settings validator
	 */
	public function __construct( Registry $registry, Schema $schema, Validator $validator ) {
		$this->namespace = 'impressum/v1';
		$this->registry = $registry;
		$this->rest_base = 'settings';
		$this->settings_schema = $schema;
		$this->validator = $validator;
	}
	
	/**
	 * Check the capability needed for the requested scope.
	 * 
	 * is_network_admin() is always false during a REST request, so the scope
	 * cannot be determined from the request context and has to be passed in.
	 * 
	 * @param	\WP_REST_Request	$request Current request
	 * @return	bool|\WP_Error Whether the current user has the capability
	 */
	private function check_capability( \WP_REST_Request $request ): bool|\WP_Error {
		$is_network = (bool) $request->get_param( 'network' );
		
		if ( $is_network && ! \is_multisite() ) {
			return new \WP_Error(
				'impressum_rest_no_multisite',
				\__( 'Network wide settings are only available on a multisite.', 'impressum' ),
				[ 'status' => 400 ]
			);
		}
		
		if ( ! \current_user_can( $is_network ? 'manage_network_options' : 'manage_options' ) ) {
			return new \WP_Error(
				'rest_forbidden',
				\__( 'You are not allowed to manage these settings.', 'impressum' ),
				[ 'status' => \rest_authorization_required_code() ]
			);
		}
		
		return true;
	}
	
	/**
	 * Validate all submitted values.
	 * 
	 * @param	mixed[]		$submitted Submitted values, keyed by settings group
	 * @param	string[]	$setting_types Registered settings groups
	 * @return	?\WP_Error Error naming every invalid setting, or null if all are valid
	 */
	private function get_invalid_values_error( array $submitted, array $setting_types ): ?\WP_Error {
		$fields = [];
		
		foreach ( $submitted as $group => $group_values ) {
			if ( ! \in_array( $group, $setting_types, true ) ) {
				continue;
			}
			
			foreach ( $this->settings_schema->get_invalid_values( $group, (array) $group_values ) as $name => $error ) {
				$fields[ $group . '.' . $name ] = $error + [
					'group' => $group,
					'name' => $name,
				];
			}
		}
		
		if ( $fields === [] ) {
			return null;
		}
		
		return new \WP_Error(
			'impressum_invalid_settings',
			\sprintf(
				/* translators: number of invalid settings */
				\_n(
					'%d setting is invalid.',
					'%d settings are invalid.',
					\count( $fields ),
					'impressum'
				),
				\count( $fields )
			),
			[
				'fields' => $fields,
				'status' => 400,
			]
		);
	}
	
	/**
	 * Get the settings payload.
	 * 
	 * @param	\WP_REST_Request	$request Current request
	 * @return	\WP_REST_Response Settings payload
	 */
	public function get_item( $request ): \WP_REST_Response { // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint -- the signature is defined by \WP_REST_Controller
		$is_network = (bool) $request->get_param( 'network' );
		
		return \rest_ensure_response(
			Helper::with_scope(
				$is_network ? 'network' : 'site',
				fn (): array => $this->settings_schema->get_payload( [
					// the settings API is not registered during a REST request
					'bootstrap' => (bool) $request->get_param( 'legacy' ),
					'group' => (string) $request->get_param( 'group' ),
					'is_network' => $is_network,
					'with_legacy' => (bool) $request->get_param( 'legacy' ),
				] )
			)
		);
	}
	
	/**
	 * Check whether the settings may be read.
	 * 
	 * @param	\WP_REST_Request	$request Current request
	 * @return	bool|\WP_Error Whether the settings may be read
	 */
	public function get_item_permissions_check( $request ): bool|\WP_Error { // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint -- the signature is defined by \WP_REST_Controller
		return $this->check_capability( $request );
	}
	
	/**
	 * Get the schema properties of all settings groups.
	 * 
	 * @return	array<string, mixed[]> Schema properties
	 */
	private function get_values_properties(): array {
		$properties = [];
		
		foreach ( $this->registry->get_setting_types() as $type ) {
			// only the shape is checked here – the values themselves are checked
			// while writing, so that every error names the setting it belongs to
			$properties[ $type ] = [
				'type' => 'object',
			];
		}
		
		return $properties;
	}
	
	/**
	 * Register the routes.
	 */
	public function register_routes(): void {
		$scope_args = [
			'network' => [
				'default' => false,
				'description' => \__( 'Whether to read or write the network wide settings.', 'impressum' ),
				'type' => 'boolean',
			],
		];
		
		// phpcs:disable SlevomatCodingStandard.Arrays.DisallowPartiallyKeyed.DisallowedPartiallyKeyed, Universal.Arrays.MixedKeyedUnkeyedArray.Found, Universal.Arrays.MixedArrayKeyTypes.StringKey -- required shape of register_rest_route()
		\register_rest_route( $this->namespace, '/' . $this->rest_base, [
			[
				'args' => $scope_args + [
					'group' => [
						'default' => '',
						'description' => \__( 'Limit the payload to a single settings group.', 'impressum' ),
						'enum' => \array_merge( [ '' ], $this->registry->get_setting_types() ),
						'type' => 'string',
					],
					'legacy' => [
						'default' => false,
						'description' => \__( 'Whether to include server side rendered markup.', 'impressum' ),
						'type' => 'boolean',
					],
				],
				'callback' => [ $this, 'get_item' ],
				'methods' => \WP_REST_Server::READABLE,
				'permission_callback' => [ $this, 'get_item_permissions_check' ],
			],
			[
				'args' => $scope_args + [
					'field_data' => [
						'additionalProperties' => [
							'properties' => [
								'name' => [ 'type' => 'string' ],
							],
							'type' => 'object',
						],
						'default' => [],
						'description' => \__( 'Custom field titles.', 'impressum' ),
						'type' => 'object',
					],
					'values' => [
						'description' => \__( 'Settings values, keyed by settings group.', 'impressum' ),
						'properties' => $this->get_values_properties(),
						'required' => true,
						'type' => 'object',
					],
				],
				'callback' => [ $this, 'update_item' ],
				'methods' => 'POST, PUT, PATCH',
				'permission_callback' => [ $this, 'update_item_permissions_check' ],
			],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );
		// phpcs:enable SlevomatCodingStandard.Arrays.DisallowPartiallyKeyed.DisallowedPartiallyKeyed, Universal.Arrays.MixedKeyedUnkeyedArray.Found, Universal.Arrays.MixedArrayKeyTypes.StringKey
		\register_rest_route( $this->namespace, '/' . $this->rest_base . '/validate', [
			[
				'args' => $scope_args + [
					'values' => [
						'description' => \__( 'Settings values, keyed by settings group.', 'impressum' ),
						'properties' => $this->get_values_properties(),
						'required' => true,
						'type' => 'object',
					],
				],
				'callback' => [ $this, 'validate_item' ],
				'methods' => \WP_REST_Server::CREATABLE,
				'permission_callback' => [ $this, 'get_item_permissions_check' ],
			],
		] );
	}
	
	/**
	 * Write the custom field titles.
	 * 
	 * @param	\WP_REST_Request	$request Current request
	 * @param	bool				$is_network Whether the network wide settings are written
	 */
	private function update_field_data( \WP_REST_Request $request, bool $is_network ): void {
		$field_data = (array) $request->get_param( 'field_data' );
		
		if ( empty( $field_data ) || ! $this->settings_schema->is_field_data_writable() ) {
			return;
		}
		
		$allowed = [];
		
		foreach ( $this->registry->get_settings( 'impressum_imprint_options' ) as $setting ) {
			$allowed[ $setting->name ] = true;
		}
		
		$field_data = \array_intersect_key( $field_data, $allowed );
		
		if ( $is_network ) {
			\update_site_option( 'impressum_field_data', $field_data );
		}
		else {
			\update_option( 'impressum_field_data', $field_data );
		}
		
		Helper::clear_option_cache( 'impressum_field_data' );
	}
	
	/**
	 * Write the settings.
	 * 
	 * @param	\WP_REST_Request	$request Current request
	 * @return	\WP_REST_Response|\WP_Error Updated settings payload
	 */
	public function update_item( $request ): \WP_REST_Response|\WP_Error { // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint -- the signature is defined by \WP_REST_Controller
		$is_network = (bool) $request->get_param( 'network' );
		$submitted = (array) $request->get_param( 'values' );
		$setting_types = $this->registry->get_setting_types();
		$invalid = $this->get_invalid_values_error( $submitted, $setting_types );
		
		if ( $invalid !== null ) {
			return $invalid;
		}
		
		foreach ( $submitted as $group => $group_values ) {
			if ( ! \in_array( $group, $setting_types, true ) ) {
				continue;
			}
			
			$current = (array) ( $is_network ? \get_site_option( $group, [] ) : \get_option( $group, [] ) );
			
			// the merged network values are a read time artifact and are never stored
			unset( $current['default'] );
			
			$values = \array_merge( $current, (array) $group_values );
			
			if ( $is_network ) {
				\update_site_option( $group, $values );
			}
			else {
				\update_option( $group, $values );
			}
			
			// update_site_option() does not trigger the option cache invalidation
			Helper::clear_option_cache( $group );
		}
		
		$this->update_field_data( $request, $is_network );
		
		return \rest_ensure_response(
			Helper::with_scope(
				$is_network ? 'network' : 'site',
				fn (): array => $this->settings_schema->get_values_payload( [
					'is_network' => $is_network,
				] )
			)
		);
	}
	
	/**
	 * Check whether the settings may be written.
	 * 
	 * @param	\WP_REST_Request	$request Current request
	 * @return	bool|\WP_Error Whether the settings may be written
	 */
	public function update_item_permissions_check( $request ): bool|\WP_Error { // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint -- the signature is defined by \WP_REST_Controller
		return $this->check_capability( $request );
	}
	
	/**
	 * Validate settings values without writing them.
	 * 
	 * @param	\WP_REST_Request	$request Current request
	 * @return	\WP_REST_Response Validation result
	 */
	public function validate_item( \WP_REST_Request $request ): \WP_REST_Response {
		$submitted = (array) $request->get_param( 'values' );
		$values = (array) ( $submitted['impressum_imprint_options'] ?? [] );
		
		return \rest_ensure_response( [
			'errors' => $this->validator->get_errors( 'impressum_imprint_options', $values, true ),
			'notices' => $this->validator->get_notices( 'impressum_imprint_options', $values ),
			'required' => $this->validator->get_required_fields( 'impressum_imprint_options', $values, true ),
		] );
	}
}
