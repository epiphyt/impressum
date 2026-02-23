/* eslint-disable camelcase */
import { CheckboxControl, PanelBody } from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

/* global impressumImprintBlock */
export default function SidebarControls( props ) {
	const { enabledFields, setAttributes } = props;
	const sortedEnabledFields = structuredClone( enabledFields ).sort();
	const supportedFields = Object.keys( impressumImprintBlock.values )
		.map( ( key ) => {
			return impressumImprintBlock.values[ key ].hide_output ? null : key;
		} )
		.filter( Boolean )
		.sort();

	const onChangeEnabledField = ( key, value ) => {
		let newValue = structuredClone( enabledFields );

		if ( value ) {
			newValue.push( key );
		} else {
			if ( newValue.indexOf( 'all' ) > -1 ) {
				newValue = supportedFields;
			}

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
								Object.keys( impressumImprintBlock.values )
									.length === enabledFields.length ||
								sortedEnabledFields.toString() ===
									supportedFields.toString() )
						}
						indeterminate={
							typeof enabledFields !== 'undefined' &&
							enabledFields.indexOf( 'all' ) === -1 &&
							enabledFields.length >= 1 &&
							enabledFields.length <
								Object.keys( impressumImprintBlock.values )
									.length &&
							sortedEnabledFields.toString() !==
								supportedFields.toString()
						}
						value="all"
						onChange={ ( value ) =>
							onChangeEnabledFieldAll( value )
						}
					/>
					{ Object.keys( impressumImprintBlock.values ).map(
						( fieldKey, index ) => {
							const field =
								impressumImprintBlock.values[ fieldKey ];

							if ( field.hide_output ) {
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
