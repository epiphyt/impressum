import { CheckboxControl } from '@wordpress/components';

/**
 * A checkbox field.
 *
 * The stored value mirrors what the classic form submitted: '1' when checked
 * and an empty string when not, so that an unchecked box actually clears the
 * option instead of leaving the previous value in place.
 *
 * @param {Object}   props          Component properties
 * @param {Object}   props.field    Field to render
 * @param {*}        props.value    Current value
 * @param {Function} props.onChange Value change handler
 * @param {string}   props.id       Identifier of the input
 */
export default function CheckboxField( { field, value, onChange, id } ) {
	return (
		<CheckboxControl
			__nextHasNoMarginBottom
			checked={ !! value && value !== '0' }
			id={ id }
			label={ field.label || undefined }
			onChange={ ( checked ) => onChange( checked ? '1' : '' ) }
		/>
	);
}
