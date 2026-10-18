import { Button, TextControl, VisuallyHidden } from '@wordpress/components';
import {
	memo,
	useCallback,
	useEffect,
	useRef,
	useState,
} from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { pencil } from '@wordpress/icons';

import { getFieldComponent, getLabelMode } from './fields';
import InlineNotice from './inline-notice';
import LegacyField from './legacy-field';
import useFieldAria from './use-field-aria';

const NO_NOTICES = [];

/**
 * The title of a field.
 *
 * @param {Object}  props          Component properties
 * @param {string}  props.mode     Label mode, see getLabelMode()
 * @param {string}  props.id       Identifier of the field's control
 * @param {Object}  props.field    Field to render the title of
 * @param {string}  props.title    Title to render
 * @param {boolean} props.isHidden Whether the title is only available to screen readers
 */
function FieldTitle( { mode, id, field, title, isHidden } ) {
	if ( mode === 'self' ) {
		// the control carries the title itself, so it is not read twice
		return isHidden ? null : <span aria-hidden="true">{ title }</span>;
	}

	if ( mode === 'none' ) {
		return isHidden ? null : <span>{ title }</span>;
	}

	const htmlFor = mode === 'legacy' ? field.labelFor : id;

	if ( isHidden ) {
		return (
			<VisuallyHidden as="label" htmlFor={ htmlFor }>
				{ title }
			</VisuallyHidden>
		);
	}

	return <label htmlFor={ htmlFor }>{ title }</label>;
}

/**
 * Check whether the properties of a row are equal.
 *
 * The notices are evaluated on every render, so they are compared by the
 * notices they contain rather than by identity.
 *
 * @param {Object} previous Previous properties
 * @param {Object} next     Next properties
 * @return {boolean} Whether the properties are equal
 */
function arePropsEqual( previous, next ) {
	const keys = Object.keys( next );

	if ( keys.length !== Object.keys( previous ).length ) {
		return false;
	}

	return keys.every( ( key ) => {
		if ( key === 'notices' ) {
			const ids = ( notices ) =>
				( notices ?? [] ).map( ( notice ) => notice.id ).join( ' ' );

			return ids( previous.notices ) === ids( next.notices );
		}

		return Object.is( previous[ key ], next[ key ] );
	} );
}

/**
 * A single row of the settings table.
 *
 * @param {Object}   props                     Component properties
 * @param {Object}   props.field               Field to render
 * @param {*}        props.value               Current value
 * @param {Function} props.onChange            Value change handler, called with the field and the value
 * @param {?string}  props.error               Validation error of this field
 * @param {string}   props.customTitle         Custom title of this field
 * @param {Function} props.onCustomTitleChange Custom title change handler, called with the field and the title
 * @param {Function} props.onTouch             Handler called with the field when it loses focus
 * @param {Object}   props.choices             Shared choice lists
 * @param {Object}   props.i18n                Shared strings
 * @param {boolean}  props.isNetwork           Whether the network wide settings are edited
 * @param {boolean}  props.required            Whether the field is currently required
 * @param {Object[]} props.notices             Notices of the field that currently apply
 * @param {Function} props.onLegacyInput       Handler called when the server side rendered markup changes
 */
