import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

const PATH = '/impressum/v1/settings';

/**
 * Get the settings payload.
 *
 * @param {Object}  args           Request arguments
 * @param {boolean} args.isNetwork Whether to read the network wide settings
 * @return {Promise<Object>} Settings payload
 */
export function fetchSettings( { isNetwork = false } = {} ) {
	return apiFetch( {
		path: addQueryArgs( PATH, { network: isNetwork } ),
	} );
}

/**
 * Write the settings.
 *
 * @param {Object}  args           Request arguments
 * @param {Object}  args.values    Values to write, keyed by settings group
 * @param {Object}  args.fieldData Custom field titles
 * @param {boolean} args.isNetwork Whether to write the network wide settings
 * @return {Promise<Object>} Updated settings payload
 */
export function saveSettings( { values, fieldData, isNetwork = false } ) {
	return apiFetch( {
		data: {
			field_data: fieldData,
			network: isNetwork,
			values,
		},
		method: 'POST',
		path: PATH,
	} );
}

/**
 * Validate values without writing them.
 *
 * This is what lets extensions drive the client side validation through their
 * existing PHP filters without shipping any JavaScript.
 *
 * @param {Object}  args           Request arguments
 * @param {Object}  args.values    Values to validate, keyed by settings group
 * @param {boolean} args.isNetwork Whether the network wide settings are edited
 * @return {Promise<Object>} Validation result
 */
export function validateSettings( { values, isNetwork = false } ) {
	return apiFetch( {
		data: {
			network: isNetwork,
			values,
		},
		method: 'POST',
		path: `${ PATH }/validate`,
	} );
}
