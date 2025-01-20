<!--
SPDX-FileLicenseText: 2021 Christopher Ng <chrng8@gmail.com>
SPDX-FileLicenseText: 2024 Tatjana Kaschperko Lindt <kaschperko-lindt@strato.de>
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
	<div class="language">
		<NcSelect :aria-label-listbox="t('simplesettings', 'Languages')"
			class="language__select"
			:clearable="false"
			:input-id="inputId"
			label="name"
			label-outside
			:options="allLanguages"
			:value="language"
			@option:selected="onLanguageChange" />
	</div>
</template>

<script>
import { ACCOUNT_SETTING_PROPERTY_ENUM } from '../../constants/AccountPropertyConstants.js'
import { savePrimaryAccountProperty } from '../../service/PersonalInfo/PersonalInfoService.js'
import { handleError } from '../../utils/handlers.js'

import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js'

export default {
	name: 'Language',

	components: {
		NcSelect,
	},

	props: {
		inputId: {
			type: String,
			default: null,
		},
		availableLanguages: {
			type: Array,
			required: true,
		},
		language: {
			type: Object,
			required: true,
		},
	},

	data() {
		return {
			initialLanguage: this.language,
		}
	},

	computed: {
		allLanguages() {
			return [this.language, ...this.availableLanguages.filter(l => l.code !== this.language.code)]
		},
	},

	methods: {
		async onLanguageChange(language) {
			this.$emit('update:language', language)

			await this.updateLanguage(language)
		},

		async updateLanguage(language) {
			try {
				const responseData = await savePrimaryAccountProperty(ACCOUNT_SETTING_PROPERTY_ENUM.LANGUAGE, language.code)
				this.handleResponse({
					language,
					status: responseData.ocs?.meta?.status,
				})
				window.location.reload()
			} catch (e) {
				this.handleResponse({
					errorMessage: t('settings', 'Unable to update language'),
					error: e,
				})

			}
		},

		handleResponse({ language, status, errorMessage, error }) {
			if (status === 'ok') {
				// Ensure that local state reflects server state
				this.initialLanguage = language
			} else {
				handleError(error, errorMessage)
			}
		},
	},
}
</script>

<style lang="scss">
.language {
	display: grid;
	max-width: 500px;
}

li.vs__dropdown-option{
		background-color: var(--ion-button-sidebar-background);
		color: var(--ion-button-sidebar-text);
		&.vs__dropdown-option--highlight {
			background-color: var(--ion-button-sidebar-background-hover);
		}
	}
</style>
