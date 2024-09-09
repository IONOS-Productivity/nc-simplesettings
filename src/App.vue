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
		<Quota />
		<hr>
		<LanguageSection />
		<hr>
		<AuthTokenSection />
		<hr>
		<WebDavUrl />
		<hr>
		<Software />
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
