<template>
	<content>
		<AuthTokenSection />
		<hr>
		<WebDavUrl />
	</content>
</template>

<script lang="ts">
import AuthTokenSection from './components/security/AuthTokenSection.vue'
import WebDavUrl from './components/files/WebDavUrl.vue'
import { defineComponent } from 'vue'

export default defineComponent({
	name: 'App',
	components: {
		AuthTokenSection,
		WebDavUrl,
	},
})
</script>

<style scoped lang="scss">
content {
	width: 100%;
	overflow-y: scroll;
	background-color: var(--color-main-background);
}

hr {
	margin: 0 30px;
}
</style>
