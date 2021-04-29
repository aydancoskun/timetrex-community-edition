const path = require('path');
const webpack = require('webpack');
const MergeIntoSingleFilePlugin = require('webpack-merge-and-include-globally');
const CopyPlugin = require('copy-webpack-plugin');
const BundleAnalyzerPlugin = require( 'webpack-bundle-analyzer').BundleAnalyzerPlugin;
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');

console.log( 'Parsing Webpack config...' );
module.exports = (env, argv) => {
	const is_production = argv && argv.mode === 'production' || process.env.NODE_ENV === 'production';
	console.log( 'MODE: ' + ( is_production ? 'production' : 'development' ) );

	var config = {
		mode: 'development',
		entry: {
			'main_ui': './interface/html5/main.js',
			'quick_punch': './interface/html5/quick_punch/main.js',
			'portal': './interface/html5/portal/recruitment/main.js',
			// 'pdf.worker': 'pdfjs-dist/build/pdf.worker.entry', // #2841 Manually load instead. pdfworker is loaded on-demand by PDFjs. If this changes, update ttpdfviewer.js pdfjsLib.GlobalWorkerOptions.workerSrc
			'main_ui-styles': './interface/html5/main_ui-styles',
			'main_ui-vendor-styles': './interface/html5/main_ui-vendor-styles',
			'quick_punch-styles': './interface/html5/quick_punch/quick_punch-styles',
			'portal-styles': './interface/html5/portal/recruitment/portal-styles', // TODO: Rename Portal to Recruitment at some point.

		},
		output: {
			filename: '[name].bundle.js?v=[contenthash]',
			path: path.resolve( __dirname, 'interface/html5/dist' ),
			publicPath: './dist/' // This is dynamically changed for quick_punch to include a '../' due to the added directory level.
		},
		stats: { // more info at https://v4.webpack.js.org/configuration/stats/
			logging: 'verbose',
			// loggingDebug: [ 'CopyPlugin' ],
			// assets: false // Trialing this to reduce to output from pdf plugin related output, and the dynamic imports. Careful, might be hiding too much output!
		},
		devtool: ( is_production ? 'source-map' : 'eval-source-map' ), // eval-source-map seems to fix breakpoints not going on the line you expect, or being triggered at all.
		module: {
			noParse: /triggerParserError\.js/, // This is to prevent webpack parsing this file and complaining about a deliberate parse error. Webpack error: "Module parse failed: Unexpected token ./interface/html5/views/developer_tools/triggerParserError.js (6:3)"
			rules: [
				{
					// Alternative import syntax trial for TopMenuManager, as an example. Can also be refactored to be imported the normal way if needed.
					test: require.resolve( './interface/html5/global/TopMenuManager' ),
					use: [
						{
							loader: 'imports-loader', // https://webpack.js.org/loaders/imports-loader/#using-configuration
							options: {
								imports: [
									{ // Alt syntax as object example.
										syntax: 'named',
										moduleName: '@/model/RibbonMenu',
										name: 'RibbonMenu',
									},
									// Alt syntax to show one-line definition.
									'named @/model/RibbonSubMenu RibbonSubMenu',
									'named @/model/RibbonSubMenu RibbonSubMenuType',
									'named @/model/RibbonSubMenuGroup RibbonSubMenuGroup',
									'named @/model/RibbonSubMenuNavItem RibbonSubMenuNavItem'
								]
							},
						},
					]
				},
				{
					test: /\.vue$/,
					use: 'vue-loader'
				},
				{
					test: /\.css$/,
					use: [
						// 'style-loader',
						MiniCssExtractPlugin.loader,
						{
							loader: 'css-loader',
							options: {
								sourceMap: true // To use this, remember to enable CSS Sourcemaps in your browser's Dev Tools.
								// Using `local` value has same effect like using `modules: true` https://webpack.js.org/loaders/css-loader/#string and https://github.com/css-modules/css-modules
								// modules: 'local', // #2662 Commenting this out for now, as its causing PDFJS CSS to not be applied globally as needed. We're not using CSS Modules yet, so this would not have benefited us yet.
							}
						},
					]
				},
				{
					// Copies node module images.
					// Match images ONLY in node_modules
					test: /(node_modules).*\.(jpe?g|png|gif|woff|woff2|eot|ttf|svg)(\?[a-z0-9=.]+)?$/,
					exclude:/node_modules\/leaflet\//,
					loader: 'file-loader',
					options: {
						emitFile: true,
						name: 'images/[path][name].[ext]?v=[contenthash]', // [path] added to prevent clashes with images with same name.
						publicPath: './', // This is for all images in node_modules other than leafet. Fixes issues for GEOFence map menu icons.
					}
				},
				{
					// Copies Leaflet node module images.
					// Match images ONLY in node_modules LEAFLET package, as this requires special treatment, handles images in a strange way. See https://github.com/Leaflet/Leaflet/issues/4968
					test: /(node_modules\/leaflet\/).*\.(jpe?g|png|gif|woff|woff2|eot|ttf|svg)(\?[a-z0-9=.]+)?$/,
					loader: 'file-loader',
					options: {
						emitFile: true,
						name: 'images/[path][name].[ext]?v=[contenthash]', // [path] added to prevent clashes with images with same name.
					}
				},
				{
					// Leaves application images in their place, do not copy.
					// Match all images except for ones in node_modules, which will be handled by the above rule.
					test: /\.(jpe?g|png|gif|woff|woff2|eot|ttf|svg)(\?[a-z0-9=.]+)?$/,
					exclude:/node_modules/,
					loader: 'file-loader',
					options: {
						emitFile: false,
						name: '[path][name].[ext]?v=[contenthash]', // [path] added to prevent clashes with images with same name.
						publicPath: '../../../',
					}
				}
			]
		},
		optimization: {
			runtimeChunk: 'single',
			moduleIds: 'deterministic',
			chunkIds: 'named',
			minimize: true,
			minimizer: [
				new TerserPlugin({ // This minifies JavaScript
					terserOptions: {
						compress: {
							drop_debugger: false,
						},
						safari10: true, //Work-arounds for Safari v10/v11. ie: Cannot declare a let variable twice: 'i'
						format: {
							comments: false,
							keep_quoted_props: true, //Should prevent Safari JS SyntaxError: Invalid character '\u00b7' - https://github.com/terser/terser/issues/729
						},
					},
					extractComments: false, //Disables creating hundreds of License.txt files.
				}),
				new CssMinimizerPlugin(),
			],
			splitChunks: {
				// minSize: 30000,
				//maxSize: 999999999, // to prevent chunking with [contentHash] names which change on Linux vs Mac builds. Issue logged at https://github.com/webpack-contrib/mini-css-extract-plugin/issues/355
			}
		},
		plugins: [
			new MiniCssExtractPlugin( {
				filename: '[name].css?v=[contenthash]',
			} ),
			// new HtmlWebpackPlugin(), // Default: This is to output the standard index.html so dev can check all output assets and ensure they are accounted for.
			// -- Injections for CSS --
			new HtmlWebpackPlugin({ // Manages Main UI CSS HTML tag generation.
				// Webpack will also generate some JS files for the styles, but we dont want to include them as they serve no purpose, the CSS is already extracted.
				inject: false,
				templateContent: ({htmlWebpackPlugin}) => `${htmlWebpackPlugin.tags.headTags}`, // inline template to only output head tags, in this case, together with the filter on CSS chunks, it will only output CSS. (We want to ignore the CSS bundle JS files, they are empty).
				filename: "_css.main_ui.template.html", // this is then required into our index.php files using PHP.
				chunksSortMode: "manual", // follow the sort order defined in 'chunks' below.
				chunks: ['main_ui-vendor-styles', 'main_ui-styles'], // Decides which chunks to include, and what order. chunkSortMode must be 'manual'.
			}),
			new HtmlWebpackPlugin({ // Manages Quick Punch CSS HTML tag generation.
				// Webpack will also generate some JS files for the styles, but we dont want to include them as they serve no purpose, the CSS is already extracted.
				inject: false,
				templateContent: ({htmlWebpackPlugin}) => `${htmlWebpackPlugin.tags.headTags}`, // inline template to only output head tags, in this case, together with the filter on CSS chunks, it will only output CSS. (We want to ignore the CSS bundle JS files, they are empty).
				filename: "_css.quick_punch.template.html", // this is then required into our index.php files using PHP.
				chunksSortMode: "manual", // follow the sort order defined in 'chunks' below.
				chunks: ['quick_punch-styles'], // Decides which chunks to include, and what order. chunkSortMode must be 'manual'.
				publicPath: '../dist/',
			}),
			new HtmlWebpackPlugin({ // Manages Portal CSS HTML tag generation.
				// Webpack will also generate some JS files for the styles, but we dont want to include them as they serve no purpose, the CSS is already extracted.
				inject: false,
				templateContent: ({htmlWebpackPlugin}) => `${htmlWebpackPlugin.tags.headTags}`, // inline template to only output head tags, in this case, together with the filter on CSS chunks, it will only output CSS. (We want to ignore the CSS bundle JS files, they are empty).
				filename: "_css.portal.template.html", // this is then required into our index.php files using PHP.
				chunksSortMode: "manual", // follow the sort order defined in 'chunks' below.
				chunks: ['portal-styles'], // Decides which chunks to include, and what order. chunkSortMode must be 'manual'.
				publicPath: '../../dist/',
			}),
			// -- Injections for JS --
			new HtmlWebpackPlugin({ // Manages Main App JS HTML tag generation.
				inject: false,
				templateContent: ({htmlWebpackPlugin}) => `${htmlWebpackPlugin.tags.bodyTags}`,
				filename: "_js.main_ui.template.html", // this is then required into our index.php files using PHP.
				chunksSortMode: "manual", // follow the sort order defined in 'chunks' below.
				chunks: ['main_ui'], // Decides which chunks to include, and what order. chunkSortMode must be 'manual'.
			}),
			new HtmlWebpackPlugin({ // Manages QuickPunch JS HTML tag generation.
				inject: false,
				templateContent: ({htmlWebpackPlugin}) => `${htmlWebpackPlugin.tags.bodyTags}`,
				filename: "_js.quick_punch.template.html", // this is then required into our index.php files using PHP.
				chunksSortMode: "manual", // follow the sort order defined in 'chunks' below.
				chunks: ['quick_punch'], // Decides which chunks to include, and what order. chunkSortMode must be 'manual'.
				publicPath: '../dist/',
			}),
			new HtmlWebpackPlugin({ // Manages Portal JS HTML tag generation.
				inject: false,
				templateContent: ({htmlWebpackPlugin}) => `${htmlWebpackPlugin.tags.bodyTags}`,
				filename: "_js.portal.template.html", // this is then required into our index.php files using PHP.
				chunksSortMode: "manual", // follow the sort order defined in 'chunks' below.
				chunks: ['portal'], // Decides which chunks to include, and what order. chunkSortMode must be 'manual'.
				publicPath: '../../dist/',
			}),
			new webpack.IgnorePlugin( {
				// Ignore moment locales to save network loads: https://webpack.js.org/plugins/ignore-plugin/#example-of-ignoring-moment-locales
				resourceRegExp: /^\.\/locale$/,
				contextRegExp: /moment$/
			} ),
			// Uncomment BundleAnalyzerPlugin section whenever you need to generate the webpack stats json for use with npm run view:stats
			// new BundleAnalyzerPlugin( {
			// 	analyzerMode: 'disabled',
			// 	generateStatsFile: true,
			// } ),
		],
		// #2662 https://stackoverflow.com/a/62022829/339803 Not 100% certain on the breakdowns in this SO answer. Research them.
		resolve: {
			alias: {
				'@': path.resolve( __dirname, 'interface/html5' ),
				'vue$': 'vue/dist/vue.esm.js',
				'jquery': require.resolve( 'jquery' ),
			},
			modules: [path.resolve( 'node_modules' )],
			extensions: ['.js', '.vue', '.json']
		},
		resolveLoader: {
			modules: ['node_modules']
		}
	}; // End config object.

	/*
	 * Additional environment dependant config options.
	 */

	console.log('Additional build options:');
	console.log( ' [x] PROD+DEV: Copy pdf.worker.js to dist folder.' );
	var copyPluginPatterns = [
		{
			// Dev+Prod: Copy pdf worker manually to dist folder instead of using entry file, to fix bug where PDFs would not load if optimization.runtimeChunk: 'single' https://github.com/mozilla/pdf.js/issues/7612#issuecomment-258827877
			// If this changes, update ttpdfviewer.js pdfjsLib.GlobalWorkerOptions.workerSrc
			from: path.resolve( 'node_modules/pdfjs-dist/build/pdf.worker.js' ),
			to: './'
		},
	];

	if ( is_production ) {
		console.log( ' [x] PROD: Clean dist directory before build.' );
		config.plugins.unshift( new CleanWebpackPlugin() ); // Clean the dist directory before a production build.
		console.log( ' [x] PROD: Copy PDFJS character maps to dist folder.' );
		copyPluginPatterns.push({ // Copies character maps in production.
			from: path.resolve('node_modules/pdfjs-dist/cmaps/'),
			to: 'pdfjs-cmaps'
		});
	}

	config.plugins.push( new CopyPlugin({
		patterns: copyPluginPatterns,
	}) );

	console.log( 'Webpack config parsed. Building...' );

	return config;
}
