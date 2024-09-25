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
			<a href="/">Previous</a>
			<ul>
				<li>
					<a @click="scrollToElement('account')">Account Settings</a>
				</li>
				<li>
					<a @click="scrollToElement('security')">Security & Privacy</a>
				</li>
				<li>
					<a @click="scrollToElement('help')">Help & Support</a>
				</li>
			</ul>
		</div>
		<div class="settings">
			<h2 ref="account">
				Account Settings
			</h2>
			<Quota />
			<LanguageSection />
			<h2 ref="security">
				Security & Privacy
			</h2>
			<AuthTokenSection />
			<WebDavUrl />
			<h2 ref="help">
				Help & Support
			</h2>
			<Software />
		</div>
	</content>
</template>

<script lang="ts">
import AuthTokenSection from './components/security/AuthTokenSection.vue'
import WebDavUrl from './components/files/WebDavUrl.vue'
import Software from './components/help/Software.vue'
import LanguageSection from './components/account/LanguageSection.vue'
import Quota from './components/account/Quota.vue'
import { defineComponent } from 'vue'

export default defineComponent({
	name: 'App',
	components: {
		LanguageSection,
		AuthTokenSection,
		WebDavUrl,
		Software,
		Quota,
	},

	methods: {
		scrollToElement(ref: string) {
			const el = this.$refs[ref] as HTMLElement

			el?.scrollIntoView({ behavior: 'smooth', inline: 'start' })
		},
	},
})
</script>

<style scoped lang="scss">
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
	background-color: var(--ion-color-cool-grey-c1);
	width: 15%;
}

h2 {
	padding-top: 25px;
	padding-left: 30px;
	margin-bottom: 0;
	font-weight: 700;
	font-size: 24px;
}
</style>
