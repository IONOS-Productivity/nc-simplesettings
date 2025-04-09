<template>
	<div class="settings-navigation">
		<ul class="app-navigation-entry">
			<NcAppNavigationItem
				id="backButton"
				:name="t('simplesettings', 'Back to Files')"
				href="/index.php/apps/files">
				<IconChevronLeft slot="icon" :size="16" />
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:name="t('simplesettings', 'Storage usage')"
				@click="scrollToElement('storage')">
				<IconStorage slot="icon" :size="18" />
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:name="t('simplesettings', 'Language')"
				@click="scrollToElement('language')">
				<IconTranslate slot="icon" :size="18" />
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:name="t('simplesettings', 'Devices & sessions', {}, undefined, {sanitize: false})"
				@click="scrollToElement('devices')">
				<IconDevices slot="icon" :size="18" />
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:name="t('simplesettings', 'WebDAV')"
				@click="scrollToElement('webDAV')">
				<IconEarth slot="icon" :size="18" />
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:name="t('simplesettings', 'App & Software')"
				@click="scrollToElement('software')">
				<IconDisplay slot="icon" :size="18" />
			</NcAppNavigationItem>
		</ul>
	</div>
</template>

<script lang="ts">
import { translate as t } from '@nextcloud/l10n'
import { defineComponent } from 'vue'

import IconChevronLeft from 'vue-material-design-icons/ChevronLeft.vue'
import IconStorage from 'vue-material-design-icons/CloudOutline.vue'
import IconTranslate from 'vue-material-design-icons/Translate.vue'
import IconDevices from 'vue-material-design-icons/TabletCellphone.vue'
import IconEarth from 'vue-material-design-icons/Earth.vue'
import IconDisplay from 'vue-material-design-icons/DesktopClassic.vue'

// @ts-expect-error: Cannot find module or its corresponding type declarations.
import NcAppNavigationItem from '@nextcloud/vue/dist/Components/NcAppNavigationItem.js'

export default defineComponent({
	name: 'Navigation',
	components: {
		IconChevronLeft,
		IconStorage,
		IconTranslate,
		IconDevices,
		IconEarth,
		IconDisplay,
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

<style>
.settings-navigation {
	margin-left: 14px;
	margin-top: 24px;

	ul {
		font-weight: 600;

		#backButton {
			margin-top: -8px;
			margin-left: -14px;
		}
	}

	.app-navigation-entry {
		li div.app-navigation-entry{
			background-color: var(--ion-button-sidebar-background);
			color: var(--ion-button-sidebar-text);
			border-radius: 100px;
			width: 95%;
			&:hover {
				background-color: var(--ion-button-sidebar-background-hover);
			}
			&:active {
				background-color: var(--ion-button-sidebar-background-active);
			}
		}
	}
}

</style>
