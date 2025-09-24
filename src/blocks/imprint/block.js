/**
 * Gutenberg imprint block.
 */

// external dependencies
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { page } from '@wordpress/icons'; // eslint-disable-line import/no-extraneous-dependencies

// internal dependencies
import attributes from './attributes';
import edit from './edit';
import deprecated from './deprecated';

// register the block
registerBlockType( 'impressum/imprint', {
	title: __( 'Imprint', 'impressum' ),
	icon: page,
	category: 'common',
	attributes,
	deprecated,
	edit,
	save: () => null,
	keywords: [ __( 'legal', 'impressum' ), __( 'information', 'impressum' ) ],
	styles: [
		{
			name: 'default',
			label: __( 'Default', 'impressum' ),
			isDefault: true,
		},
		{
			name: 'no-title',
			label: __( 'Without Titles', 'impressum' ),
		},
	],
	supports: {
		html: false,
	},
} );
