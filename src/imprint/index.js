/**
 * Gutenberg imprint block.
 */

// external dependencies
import { registerBlockType } from '@wordpress/blocks';
import { page } from '@wordpress/icons';

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
