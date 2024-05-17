<template>
	<NcAppContent>
		<div id="simplesettings">
			<h1>Hello world!</h1>
		</div>
	</NcAppContent>
</template>

<script lang="ts">
import NcAppContent from '@nextcloud/vue/dist/Components/NcAppContent.js'

export default {
	name: 'App',
	components: {
		NcAppContent,
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
