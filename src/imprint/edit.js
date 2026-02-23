/* eslint-disable camelcase */
// external dependencies
import { useBlockProps } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { page } from '@wordpress/icons'; // eslint-disable-line import/no-extraneous-dependencies

import SidebarControls from './controls';
import { getFieldsByName } from './utils';

/* global impressumImprintBlock */
const ImprintEdit = ( props ) => {
	const {
		attributes: { enabledFields },
		setAttributes,
	} = props;
	const blockProps = useBlockProps();
	let printableFields = enabledFields;

	if ( enabledFields.indexOf( 'all' ) !== -1 ) {
		printableFields = Object.keys( impressumImprintBlock.values )
			.map( ( key ) =>
				impressumImprintBlock.values[ key ].hide_output ? null : key
			)
			.filter( Boolean );
	}

	const fields = getFieldsByName(
		printableFields,
		blockProps.className,
		setAttributes
	);

	return (
		<div { ...blockProps }>
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
