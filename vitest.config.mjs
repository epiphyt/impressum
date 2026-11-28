import react from '@vitejs/plugin-react-swc';
import { defineConfig } from 'vitest/config';

export default defineConfig( {
	plugins: [
		react( {
			// the source keeps JSX in .js files, which the build handles via Babel
			parserConfig( id ) {
				if ( id.includes( '/node_modules/' ) ) {
					return undefined;
				}

				if ( /\.jsx?$/.test( id ) ) {
					return { syntax: 'ecmascript', jsx: true };
				}

				return undefined;
			},
		} ),
	],
	resolve: {
		// the packages must share a single React, or hooks are never dispatched
		dedupe: [ 'react', 'react-dom' ],
	},
	test: {
		environment: 'node',
		globals: false,
		include: [ 'src/**/__tests__/**/*.test.{js,jsx}' ],
		restoreMocks: true,
		setupFiles: [ './tests/js/setup.mjs' ],
	},
} );