function FieldRow( {
	field,
	value,
	onChange,
	error,
	customTitle,
	onCustomTitleChange,
	onTouch,
	choices,
	i18n,
	isNetwork,
	required = false,
	notices = NO_NOTICES,
	onLegacyInput,
} ) {
	const [ isEditingTitle, setIsEditingTitle ] = useState( false );
	const editButtonRef = useRef( null );
	const shouldRestoreFocus = useRef( false );
	const Component = getFieldComponent( field );
	const labelMode = getLabelMode( field, { choices, isNetwork } );
	const id = `impressum-${ field.group }-${ field.name }`;
	const title = customTitle || field.title;
	const ids = {
		description: field.description ? `${ id }-description` : null,
		descriptionHtml: field.descriptionHtml
			? `${ id }-description-html`
			: null,
		error: error ? `${ id }-error` : null,
		notices: notices.length ? `${ id }-notices` : null,
		required: required ? `${ id }-required` : null,
	};
	const controlsRef = useFieldAria( {
		describedBy: [
			ids.error,
			ids.required,
			ids.description,
			ids.descriptionHtml,
			ids.notices,
		],
		invalid: !! error,
		required,
	} );

	useEffect( () => {
		if ( ! isEditingTitle && shouldRestoreFocus.current ) {
			shouldRestoreFocus.current = false;
			editButtonRef.current?.focus();
		}
	}, [ isEditingTitle ] );

	const onFieldChange = useCallback(
		( next ) => onChange( field, next ),
		[ field, onChange ]
	);
	const onTitleChange = useCallback(
		( next ) => onCustomTitleChange( field, next ),
		[ field, onCustomTitleChange ]
	);
	const onTitleKeyDown = ( event ) => {
		if ( event.key !== 'Enter' && event.key !== 'Escape' ) {
			return;
		}

		// the title input is part of the form, which must not be submitted
		event.preventDefault();
		shouldRestoreFocus.current = true;
		setIsEditingTitle( false );
	};

	return (
		<tr
			className={ `impressum-settings__row ${ field.cssClass ?? '' }` }
			data-impressum-field={ `${ field.group }.${ field.name }` }
		>
			<th scope="row">
				<FieldTitle
					field={ field }
					id={ id }
					isHidden={ isEditingTitle }
					mode={ labelMode }
					title={ title }
				/>
				{ isEditingTitle && (
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						autoFocus
						hideLabelFromVision
						label={ sprintf(
							/* translators: field title */
							__( 'Title of the field “%s”', 'impressum' ),
							field.title
						) }
						onBlur={ () => setIsEditingTitle( false ) }
						onChange={ onTitleChange }
						onKeyDown={ onTitleKeyDown }
						value={ customTitle || field.title }
					/>
				) }
				{ field.supportsCustomTitle && ! isEditingTitle && (
					<Button
						className="impressum-settings__rename"
						icon={ pencil }
						label={ sprintf(
							/* translators: field title */
							__( 'Edit the title of the field “%s”', 'impressum' ),
							title
						) }
						onClick={ () => setIsEditingTitle( true ) }
						ref={ editButtonRef }
						size="small"
					/>
				) }
				{ /* keeps the custom title in the classic form submission */ }
				{ field.supportsCustomTitle && customTitle && (
					<input
						name={ `impressum_field_data[${ field.name }][name]` }
						type="hidden"
						value={ customTitle }
					/>
				) }
			</th>
			<td onBlur={ () => onTouch?.( field ) } ref={ controlsRef }>
				{ Component ? (
					<Component
						choices={ choices }
						field={ field }
						i18n={ i18n }
						id={ id }
						isNetwork={ isNetwork }
						label={ title }
						onChange={ onFieldChange }
						value={ value }
					/>
				) : (
					<LegacyField field={ field } onInput={ onLegacyInput } />
				) }

				{ error && (
					<InlineNotice
						className="impressum-settings__error"
						id={ ids.error }
						status="error"
					>
						{ error }
					</InlineNotice>
				) }

				{ required && (
					<p
						className="description impressum__description impressum-required-field"
						id={ ids.required }
					>
						{ i18n.requiredNotice }
					</p>
				) }

				{ field.description && (
					<p
						className="description impressum__description"
						id={ ids.description }
					>
						{ field.description }
					</p>
				) }

				{ /* output of the per field action, which extensions still use */ }
				{ field.descriptionHtml && (
					<div
						dangerouslySetInnerHTML={ {
							__html: field.descriptionHtml,
						} }
						id={ ids.descriptionHtml }
					/>
				) }

				{ notices.length > 0 && (
					<div id={ ids.notices }>
						{ notices.map( ( notice ) => (
							<InlineNotice
								key={ notice.id }
								status={ notice.status ?? 'warning' }
							>
								{ notice.text }
							</InlineNotice>
						) ) }
					</div>
				) }
			</td>
		</tr>
	);
}

export default memo( FieldRow, arePropsEqual );
