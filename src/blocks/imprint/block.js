/**
 * Gutenberg imprint block.
 */

// external dependencies
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
// eslint-disable-next-line import/no-extraneous-dependencies
import { page } from '@wordpress/icons';

import attributes from './attributes';
import edit from './edit';

// register the block
registerBlockType( 'impressum/imprint', {
	title: __( 'Imprint', 'impressum' ),
	icon: page,
	category: 'common',
	attributes,
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
