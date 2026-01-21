/**
 * Gutenberg imprint block.
 */

// external dependencies
import { registerBlockType } from '@wordpress/blocks'; // eslint-disable-line import/no-extraneous-dependencies
import { page } from '@wordpress/icons'; // eslint-disable-line import/no-extraneous-dependencies

// internal dependencies
import deprecated from './deprecated';
import edit from './edit';
import meta from './block.json';

import './editor.scss';

// register the block
registerBlockType( meta, {
	icon: page,
	deprecated,
	edit,
	save: () => null,
} );
