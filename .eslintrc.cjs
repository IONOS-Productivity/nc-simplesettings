module.exports = {
	extends: [
		'@nextcloud',
		'@nextcloud/eslint-config/typescript',
	],
	rules: {
		'jsdoc/require-jsdoc': 'off',
		'vue/first-attribute-linebreak': 'off',
		// Workaround due to a bug in tsc, which fails with "property foo
		// does not exist", which is clearly present
		// See https://github.com/vuejs/vue/issues/12628#issuecomment-1283730746
		'vue/order-in-components': 'off',
		'quotes': ['error', 'single'],
	},
}
