<?php
declare(strict_types = 1);

namespace epiphyt\Impressum\settings;

/**
 * Evaluation of declarative setting conditions, validation rules and notices.
 * 
 * The rules live on the setting definitions so that the server and the settings
 * app agree on them instead of each implementing their own copy.
 * 
 * @since	3.1.0
 * 
 * @author	Epiphyt
 * @license	GPL2
 * @package	epiphyt\Impressum
 */
final class Validator {
	/**
	 * @var		?\epiphyt\Impressum\settings\Registry Settings registry
	 */
	private ?Registry $registry = null;
	
	/**
	 * Validator constructor.
	 * 
	 * @param	\epiphyt\Impressum\settings\Registry	$registry Settings registry
	 */
	public function __construct( Registry $registry ) {
		$this->registry = $registry;
	}
	
	/**
	 * Check whether a value is contained in a list of expected values.
	 * 
	 * @param	mixed	$actual Value to look for
	 * @param	mixed[]	$expected List of accepted values
	 * @return	bool Whether the value is contained in the list
	 */
	private function contains( mixed $actual, array $expected ): bool {
		foreach ( $expected as $value ) {
			if ( $this->is_equal( $actual, $value ) ) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Evaluate a condition against a set of values.
	 * 
	 * A condition is either a group – an array with an 'all' or 'any' key
	 * holding further conditions – or a single comparison with a 'field', an
	 * 'operator' and, depending on the operator, a 'value'.
	 * 
	 * @param	mixed[]	$condition Condition to evaluate
	 * @param	mixed[]	$values Values to evaluate the condition against
	 * @return	bool Whether the condition matches
	 */
	public function evaluate( array $condition, array $values ): bool {
		if ( isset( $condition['all'] ) ) {
			foreach ( (array) $condition['all'] as $sub_condition ) {
				if ( ! $this->evaluate( (array) $sub_condition, $values ) ) {
					return false;
				}
			}
			
			return true;
		}
		
		if ( isset( $condition['any'] ) ) {
			foreach ( (array) $condition['any'] as $sub_condition ) {
				if ( $this->evaluate( (array) $sub_condition, $values ) ) {
					return true;
				}
			}
			
			return false;
		}
		
		if ( empty( $condition['field'] ) ) {
			return true;
		}
		
		$actual = $values[ $condition['field'] ] ?? null;
		$expected = $condition['value'] ?? null;
		
		return match ( $condition['operator'] ?? 'truthy' ) {
			'empty', 'falsy' => empty( $actual ),
			'in' => $this->contains( $actual, (array) $expected ),
			'is' => $this->is_equal( $actual, $expected ),
			'is_not' => ! $this->is_equal( $actual, $expected ),
			'matches' => \is_scalar( $actual ) && $this->matches_pattern( (string) $expected, (string) $actual ),
			'not_in' => ! $this->contains( $actual, (array) $expected ),
			default => ! empty( $actual ),
		};
	}
	
	/**
	 * Get all invalid fields of a setting type.
	 * 
	 * The returned list is keyed by field name – or by the pair key for fields
	 * validated as a pair – and holds the field titles, sorted naturally.
	 * 
	 * @param	string	$group Setting type to validate
	 * @param	mixed[]	$values Values to validate
	 * @param	bool	$include_client_only Whether to include rules only enforced in the settings app
	 * @return	array<string, string> Invalid fields with their titles
	 */
	public function get_errors( string $group, array $values, bool $include_client_only = false ): array {
		$errors = [];
		$settings = $this->registry->get_settings( $group );
		$prefix = $group . '_';
		
		foreach ( $this->get_required_fields( $group, $values, $include_client_only ) as $field ) {
			if ( ! empty( $values[ $field ] ) || ! isset( $settings[ $prefix . $field ] ) ) {
				continue;
			}
			
			$errors[ $field ] = $settings[ $prefix . $field ]->get_title();
		}
		
		$errors += $this->get_pair_errors( $group, $values );
		
		foreach ( $settings as $setting ) {
			$validation = (array) $setting->get_data( 'validation' );
			
			if ( empty( $validation['pattern'] ) || isset( $errors[ $setting->name ] ) ) {
				continue;
			}
			
			$value = $values[ $setting->name ] ?? '';
			
			if ( empty( $value ) || $this->matches_pattern( (string) $validation['pattern'], (string) $value ) ) {
				continue;
			}
			
			$errors[ $setting->name ] = $setting->get_title();
		}
		
		\asort( $errors, \SORT_NATURAL );
		
		return $errors;
	}
	
	/**
	 * Get all notices that currently apply.
	 * 
	 * @param	string	$group Setting type to get the notices of
	 * @param	mixed[]	$values Values to evaluate the notice conditions against
	 * @return	array<string, mixed[]> Active notices, keyed by field name
	 */
	public function get_notices( string $group, array $values ): array {
		$notices = [];
		
		foreach ( $this->registry->get_settings( $group ) as $setting ) {
			foreach ( (array) $setting->get_data( 'notices' ) as $notice ) {
				if ( ! empty( $notice['when'] ) && ! $this->evaluate( (array) $notice['when'], $values ) ) {
					continue;
				}
				
				$notices[ $setting->name ][] = $notice;
			}
		}
		
		return $notices;
	}
	
	/**
	 * Get the errors of all fields that are validated as a pair.
	 * 
	 * A pair is invalid if none of its two fields has a value, since either one
	 * of them satisfies the requirement.
	 * 
	 * @param	string	$group Setting type to validate
	 * @param	mixed[]	$values Values to validate
	 * @return	array<string, string> Invalid pairs with their combined titles
	 */
	public function get_pair_errors( string $group, array $values ): array {
		$errors = [];
		$settings = $this->registry->get_settings( $group );
		$prefix = $group . '_';
		
		foreach ( $settings as $setting ) {
			$validation = (array) $setting->get_data( 'validation' );
			
			if ( empty( $validation['pair_with'] ) || empty( $validation['pair_key'] ) ) {
				continue;
			}
			
			$partner = (string) $validation['pair_with'];
			
			if (
				! empty( $values[ $setting->name ] )
				|| ! empty( $values[ $partner ] )
				|| ! isset( $settings[ $prefix . $setting->name ], $settings[ $prefix . $partner ] )
			) {
				continue;
			}
			
			$errors[ (string) $validation['pair_key'] ] = \sprintf(
				(string) ( $validation['pair_title'] ?? '%1$s / %2$s' ),
				$settings[ $prefix . $setting->name ]->get_title(),
				$settings[ $prefix . $partner ]->get_title()
			);
		}
		
		return $errors;
	}
	
	/**
	 * Get all required fields of a setting type.
	 * 
	 * @param	string	$group Setting type to get the required fields of
	 * @param	mixed[]	$values Values to evaluate conditional requirements against
	 * @param	bool	$include_client_only Whether to include fields only required in the settings app
	 * @return	string[] List of required field names
	 */
	public function get_required_fields( string $group, array $values, bool $include_client_only = false ): array {
		$required_fields = [];
		
		foreach ( $this->registry->get_settings( $group ) as $setting ) {
			$validation = (array) $setting->get_data( 'validation' );
			
			if ( ! $include_client_only && ! empty( $validation['client_only'] ) ) {
				continue;
			}
			
			if ( ! empty( $validation['required'] ) ) {
				$required_fields[] = $setting->name;
				
				continue;
			}
			
			if (
				! empty( $validation['required_unless'] )
				&& ! $this->evaluate( (array) $validation['required_unless'], $values )
			) {
				$required_fields[] = $setting->name;
			}
		}
		
		\sort( $required_fields, \SORT_NATURAL );
		
		/**
		 * Filter required fields.
		 * 
		 * @since	2.1.0
		 * 
		 * @param	string[]	$required_fields List of required fields
		 * @param	array		$options Imprint options
		 */
		return (array) \apply_filters( 'impressum_required_fields', $required_fields, $values );
	}
	
	/**
	 * Compare two values.
	 * 
	 * Option values are strings once they come back from the database, so
	 * scalars are compared as strings to avoid type juggling surprises.
	 * 
	 * @param	mixed	$actual Actual value
	 * @param	mixed	$expected Expected value
	 * @return	bool Whether both values are considered equal
	 */
	private function is_equal( mixed $actual, mixed $expected ): bool {
		if ( \is_scalar( $actual ) && \is_scalar( $expected ) ) {
			return (string) $actual === (string) $expected;
		}
		
		return $actual === $expected;
	}
	
	/**
	 * Check whether a setting is currently visible.
	 * 
	 * @param	\epiphyt\Impressum\settings\Setting	$setting Setting to check
	 * @param	mixed[]								$values Values to evaluate the conditions against
	 * @return	bool Whether the setting is visible
	 */
	public function is_visible( Setting $setting, array $values ): bool {
		$conditions = (array) $setting->get_data( 'conditions' );
		
		if ( empty( $conditions['visible'] ) ) {
			return true;
		}
		
		return $this->evaluate( (array) $conditions['visible'], $values );
	}
	
	/**
	 * Match a value against a delimiter-less pattern.
	 * 
	 * Patterns are stored without delimiters so that they are valid in both
	 * PCRE and ECMAScript and can be shared with the settings app.
	 * 
	 * @param	string	$pattern Pattern without delimiters
	 * @param	string	$value Value to match
	 * @return	bool Whether the value matches the pattern
	 */
	private function matches_pattern( string $pattern, string $value ): bool {
		if ( $pattern === '' ) {
			return true;
		}
		
		// escape every delimiter that is not escaped already
		$pattern = (string) \preg_replace( '#(?<!\\\\)((?:\\\\\\\\)*)/#', '$1\\/', $pattern );
		$result = \preg_match( '/' . $pattern . '/', $value );
		
		// like in the settings app, an invalid pattern must never block saving
		return $result === false || $result === 1;
	}
}
