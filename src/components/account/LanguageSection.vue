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
	<section class="section">
		<h2>{{ t('simplesettings', 'Language') }}</h2>
		<Language v-if="isEditable"
			:input-id="inputId"
			:available-languages="availableLanguages"
			:language.sync="language" />

		<span v-else>
			{{ t('simplesettings', 'No language set') }}
		</span>
	</section>
</template>

<script>
import { loadState } from '@nextcloud/initial-state'
import Language from './Language.vue'
import { ACCOUNT_SETTING_PROPERTY_ENUM, ACCOUNT_SETTING_PROPERTY_READABLE_ENUM } from '../../constants/AccountPropertyConstants.js'

const { languageMap: { activeLanguage, allLanguages } } = loadState('simplesettings', 'personalInfoParameters', {})

export default {
	name: 'LanguageSection',

	components: {
		Language,
	},

	setup() {
		return {
			availableLanguages: allLanguages,
			propertyReadable: ACCOUNT_SETTING_PROPERTY_READABLE_ENUM.LANGUAGE,
		}
	},

	data() {
		return {
			language: activeLanguage,
		}
	},

	computed: {
		inputId() {
			return `account-setting-${ACCOUNT_SETTING_PROPERTY_ENUM.LANGUAGE}`
		},

		isEditable() {
			return Boolean(this.language)
		},
	},
}
</script>
