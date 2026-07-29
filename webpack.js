const webpack = require('webpack')
const webpackConfig = require('@nextcloud/webpack-vue-config')
const ESLintPlugin = require('eslint-webpack-plugin')
const StyleLintPlugin = require('stylelint-webpack-plugin')
const path = require('path')

webpackConfig.entry = {
	main: { import: path.join(__dirname, 'src', 'main.ts'), filename: 'main.js' },
}

webpackConfig.plugins.push(
	new ESLintPlugin({
		extensions: ['js', 'vue'],
		files: 'src',
	}),
)
webpackConfig.plugins.push(
	new StyleLintPlugin({
		files: 'src/**/*.{css,scss,vue}',
	}),
)

// Modify Webpack config to patch .tsx? rule
// The ts-loader option appendTsSuffixTo: [/\.vue$/] is required, otherwise
// it would emit no transpiler result for <script lang="ts"> blocks in .vue
// files
webpackConfig.module.rules = webpackConfig.module.rules.map((module) => {
	if (!Array.isArray(module.use) || !module.use.includes('ts-loader')) {
		return module
	}

	return {
		test: /\.tsx?$/,
		use: [
			'babel-loader',
			{
				loader: 'ts-loader',
				options: {
					appendTsSuffixTo: [/\.vue$/]
				}
			}
		],
		exclude: /node_modules/
	}
})

webpackConfig.module.rules.push({
	test: /\.svg$/i,
	type: 'asset/source',
})

webpackConfig.plugins.push(new webpack.SourceMapDevToolPlugin({
	filename: '[file].map',
}))

module.exports = webpackConfig
