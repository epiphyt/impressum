/* eslint-disable camelcase */
// external dependencies
import { PanelBody, Placeholder, SelectControl } from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
// eslint-disable-next-line import/no-extraneous-dependencies
import { page } from '@wordpress/icons';

// internal dependencies
import getFields from './utils';

/* global impressum_fields */
const SidebarControls = ( props ) => {
	const { enabledFields, setAttributes } = props;
	let options = Object.keys( impressum_fields.fields ).map( ( key ) => {
		if (
			typeof impressum_fields.fields[ key ].title === 'undefined' ||
			! impressum_fields.fields[ key ].title ||
			impressum_fields.fields[ key ].no_output
		) {
			return null;
		}

		return {
			value: key,
			label: impressum_fields.fields[ key ].title,
		};
	} );
	options = options.filter( ( element ) => element !== null );

	return (
		<InspectorControls>
			<PanelBody title={ __( 'Fields', 'impressum' ) }>
				<div className="impressum-select-multiple">
					<SelectControl
						help={ __(
							'(Un-)Select multiple fields with STRG/CMD.',
							'impressum'
						) }
						label={ __( 'Enabled Fields', 'impressum' ) }
						multiple
						onChange={ ( newValue ) =>
							setAttributes( { enabledFields: newValue } )
						}
						options={ options }
						size="10"
						value={ enabledFields }
					/>
				</div>
			</PanelBody>
		</InspectorControls>
	);
};

const ImprintEdit = ( props ) => {
	const {
		attributes: { enabledFields },
		className,
		setAttributes,
	} = props;
	const fields = getFields( enabledFields, className ).filter( Boolean );

	return (
		<div className={ className }>
			<SidebarControls
				enabledFields={ enabledFields }
				setAttributes={ setAttributes }
			/>
			{ fields.length ? (
				fields
			) : (
				<Placeholder
					icon={ page }
					label={ __( 'Imprint', 'impressum' ) }
				>
					{ __(
						'There is currently no imprint data set.',
						'impressum'
					) }
				</Placeholder>
			) }
		</div>
	);
};

export default ImprintEdit;
