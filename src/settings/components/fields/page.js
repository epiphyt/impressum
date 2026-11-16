import { ComboboxControl, SelectControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

import { useDebounce } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

import InlineNotice from '../inline-notice';

/**
 * A page selection field.
 *
 * Sites with a manageable number of pages get the full list in the payload.
 * Above that the list is left out and pages are searched for on demand.
 *
 * @param {Object}   props           Component properties
 * @param {Object}   props.field     Field to render
 * @param {*}        props.value     Current value
 * @param {Function} props.onChange  Value change handler
 * @param {string}   props.id        Identifier of the input
 * @param {Object}   props.choices   Shared choice lists
 * @param {Object}   props.i18n      Shared strings
 * @param {boolean}  props.isNetwork Whether the network wide settings are edited
 * @param {string}   props.label     Title of the field
 */
export default function PageField( {
	field,
	value,
	onChange,
	id,
	choices,
	i18n,
	isNetwork,
	label,
} ) {
	const pages = choices?.pages ?? null;
	const [ searched, setSearched ] = useState( [] );
	// the selected page has to stay an option, otherwise it is not displayed
	const [ selected, setSelected ] = useState( () =>
		value && field.valueLabel
			? { label: field.valueLabel, value: String( value ) }
			: null
	);
	const search = useDebounce( ( term ) => {
		if ( ! term ) {
			setSearched( [] );

			return;
		}

		apiFetch( {
			path: addQueryArgs( '/wp/v2/pages', {
				_fields: 'id,title',
				context: 'edit',
				per_page: 20,
				search: term,
				status: 'publish,draft',
			} ),
		} )
			.then( ( results ) =>
				setSearched(
					results.map( ( page ) => ( {
						label:
							page.title?.raw ||
							page.title?.rendered ||
							`#${ page.id }`,
						value: String( page.id ),
					} ) )
				)
			)
			.catch( () => setSearched( [] ) );
	}, 300 );

	if ( isNetwork ) {
		return (
			<InlineNotice status="info">{ i18n.networkUnavailable }</InlineNotice>
		);
	}

	if ( pages === null ) {
		const current = value ? String( value ) : null;
		const options =
			selected &&
			selected.value === current &&
			! searched.some( ( option ) => option.value === current )
				? [ selected, ...searched ]
				: searched;

		return (
			<ComboboxControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				allowReset
				hideLabelFromVision
				label={ label }
				onChange={ ( next ) => {
					setSelected(
						options.find( ( option ) => option.value === next ) ??
							null
					);
					onChange( next ?? '' );
				} }
				onFilterValueChange={ search }
				options={ options }
				value={ current }
			/>
		);
	}

	if ( pages.length === 0 ) {
		return <InlineNotice status="warning">{ i18n.noPages }</InlineNotice>;
	}

	return (
		<SelectControl
			__next40pxDefaultSize
			__nextHasNoMarginBottom
			className="impressum-settings__control"
			id={ id }
			onChange={ onChange }
			options={ [
				{ label: i18n.selectPlaceholder, value: '' },
				...pages.map( ( page ) => ( {
					label: page.label || __( '(no title)', 'impressum' ),
					value: String( page.value ),
				} ) ),
			] }
			value={ value ? String( value ) : '' }
		/>
	);
}
