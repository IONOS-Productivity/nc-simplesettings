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
		// vue/no-v-model-argument is a Vue 2 rule; v-model:arg is valid Vue 3 syntax
		'vue/no-v-model-argument': 'off',
		quotes: ['error', 'single'],
	},
}
