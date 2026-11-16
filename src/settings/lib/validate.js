import { evaluate, isEmpty, matchesPattern } from './conditions';

/**
 * Get the values that are in effect for a group.
 *
 * Empty values fall back to the network wide ones, as they do when the
 * imprint is output.
 *
 * @param {Object} values   Current values of the group
 * @param {Object} defaults Network wide values of the group
 * @return {Object} Effective values
 */
export function getEffectiveValues( values, defaults ) {
	const effective = { ...( defaults ?? {} ) };

	for ( const [ name, value ] of Object.entries( values ?? {} ) ) {
		if ( ! isEmpty( value ) || ! ( name in effective ) ) {
			effective[ name ] = value;
		}
	}

	return effective;
}

/**
 * Check whether a field is currently required.
 *
 * @param {Object} field  Field to check
 * @param {Object} values Values to evaluate conditional requirements against
 * @return {boolean} Whether the field is required
 */
export function isRequired( field, values ) {
	const validation = field.validation ?? {};

	if ( validation.required ) {
		return true;
	}

	if ( validation.required_unless ) {
		return ! evaluate( validation.required_unless, values );
	}

	return false;
}

/**
 * Validate a single field.
 *
 * Validation never blocks saving – it only surfaces what is missing or
 * malformed, exactly as the settings page did before.
 *
 * @param {Object} field  Field to validate
 * @param {Object} values Current values of the field's group
 * @return {?string} Error message, or null if the field is valid
 */
export function getFieldError( field, values ) {
	const validation = field.validation ?? {};
	const value = values[ field.name ];

	if ( isEmpty( value ) ) {
		if ( isRequired( field, values ) ) {
			return validation.messages?.required ?? null;
		}

		return null;
	}

	if (
		validation.pattern &&
		! matchesPattern( validation.pattern, String( value ) )
	) {
		return validation.messages?.pattern ?? null;
	}

	return null;
}

/**
 * Validate all visible fields of a group.
 *
 * @param {Object[]} fields List of fields
 * @param {Object}   values Current values of the group
 * @return {Object} Error messages, keyed by field name
 */
export function getErrors( fields, values ) {
	const errors = {};

	for ( const field of fields ) {
		const error = getFieldError( field, values );

		if ( error ) {
			errors[ field.name ] = error;
		}
	}

	return errors;
}

/**
 * Strip every character a field does not accept.
 *
 * @param {Object} field Field the value belongs to
 * @param {string} value Value to sanitize
 * @return {string} Sanitized value
 */
export function sanitizeValue( field, value ) {
	const pattern = field.validation?.sanitize_pattern;

	if ( ! pattern || typeof value !== 'string' ) {
		return value;
	}

	try {
		return value.replace( new RegExp( pattern, 'g' ), '' );
	} catch {
		return value;
	}
}
