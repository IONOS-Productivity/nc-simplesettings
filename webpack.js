const webpackConfig = require('@nextcloud/webpack-vue-config')
const ESLintPlugin = require('eslint-webpack-plugin')
const StyleLintPlugin = require('stylelint-webpack-plugin')
const path = require('path')

webpackConfig.entry = {
	main: path.join(__dirname, 'src', 'main.ts'),
}

// For debugging
webpackConfig.stats = { errorDetails: true };

// Fix wrong configuration in the @nextcloud/webpack-vue-config module.
// It's defined as ['*', '.ts', '.js', '.vue'], but Webpack complains it should
// use leading dots. "*" is not documented anyway.
// See https://webpack.js.org/configuration/resolve/#resolveextensions
webpackConfig.resolve.extensions = [".ts",".js",".vue"];

webpackConfig.plugins.push(
	new ESLintPlugin({
		extensions: ['.js', '.ts', '.vue'],
		files: 'src',
	}),
)
webpackConfig.plugins.push(
	new StyleLintPlugin({
		files: 'src/**/*.{css,scss,vue}',
	}),
)

webpackConfig.module.rules.push({
	test: /\.svg$/i,
	type: 'asset/source',
})

module.exports = webpackConfig
