<template>
	<AuthTokenSection />
</template>

<script lang="ts">
import AuthTokenSection from './components/security/AuthTokenSection.vue'
import { defineComponent } from 'vue'

export default defineComponent({
	name: 'App',
	components: {
		AuthTokenSection,
	},
})
</script>

<style scoped lang="scss">
#simplesettings {
	display: flex;
	justify-content: center;
	margin: 16px;
}
</style>
