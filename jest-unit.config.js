const defaultConfig = require( '@wordpress/scripts/config/jest-unit.config' );

module.exports = {
	...defaultConfig,
	moduleNameMapper: {
		...defaultConfig.moduleNameMapper,
		// the packages resolve React through different module registries, which
		// would give the test renderer a React whose hooks are never dispatched
		'^react$': require.resolve( 'react' ),
		'^react-dom$': require.resolve( 'react-dom' ),
		'^react-dom/client$': require.resolve( 'react-dom/client' ),
		'^react/jsx-runtime$': require.resolve( 'react/jsx-runtime' ),
	},
};
