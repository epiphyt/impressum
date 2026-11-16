/**
 * Evaluation of the declarative conditions shipped with every field.
 *
 * This is a port of \epiphyt\Impressum\settings\Validator::evaluate(). Both have
 * to agree, so any change here needs the same change there.
 */

/**
 * Compare two values.
 *
 * Option values come back from the database as strings, so scalars are compared
 * as strings to avoid type juggling surprises.
 *
 * @param {*} actual   Actual value
 * @param {*} expected Expected value
 * @return {boolean} Whether both values are considered equal
 */
function isEqual( actual, expected ) {
	if ( actual === null || actual === undefined ) {
		return actual === expected;
	}

	if ( typeof actual === 'object' || typeof expected === 'object' ) {
		return actual === expected;
	}

	return String( actual ) === String( expected );
}

/**
 * Check whether a value is considered empty.
 *
 * Mirrors the semantics of empty() in PHP, where '0' counts as empty.
 *
 * @param {*} value Value to check
 * @return {boolean} Whether the value is empty
 */
export function isEmpty( value ) {
	if ( Array.isArray( value ) ) {
		return value.length === 0;
	}

	return (
		value === null ||
		value === undefined ||
		value === false ||
		value === 0 ||
		value === '' ||
		value === '0'
	);
}

/**
 * Match a value against a delimiter-less pattern.
 *
 * @param {string} pattern Pattern without delimiters
 * @param {string} value   Value to match
 * @return {boolean} Whether the value matches the pattern
 */
export function matchesPattern( pattern, value ) {
	if ( ! pattern ) {
		return true;
	}

	try {
		return new RegExp( pattern ).test( value );
	} catch {
		// an invalid pattern must never block saving
		return true;
	}
}

/**
 * Evaluate a condition against a set of values.
 *
 * @param {Object} condition Condition to evaluate
 * @param {Object} values    Values to evaluate the condition against
 * @return {boolean} Whether the condition matches
 */
export function evaluate( condition, values ) {
	if ( ! condition || typeof condition !== 'object' ) {
		return true;
	}

	if ( condition.all !== undefined ) {
		return Object.values( condition.all ).every( ( sub ) =>
			evaluate( sub, values )
		);
	}

	if ( condition.any !== undefined ) {
		return Object.values( condition.any ).some( ( sub ) =>
			evaluate( sub, values )
		);
	}

	if ( ! condition.field ) {
		return true;
	}

	const actual = values[ condition.field ] ?? null;
	const expected = condition.value ?? null;

	switch ( condition.operator ) {
		case 'empty':
		case 'falsy':
			return isEmpty( actual );
		case 'in':
			return [].concat( expected ?? [] ).some( ( one ) => isEqual( actual, one ) );
		case 'is':
			return isEqual( actual, expected );
		case 'is_not':
			return ! isEqual( actual, expected );
		case 'matches':
			return matchesPattern( String( expected ), String( actual ?? '' ) );
		case 'not_in':
			return ! [].concat( expected ?? [] ).some( ( one ) =>
				isEqual( actual, one )
			);
		default:
			return ! isEmpty( actual );
	}
}

/**
 * Check whether a field is currently visible.
 *
 * @param {Object} field  Field to check
 * @param {Object} values Values to evaluate the conditions against
 * @return {boolean} Whether the field is visible
 */
export function isVisible( field, values ) {
	if ( ! field.conditions?.visible ) {
		return true;
	}

	return evaluate( field.conditions.visible, values );
}

/**
 * Get all notices of a field that currently apply.
 *
 * @param {Object} field  Field to get the notices of
 * @param {Object} values Values to evaluate the notice conditions against
 * @return {Array} List of active notices
 */
export function getActiveNotices( field, values ) {
	return ( field.notices ?? [] ).filter(
		( notice ) => ! notice.when || evaluate( notice.when, values )
	);
}
