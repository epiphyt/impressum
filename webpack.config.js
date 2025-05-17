const defaultConfig = require( './node_modules/@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );
const IgnoreEmitPlugin = require( 'ignore-emit-webpack-plugin' );
const MiniCSSExtractPlugin = require( 'mini-css-extract-plugin' );

const isProduction = process.env.NODE_ENV === 'production';
const mode = isProduction ? 'production' : 'development';

const jsFiles = {
	'admin-options': path.resolve( process.cwd(), 'assets/js', 'admin-options.js' ),
	'admin-tabs': path.resolve( process.cwd(), 'assets/js', 'admin-tabs.js' ),
	'ajax-dismissible-notice': path.resolve( process.cwd(), 'assets/js', 'ajax-dismissible-notice.js' ),
};
const scssFiles = {
	'style': path.resolve( process.cwd(), 'assets/style/scss', 'style.scss' ),
};

module.exports = [
	// blocks
	{ ...defaultConfig },
	// JavaScript minification
	{
		mode,
		devtool: ! isProduction ? 'source-map' : 'hidden-source-map',
		entry: jsFiles,
		output: {
			filename: '[name].min.js',
			path: path.resolve( process.cwd(), 'assets/js/build' ),
		},
		optimization: {
			minimize: true,
			minimizer: defaultConfig.optimization.minimizer,
		},
	},
	// compiled + minified CSS file
	{
		mode,
		devtool: ! isProduction ? 'source-map' : 'hidden-source-map',
		entry: scssFiles,
		output: {
			path: path.resolve( process.cwd(), 'assets/style/build' ),
		},
		module: {
			rules: [
				{
					test: /\.(sc|sa)ss$/,
					use: [
						MiniCSSExtractPlugin.loader,
						{
							loader: 'css-loader',
							options: {
								sourceMap: ! isProduction,
								url: false,
							},
						},
						{
							loader: 'sass-loader',
							options: {
								sourceMap: ! isProduction,
								sassOptions: {
									minimize: true,
									outputStyle: 'compressed',
								},
							},
						},
					],
				},
			],
		},
		plugins: [ new MiniCSSExtractPlugin( { filename: '[name].min.css' } ), new IgnoreEmitPlugin( [ '.js' ] ) ],
	},
	// compiled CSS
	{
		mode,
		devtool: ! isProduction ? 'source-map' : 'hidden-source-map',
		entry: scssFiles,
		output: {
			path: path.resolve( process.cwd(), 'assets/style/build' ),
		},
		module: {
			rules: [
				{
					test: /\.(sc|sa)ss$/,
					use: [
						MiniCSSExtractPlugin.loader,
						{
							loader: 'css-loader',
							options: {
								sourceMap: ! isProduction,
								url: false,
							},
						},
						{
							loader: 'sass-loader',
							options: {
								sourceMap: ! isProduction,
								sassOptions: {
									minimize: false,
									outputStyle: 'expanded',
								},
							},
						},
					],
				},
			],
		},
		plugins: [ new MiniCSSExtractPlugin( { filename: '[name].css' } ), new IgnoreEmitPlugin( [ '.js' ] ) ],
	},
];
