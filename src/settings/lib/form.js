const SUBMITTED_OPTIONS = 'impressum_submitted_options[]';

/**
 * Check whether a form control name belongs to an input name.
 *
 * @param {string} name      Name of the form control
 * @param {string} inputName Input name of a field
 * @return {boolean} Whether the name belongs to the field
 */
function belongsTo( name, inputName ) {
	return name === inputName || name.startsWith( `${ inputName }[` );
}

/**
 * Get the value of a field rendered as markup from the form it is part of.
 *
 * The markup is not controlled by the settings app, so its current value only
 * lives in the form. Names like `name[]` are read as a list and names like
 * `name[key]` as an object.
 *
 * @param {HTMLFormElement} form      Form containing the field
 * @param {string}          inputName Input name of the field
 * @return {*} Current value, or undefined if the form contains no control of the field
 */
export function getLegacyValue( form, inputName ) {
	if ( ! form || ! inputName ) {
		return undefined;
	}

	const hasControl = Array.from( form.elements ).some(
		( element ) => element.name && belongsTo( element.name, inputName )
	);

	if ( ! hasControl ) {
		return undefined;
	}

	const prefix = `${ inputName }[`;
	// an unchecked checkbox is not submitted, which clears the value
	let value = '';

	for ( const [ name, entry ] of new FormData( form ) ) {
		if ( typeof entry !== 'string' || ! belongsTo( name, inputName ) ) {
			continue;
		}

		if ( name === inputName ) {
			value = entry;

			continue;
		}

		const key = name.slice( prefix.length, name.indexOf( ']', prefix.length ) );

		if ( key === '' ) {
			value = Array.isArray( value ) ? [ ...value, entry ] : [ entry ];
		} else {
			value =
				value && typeof value === 'object' && ! Array.isArray( value )
					? { ...value, [ key ]: entry }
					: { [ key ]: entry };
		}
	}

	return value;
}

/**
 * Get the names of all options a form contains controls of.
 *
 * @param {HTMLFormElement} form Form to get the option names of
 * @return {string[]} List of option names
 */
export function getOptionNames( form ) {
	const names = new Set();

	for ( const element of form.elements ) {
		const match = element.name?.match( /^([^[\]]+)\[/ );

		if ( match && element.name !== SUBMITTED_OPTIONS ) {
			names.add( match[ 1 ] );
		}
	}

	return [ ...names ];
}

/**
 * List every option a form contains controls of in the form itself.
 *
 * An option whose controls are all unchecked checkboxes is missing in the
 * submitted data, which would otherwise be indistinguishable from an option
 * that is not part of the submitted tab at all.
 *
 * @param {HTMLFormElement} form      Form to submit
 * @param {HTMLElement}     container Element to add the list to
 */
export function addSubmittedOptions( form, container ) {
	if ( ! form || ! container ) {
		return;
	}

	container.replaceChildren(
		...getOptionNames( form ).map( ( name ) => {
			const input = document.createElement( 'input' );

			input.name = SUBMITTED_OPTIONS;
			input.type = 'hidden';
			input.value = name;

			return input;
		} )
	);
}
