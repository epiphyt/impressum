/* eslint-disable camelcase */
import { CheckboxControl, PanelBody } from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

/* global impressum_fields */
export default function SidebarControls( props ) {
	const { enabledFields, setAttributes } = props;
	const sortedEnabledFields = structuredClone( enabledFields ).sort();
	const supportedFields = Object.keys( impressum_fields.values )
		.map( ( key ) => {
			return impressum_fields.values[ key ].no_output ? null : key;
		} )
		.filter( Boolean )
		.sort();

	const onChangeEnabledField = ( key, value ) => {
		const newValue = structuredClone( enabledFields );

		if ( value ) {
			newValue.push( key );
		} else {
			const index = newValue.indexOf( key );

			if ( index !== -1 ) {
				newValue.splice( index, 1 );
			}
		}

		setAttributes( { enabledFields: newValue } );
	};

	const onChangeEnabledFieldAll = ( value ) => {
		if ( value ) {
			setAttributes( { enabledFields: [ 'all' ] } );
		} else {
			setAttributes( { enabledFields: [] } );
		}
	};

	return (
		<InspectorControls>
			<PanelBody title={ __( 'Enabled Fields', 'impressum' ) }>
				<div className="impressum__checkbox-select">
					<CheckboxControl
						key={ 'enabled-field-all' }
						label={ __( 'All', 'impressum' ) }
						checked={
							typeof enabledFields !== 'undefined' &&
							( enabledFields.indexOf( 'all' ) !== -1 ||
								Object.keys( impressum_fields.values )
									.length === enabledFields.length ||
								sortedEnabledFields.toString() ===
									supportedFields.toString() )
						}
						indeterminate={
							typeof enabledFields !== 'undefined' &&
							enabledFields.indexOf( 'all' ) === -1 &&
							enabledFields.length >= 1 &&
							enabledFields.length <
								Object.keys( impressum_fields.values ).length &&
							sortedEnabledFields.toString() !==
								supportedFields.toString()
						}
						value="all"
						onChange={ ( value ) =>
							onChangeEnabledFieldAll( value )
						}
					/>
					{ Object.keys( impressum_fields.values ).map(
						( fieldKey, index ) => {
							const field = impressum_fields.values[ fieldKey ];

							if ( field.no_output ) {
								return null;
							}

							return (
								<CheckboxControl
									key={ 'enabled-field-' + index }
									label={ field.custom_title || field.title }
									checked={
										enabledFields.indexOf( 'all' ) !== -1 ||
										enabledFields.indexOf( fieldKey ) !== -1
									}
									value={ fieldKey }
									onChange={ ( value ) =>
										onChangeEnabledField( fieldKey, value )
									}
								/>
							);
						}
					) }
				</div>
			</PanelBody>
		</InspectorControls>
	);
}
