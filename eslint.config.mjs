import wordpress from '@wordpress/eslint-plugin';

export default [
	{
		ignores: [
			'**/.cache/**',
			'**/build/**',
			'**/build-module/**',
			'**/build-types/**',
			'**/node_modules/**',
			'**/vendor/**',
		],
	},
	...wordpress.configs.recommended,
	{
		settings: {
			'import/resolver': {
				node: { extensions: [ '.js', '.jsx' ] },
			},
		},
	},
	...wordpress.configs[ 'test-unit' ].map( ( config ) => ( {
		...config,
		files: [ '**/__tests__/**/*.{js,jsx}', '**/*.test.{js,jsx}' ],
	} ) ),
];
