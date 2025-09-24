/* eslint-disable camelcase */
// external dependencies
import { Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { page } from '@wordpress/icons'; // eslint-disable-line import/no-extraneous-dependencies

import SidebarControls from './controls';
import { getFieldsByName } from './utils';

/* global impressum_fields */
const ImprintEdit = ( props ) => {
	const {
		attributes: { enabledFields },
		className,
		setAttributes,
	} = props;
	let printableFields = enabledFields;

	if ( enabledFields.indexOf( 'all' ) !== -1 ) {
		printableFields = Object.keys( impressum_fields.values )
			.map( ( key ) =>
				impressum_fields.values[ key ].no_output ? null : key
			)
			.filter( Boolean );
	}

	const fields = getFieldsByName( printableFields, className, setAttributes );

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
						'There is currently no imprint data set or activated.',
						'impressum'
					) }
				</Placeholder>
			) }
		</div>
	);
};

export default ImprintEdit;
