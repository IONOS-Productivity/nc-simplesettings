<!--
SPDX-FileLicenseText: 2024 Thomas Lehmann <t.lehmann@strato.de>
SPDX-License-Identifier: AGPL-3.0-or-later

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
-->

<template>
	<content>
		<div class="navigation">
			<Navigation @scroll-to="scrollToElement" />
		</div>
		<div class="settings">
			<a id="close-icon" href="/index.php/apps/files">
				<IconClose :size="24" />
			</a>
			<h2 ref="account">
				{{ t('simplesettings', 'Account Settings') }}
			</h2>
			<Quota />
			<LanguageSection />
			<h2 ref="security">
				{{ t('simplesettings', 'Security & Privacy') }}
			</h2>
			<AuthTokenSection />
			<WebDavUrl />
			<h2 ref="help">
				{{ t('simplesettings', 'Help & Support') }}
			</h2>
			<Software />
		</div>
	</content>
</template>

<script lang="ts">
import { translate as t } from '@nextcloud/l10n'
import AuthTokenSection from './components/security/AuthTokenSection.vue'
import WebDavUrl from './components/files/WebDavUrl.vue'
import Software from './components/help/Software.vue'
import LanguageSection from './components/account/LanguageSection.vue'
import Quota from './components/account/Quota.vue'
import Navigation from './components/navigation/Navigation.vue'
import IconClose from 'vue-material-design-icons/Close.vue'
import { defineComponent } from 'vue'

export default defineComponent({
	name: 'App',
	components: {
		LanguageSection,
		AuthTokenSection,
		WebDavUrl,
		Software,
		Quota,
		Navigation,
		IconClose,
	},
	methods: {
		scrollToElement(element: string) {
			const el = this.$refs[element] as HTMLElement
			el?.scrollIntoView({ behavior: 'smooth', block: 'start' })
		},
		t,
	},
})
</script>

<style scoped lang="scss">
@use '../../../core/css/variables.scss' as variables;

content {
	display: flex;
	align-items: stretch;
	flex-direction: row;
	width: 100%;
	background-color: var(--color-main-background);
}

.settings {
	width: 85%;
	overflow-y: scroll;

	:deep(.section) {
		padding-top: 16px;
		margin-bottom: 0;
	}
}

.navigation {
	background-color: var(--ion-surface-secondary);
	width: 15%;
}

#close-icon {
	// Hide the close icon on desktop
	display: none;
}

h2 {
	padding-top: 25px;
	padding-left: 30px;
	margin-bottom: 0;
	font-weight: 700;
	font-size: 24px;
}

@media screen and (max-width: calc(variables.$breakpoint-mobile / 2)) {
	.settings {
		position: relative;
		width: 100%;
	}

	.navigation {
		// Hide the navigation bar on mobile
		display: none;
	}

	#close-icon {
		display: block;
		position: absolute;
		top: 0;
		right: 0;
		z-index: 10;
		font-size: 24px;
		padding: 10px;
	}
}
</style>
