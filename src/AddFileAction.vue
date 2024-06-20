<template>
	<div>
		foo
	</div>
</template>

<script lang="ts">
import { registerFileAction, FileAction } from '@nextcloud/files'
import { mdiStar } from '@mdi/js'

// eslint-disable-next-line no-console
console.log('loaded AddFileAction.vue')

export default {
	name: 'AddFileAction',
	beforeMount() {

		// register on load
		document.addEventListener('DOMContentLoaded', () => {
			const action: FileAction = new FileAction({
				id: 'foo',
				displayName: () => 'Foo',
				title: () => 'Foo Title',
				iconSvgInline: () => mdiStar,
				exec: async () => {
					// eslint-disable-next-line no-console
					console.log('foo action')
					return true
				},
			})
			registerFileAction(action)
			// eslint-disable-next-line no-console
			console.info('handler registered')
		})
	},
}
</script>
