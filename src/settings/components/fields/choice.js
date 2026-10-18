import { SelectControl } from '@wordpress/components';

/**
 * Get the options of a field.
 *
 * Long lists are shipped once in the payload and referenced by the fields that
 * use them, so they are resolved here rather than per field.
 *
 * @param {Object} field   Field to get the options of
 * @param {Object} choices Shared choice lists
 * @return {Array} List of options
 */
function getOptions( field, choices ) {
	if ( field.choicesRef ) {
		return choices?.[ field.choicesRef ] ?? [];
	}

	return field.options ?? [];
}

/**
 * A select field.
 *
 * @param {Object}   props          Component properties
 * @param {Object}   props.field    Field to render
 * @param {*}        props.value    Current value
 * @param {Function} props.onChange Value change handler
 * @param {string}   props.id       Identifier of the input
 * @param {Object}   props.choices  Shared choice lists
 * @param {Object}   props.i18n     Shared strings
 */
export default function ChoiceField( {
	field,
	value,
	onChange,
	id,
	choices,
	i18n,
} ) {
	// country and legal entity use a different placeholder than a plain select
	const placeholder =
		field.choicesRef === 'country' || field.choicesRef === 'legalEntity'
			? i18n.selectPlaceholderShort
			: i18n.selectPlaceholder;
	const options = [
		{ label: placeholder, value: '' },
		...getOptions( field, choices ).map( ( option ) => ( {
			label: option.label,
			value: String( option.value ),
		} ) ),
	];
	const current = value ?? '';

	return (
		<SelectControl
			__next40pxDefaultSize
			__nextHasNoMarginBottom
			className="impressum-settings__control"
			id={ id }
			onChange={ onChange }
			options={ options }
			value={ String( current ) }
		/>
	);
}
