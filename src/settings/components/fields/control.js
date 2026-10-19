import {
	CheckboxControl,
	RadioControl,
	SelectControl,
	TextControl,
	TextareaControl,
} from '@wordpress/components';

import InlineNotice from '../inline-notice';
import { sanitizeValue } from '../../lib/validate';

const INPUT_TYPES = {
	number: 'number',
	password: 'password',
	text: 'text',
	url: 'url',
};

/**
 * A field described by its callback rather than rendered by it.
 *
 * This is what lets a field type registered in PHP look exactly like a built-in
 * one: the callback describes the control it wants, and it is rendered here.
 *
 * @param {Object}   props          Component properties
 * @param {Object}   props.field    Field to render
 * @param {*}        props.value    Current value
 * @param {Function} props.onChange Value change handler
 * @param {string}   props.id       Identifier of the input
 * @param {Object}   props.i18n     Shared strings
 * @param {string}   props.label    Title of the field
 */
export default function ControlField( {
	field,
	value,
	onChange,
	id,
	i18n,
	label,
} ) {
	const control = field.control ?? {};
	const options = ( control.options ?? [] ).map( ( option ) => ( {
		label: option.label,
		value: String( option.value ),
	} ) );
	const disabled = !! control.disabled;
	const current = value ?? control.default ?? '';

	const notice = control.notice ? (
		<InlineNotice status={ control.notice.status ?? 'info' }>
			{ control.notice.text }
		</InlineNotice>
	) : null;

	let element;

	switch ( control.type ) {
		case 'checkbox':
			element = (
				<CheckboxControl
					checked={ !! current && current !== '0' }
					disabled={ disabled }
					id={ id }
					label={ control.label || field.label || undefined }
					onChange={ ( checked ) => onChange( checked ? '1' : '' ) }
				/>
			);
			break;

		case 'radio':
			element = (
				<RadioControl
					hideLabelFromVision
					label={ label }
					onChange={ onChange }
					options={ options }
					selected={ String( current ) }
				/>
			);
			break;

		case 'select':
			element = (
				<SelectControl
					__next40pxDefaultSize
					className="impressum-settings__control"
					disabled={ disabled }
					id={ id }
					multiple={ !! control.multiple }
					onChange={ onChange }
					options={
						control.multiple
							? options
							: [
									{
										label: i18n.selectPlaceholder,
										value: '',
									},
									...options,
							  ]
					}
					value={
						control.multiple
							? [].concat( current ?? [] ).map( String )
							: String( current )
					}
				/>
			);
			break;

		case 'textarea':
			element = (
				<TextareaControl
					className="impressum-settings__control"
					disabled={ disabled }
					id={ id }
					onChange={ onChange }
					placeholder={ field.placeholder || undefined }
					rows={ control.rows ?? field.rows ?? 10 }
					value={ current }
				/>
			);
			break;

		default:
			element = (
				<TextControl
					__next40pxDefaultSize
					className="impressum-settings__control"
					disabled={ disabled }
					id={ id }
					max={ control.max }
					min={ control.min }
					onChange={ ( next ) =>
						onChange( sanitizeValue( field, next ) )
					}
					placeholder={ field.placeholder || undefined }
					step={ control.step }
					type={ INPUT_TYPES[ control.type ] ?? 'text' }
					value={ current }
				/>
			);
	}

	return (
		<>
			<div className="impressum-settings__control-wrapper">
				{ element }
				{ control.suffix && (
					<span className="impressum-settings__suffix">
						{ control.suffix }
					</span>
				) }
			</div>
			{ notice }
		</>
	);
}
