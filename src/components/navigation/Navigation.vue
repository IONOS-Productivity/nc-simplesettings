<template>
	<div class="settings-navigation">
		<ul class="app-navigation-entry">
			<NcAppNavigationItem
				id="backButton"
				:name="t('simplesettings', 'Back to Files')"
				href="/index.php/apps/files">
				<IconChevronLeft slot="icon" :size="24" />
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:name="t('simplesettings', 'Account Settings')"
				@click="scrollToElement('account')">
				<IconAccount slot="icon" :size="20" />
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:name="t('simplesettings', 'Security & Privacy')"
				@click="scrollToElement('security')">
				<IconLock slot="icon" :size="20" />
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:name="t('simplesettings', 'Help & Support')"
				@click="scrollToElement('help')">
				<IconHeadset slot="icon" :size="20" />
			</NcAppNavigationItem>
		</ul>
	</div>
</template>

<script lang="ts">
import { translate as t } from '@nextcloud/l10n'
import { defineComponent } from 'vue'

import IconAccount from 'vue-material-design-icons/Account.vue'
import IconLock from 'vue-material-design-icons/Lock.vue'
import IconHeadset from 'vue-material-design-icons/Headset.vue'
import IconChevronLeft from 'vue-material-design-icons/ChevronLeft.vue'

// @ts-expect-error: Cannot find module or its corresponding type declarations.
import NcAppNavigationItem from '@nextcloud/vue/dist/Components/NcAppNavigationItem.js'

export default defineComponent({
	name: 'Navigation',
	components: {
		IconAccount,
		IconLock,
		IconHeadset,
		IconChevronLeft,
		NcAppNavigationItem,
	},
	methods: {
		scrollToElement(ref: string) {
			this.$emit('scroll-to', ref)
		},
		t,
	},
})
</script>

<style scoped>
.settings-navigation {
	margin-left: 14px;
	margin-top: 24px;

	ul {
		#backButton {
			margin-top: -8px;
			margin-left: -14px;
		}
	}
}
</style>
