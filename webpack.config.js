const defaultConfig = require( './node_modules/@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );
const IgnoreEmitPlugin = require( 'ignore-emit-webpack-plugin' );

const isProduction = process.env.NODE_ENV === 'production';
const myConfig = {
	optimization: {
		...defaultConfig.optimization,
		splitChunks: {
			cacheGroups: {
				editor: {
					name: 'editor',
					test: /editor\.(sc|sa|c)ss$/,
					chunks: 'all',
					enforce: true,
				},
				default: false,
			},
		},
	},
	plugins: [
		...defaultConfig.plugins,
		new IgnoreEmitPlugin( [ 'editor.js', 'style.js' ] ),
	],
};

module.exports = [
	// JavaScript minification
	{
		mode: defaultConfig.mode,
		devtool: ! isProduction ? 'source-map' : 'eval',
		entry: {
			'admin-options': path.resolve( process.cwd(), 'assets/js', 'admin-options.js' ),
			'ajax-dismissible-notice': path.resolve( process.cwd(), 'assets/js', 'ajax-dismissible-notice.js' ),
		},
		output: {
			filename: '[name].min.js',
			path: path.resolve( process.cwd(), 'assets/js' ),
		},
		optimization: {
			minimize: true,
			minimizer: defaultConfig.optimization.minimizer,
		},
	},
	// blocks
	{
		...defaultConfig,
		...myConfig,
		entry: {
			imprint: path.resolve( process.cwd(), 'src/blocks/imprint', 'block.js' ),
			editor: path.resolve( process.cwd(), 'src', 'editor.scss' ),
		},
	},
	// compiled + minified CSS file
	{
		mode: defaultConfig.mode,
		entry: {
			style: path.resolve( process.cwd(), 'assets/style/scss', 'style.scss' ),
		},
		output: {
			filename: '[name].js',
			path: path.resolve( process.cwd(), 'build' ),
		},
		module: {
			rules: [
				{
					test: /\.(sc|sa)ss$/,
					use: [
						{
							loader: 'file-loader',
							options: {
								name: '[name].min.css',
								outputPath: '../assets/style',
							}
						},
						{
							loader: 'extract-loader',
						},
						{
							loader: 'css-loader',
							options: {
								sourceMap: ! isProduction,
							}
						},
						{
							loader: 'sass-loader',
							options: {
								sourceMap: ! isProduction,
								sassOptions: {
									minimize: true,
									outputStyle: 'compressed',
								}
							}
						},
					],
				},
			],
		},
	},
	// compiled CSS file
	{
		mode: defaultConfig.mode,
		entry: {
			style: path.resolve( process.cwd(), 'assets/style/scss', 'style.scss' ),
		},
		output: {
			filename: '[name].js',
			path: path.resolve( process.cwd(), 'build' ),
		},
		module: {
			rules: [
				{
					test: /\.(sc|sa)ss$/,
					use: [
						{
							loader: 'file-loader',
							options: {
								name: '[name].css',
								outputPath: '../assets/style',
							}
						},
						{
							loader: 'extract-loader',
						},
						{
							loader: 'css-loader',
							options: {
								sourceMap: ! isProduction,
							}
						},
						{
							loader: 'sass-loader',
							options: {
								sourceMap: ! isProduction,
								sassOptions: {
									minimize: false,
									outputStyle: 'expanded',
								}
							}
						},
					],
				},
			],
		},
	},
];
