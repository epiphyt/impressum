import { __ } from '@wordpress/i18n';

const PARAM_PATTERN = /values\[([^\]]+)\]\[([^\]]+)\]/;

/**
 * Collect every error a response carries.
 *
 * A failed validation reports the offending parameter in the generic message
 * and what is wrong with it in the details, so the details are what matters.
 *
 * @param {Object} error Error of a request
 * @return {Object[]} List of errors
 */
function flatten( error ) {
	const collected = [];
	const walk = ( entry ) => {
		if ( ! entry || typeof entry !== 'object' ) {
			return;
		}

		if ( entry.message ) {
			collected.push( entry );
		}

		for ( const additional of entry.additional_errors ?? [] ) {
			walk( additional );
		}
	};

	for ( const detail of Object.values( error?.data?.details ?? {} ) ) {
		walk( detail );
	}

	if ( ! collected.length ) {
		// without details the parameter map still carries the messages
		for ( const message of Object.values( error?.data?.params ?? {} ) ) {
			if ( typeof message === 'string' ) {
				collected.push( { message } );
			}
		}
	}

	return collected;
}

/**
 * Get the errors that belong to a single setting.
 *
 * @param {Object} error Error of a request
 * @return {Object[]} List of errors with their group and setting name
 */
export function getFieldErrors( error ) {
	const fields = [];

	// settings rejected while writing are reported per setting, with a message
	// that is about the value rather than about the parameter carrying it
	for ( const entry of Object.values( error?.data?.fields ?? {} ) ) {
		if ( entry?.group && entry?.name ) {
			fields.push( {
				group: entry.group,
				message: entry.message,
				name: entry.name,
			} );
		}
	}

	if ( fields.length ) {
		return fields;
	}

	for ( const entry of flatten( error ) ) {
		// the parameter is reported in its own key, and otherwise named in the
		// message, which is the only place a parameter map mentions it
		const param = entry.data?.param ?? entry.message ?? '';
		const match = String( param ).match( PARAM_PATTERN );

		if ( ! match ) {
			continue;
		}

		fields.push( {
			group: match[ 1 ],
			message: entry.message,
			name: match[ 2 ],
		} );
	}

	return fields;
}

/**
 * Get the message shown when an error belongs to no setting in particular.
 *
 * @param {Object} error Error of a request
 * @return {string} Message
 */
export function getErrorMessage( error ) {
	const messages = flatten( error ).map( ( entry ) => entry.message );

	if ( messages.length ) {
		return [ ...new Set( messages ) ].join( ' ' );
	}

	return (
		error?.message ?? __( 'The settings could not be saved.', 'impressum' )
	);
}
