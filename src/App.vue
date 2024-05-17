<template>
	<AuthTokenSection />
</template>

<script lang="ts">
import AuthTokenSection from './components/AuthTokenSection.vue'

export default {
	name: 'App',
	components: {
		AuthTokenSection,
	},
}
</script>

<style scoped lang="scss">
#simplesettings {
	display: flex;
	justify-content: center;
	margin: 16px;
}
</style>
