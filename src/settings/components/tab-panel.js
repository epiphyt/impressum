import FieldRow from './field-row';
import TabContent, { TabIntro } from './tab-content';
import { getActiveNotices } from '../lib/conditions';
import { isRequired } from '../lib/validate';

/**
 * A tab rendered by the settings app.
 *
 * @param {Object}   props                     Component properties
 * @param {Object}   props.tab                 Tab to render
 * @param {Object[]} props.fields              Visible fields of the tab
 * @param {Object}   props.values              Values of the tab's settings group
 * @param {Object}   props.effectiveValues     Values of the tab's settings group, falling back to the network wide ones
 * @param {Object}   props.fieldData           Custom field titles
 * @param {Object}   props.errors              Validation errors, keyed by group and name
 * @param {Object}   props.serverErrors        Errors the server reported, keyed by group and name
 * @param {Object}   props.touched             Fields that have lost focus, keyed by group and name
 * @param {boolean}  props.isSubmitted         Whether saving has been attempted
 * @param {Object}   props.payload             Settings payload
 * @param {Function} props.onChange            Value change handler
 * @param {Function} props.onCustomTitleChange Custom title change handler
 * @param {Function} props.onTouch             Handler called when a field loses focus
 * @param {Function} props.onLegacyInput       Handler called when server side rendered markup changes
 */
export default function SettingsTab( {
	tab,
	fields,
	values,
	effectiveValues,
	fieldData,
	errors,
	serverErrors,
	touched,
	isSubmitted,
	payload,
	onChange,
	onCustomTitleChange,
	onTouch,
	onLegacyInput,
} ) {
	const sections = tab.sections?.length
		? tab.sections
		: [ { fields: [], id: '', title: '' } ];

	/**
	 * Get the error shown for a field.
	 *
	 * A client side error is only shown once the field has been left or saving
	 * has been attempted, so that it does not appear while typing the value.
	 *
	 * @param {Object} field Field to get the error of
	 * @return {?string} Error message, or null if there is none to show
	 */
	const getError = ( field ) => {
		const key = `${ field.group }.${ field.name }`;

		if ( serverErrors[ key ] ) {
			return serverErrors[ key ];
		}

		return isSubmitted || touched[ key ] ? errors[ key ] ?? null : null;
	};

	return (
		<>
			<TabIntro tab={ tab } />

			{ sections.map( ( section ) => {
				const sectionFields = fields.filter(
					( field ) => ! section.id || field.section === section.id
				);

				if ( ! sectionFields.length ) {
					return null;
				}

				return (
					<div
						className="impressum-settings__section impressum-content-wrapper"
						key={ section.id }
					>
						{ section.title && <h2>{ section.title }</h2> }

						<table className="form-table" role="presentation">
							<tbody>
								{ sectionFields.map( ( field ) => (
									<FieldRow
										choices={ payload.choices }
										customTitle={
											fieldData[ field.name ]?.name ?? ''
										}
										error={ getError( field ) }
										field={ field }
										i18n={ payload.i18n }
										isNetwork={ payload.isNetwork }
										key={ field.key }
										notices={ getActiveNotices(
											field,
											effectiveValues
										) }
										onChange={ onChange }
										onCustomTitleChange={
											onCustomTitleChange
										}
										onLegacyInput={ onLegacyInput }
										onTouch={ onTouch }
										required={ isRequired(
											field,
											effectiveValues
										) }
										value={ values[ field.name ] }
									/>
								) ) }
							</tbody>
						</table>
					</div>
				);
			} ) }

			<TabContent tab={ tab } />

			{ tab.html && (
				<div dangerouslySetInnerHTML={ { __html: tab.html } } />
			) }
		</>
	);
}
