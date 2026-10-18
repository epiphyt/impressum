import { applyFilters } from '@wordpress/hooks';

import CheckboxField from './checkbox';
import ChoiceField from './choice';
import ControlField from './control';
import PageField from './page';
import TextField from './text';
import TextareaField from './textarea';

/**
 * The field types the settings app renders itself.
 *
 * Everything not listed here falls back to the server side rendered markup,
 * which is how third party field callbacks keep working.
 */
const FIELD_COMPONENTS = {
	checkbox: CheckboxField,
	country: ChoiceField,
	email: TextField,
	legal_entity: ChoiceField,
	page: PageField,
	phone: TextField,
	select: ChoiceField,
	text: TextField,
	textarea: TextareaField,
};

/**
 * Get the component rendering a field.
 *
 * A field describing the control it wants is rendered from that description,
 * which takes precedence over the field type: it is the more specific
 * statement of the two.
 *
 * @param {Object} field Field to render
 * @return {?Function} Field component, or null if the field is not rendered natively
 */
export function getFieldComponent( field ) {
	if ( field.control ) {
		return ControlField;
	}

	return getComponentForType( field.type );
}

/**
 * Get how the title of a field labels its control.
 *
 * - 'label': the title labels the control with the field's identifier
 * - 'legacy': the title labels the control of the server side rendered markup
 * - 'self': the control cannot take the identifier and labels itself
 * - 'none': there is no control to label
 *
 * @param {Object}  field             Field to render
 * @param {Object}  context           Render context
 * @param {Object}  context.choices   Shared choice lists
 * @param {boolean} context.isNetwork Whether the network wide settings are edited
 * @return {string} Label mode
 */
export function getLabelMode( field, { choices, isNetwork } ) {
	const Component = getFieldComponent( field );

	if ( ! Component ) {
		return field.legacyHtml ? 'legacy' : 'none';
	}

	// a radio group is labeled by its legend
	if ( field.control?.type === 'radio' ) {
		return 'self';
	}

	if ( Component === PageField ) {
		const pages = choices?.pages ?? null;

		if ( isNetwork || pages?.length === 0 ) {
			return 'none';
		}

		// the search field does not take an identifier
		if ( pages === null ) {
			return 'self';
		}
	}

	return 'label';
}

/**
 * Get the component rendering a field type.
 *
 * @param {string} type Field type
 * @return {?Function} Field component, or null if the type is not rendered natively
 */
export function getComponentForType( type ) {
	/**
	 * Filter the components rendering the field types.
	 *
	 * @param {Object} components Field components, keyed by field type
	 */
	const components = applyFilters(
		'impressum.settings.fieldTypes',
		FIELD_COMPONENTS
	);

	return components[ type ] ?? null;
}
