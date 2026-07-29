<template>
	<div class="settings-navigation">
		<ul class="app-navigation-entry">
			<NcAppNavigationItem
				id="backButton"
				:name="t('simplesettings', 'Back to Files')"
				href="/index.php/apps/files">
				<template #icon>
					<IconChevronLeft :size="16" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:name="t('simplesettings', 'Storage usage')"
				@click="scrollToElement('storage')">
				<template #icon>
					<IconStorage :size="18" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:name="t('simplesettings', 'Language')"
				@click="scrollToElement('language')">
				<template #icon>
					<IconTranslate :size="18" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:name="t('simplesettings', 'Devices & sessions', {}, undefined, {sanitize: false})"
				@click="scrollToElement('devices')">
				<template #icon>
					<IconDevices :size="18" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:name="t('simplesettings', 'WebDAV')"
				@click="scrollToElement('webDAV')">
				<template #icon>
					<IconEarth :size="18" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:name="t('simplesettings', 'App & Software')"
				@click="scrollToElement('software')">
				<template #icon>
					<IconDisplay :size="18" />
				</template>
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

import { NcAppNavigationItem } from '@nextcloud/vue'

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
