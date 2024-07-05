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
	<AuthTokenSection />
</template>

<script lang="ts">
import AuthTokenSection from './components/AuthTokenSection.vue'
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
