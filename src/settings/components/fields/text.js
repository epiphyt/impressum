import { TextControl } from '@wordpress/components';

import { sanitizeValue } from '../../lib/validate';

const INPUT_TYPES = {
	email: 'email',
	phone: 'tel',
	text: 'text',
};

/**
 * A single line text field.
 *
 * @param {Object}   props          Component properties
 * @param {Object}   props.field    Field to render
 * @param {*}        props.value    Current value
 * @param {Function} props.onChange Value change handler
 * @param {string}   props.id       Identifier of the input
 */
export default function TextField( { field, value, onChange, id } ) {
	return (
		<TextControl
			__next40pxDefaultSize
			__nextHasNoMarginBottom
			autoComplete={ field.autocomplete || undefined }
			className="impressum-settings__control"
			id={ id }
			onChange={ ( next ) => onChange( sanitizeValue( field, next ) ) }
			placeholder={ field.placeholder || undefined }
			type={ INPUT_TYPES[ field.type ] ?? 'text' }
			value={ value ?? '' }
		/>
	);
}
