import { TextareaControl } from '@wordpress/components';

/**
 * A multi line text field.
 *
 * @param {Object}   props          Component properties
 * @param {Object}   props.field    Field to render
 * @param {*}        props.value    Current value
 * @param {Function} props.onChange Value change handler
 * @param {string}   props.id       Identifier of the input
 */
export default function TextareaField( { field, value, onChange, id } ) {
	return (
		<TextareaControl
			__nextHasNoMarginBottom
			className="impressum-settings__control"
			id={ id }
			onChange={ onChange }
			placeholder={ field.placeholder || undefined }
			rows={ field.rows ?? 10 }
			value={ value ?? '' }
		/>
	);
}
