<!--
SPDX-FileLicenseText: 2024 Thomas Lehmann <t.lehmann@strato.de> with IONOS/Strato
SPDX-FileLicenseText: 2023 John Molakvoæ <skjnldsv@protonmail.com>
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
	<div id="security" class="section">
		<!-- Mostly copied from apps/files/src/views/Settings.vue -->
		<NcAppSettingsSection id="webdav" :name="t('simplesettings', 'WebDAV')">
			<ol>
				<li>
					<em>{{ t('simplesettings', 'Create an app password in the section above.') }}</em>
				</li>
				<li>
					<em>
						{{ t('simplesettings', 'Use this address to access your Files via WebDAV') }}
						<a class="setting-link"
							:href="webdavDocs"
							target="_blank"
							rel="noreferrer noopener">
							{{ t('simplesettings', 'See our FAQ for details') }} ↗
						</a>
					</em>
				</li>
			</ol>
			<br>
			<NcInputField id="webdav-url-input"
				:label="t('simplesettings', 'WebDAV URL')"
				:show-trailing-button="true"
				:success="webdavUrlCopied"
				:trailing-button-label="t('simplesettings', 'Copy to clipboard')"
				:value="webdavUrl"
				readonly="readonly"
				type="url"
				@focus="$event.target.select()"
				@trailing-button-click="copyCloudId">
				<template #trailing-button-icon>
					<Clipboard :size="20" />
				</template>
			</NcInputField>
		</NcAppSettingsSection>
	</div>
</template>

<script lang="ts">
import type { NextcloudUser } from '@nextcloud/auth'
import { defineComponent } from 'vue'
// @ts-expect-error: Cannot find module or its corresponding type declarations.
import NcAppSettingsSection from '@nextcloud/vue/dist/Components/NcAppSettingsSection.js'
// @ts-expect-error: Cannot find module or its corresponding type declarations.
import NcInputField from '@nextcloud/vue/dist/Components/NcInputField.js'
import Clipboard from 'vue-material-design-icons/Clipboard.vue'
import { generateRemoteUrl } from '@nextcloud/router'
import { getCurrentUser } from '@nextcloud/auth'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { translate as t } from '@nextcloud/l10n'

// TODO add translations in "simplesettings" namespace

export default defineComponent({
	name: 'WebDavUrl',
	components: {
		Clipboard,
		NcInputField,
		NcAppSettingsSection,
	},
	data() {
		let webdavUrl = ''
		if (getCurrentUser() !== null && getCurrentUser()?.uid) {
			webdavUrl = generateRemoteUrl(`dav/files/${encodeURIComponent((getCurrentUser() as NextcloudUser).uid)}`)
		}
		return {
			// Webdav infos
			webdavUrl,
			// TODO this is only a dummy URL & make this a config
			webdavDocs: 'https://www.ionos.com/help/',
			webdavUrlCopied: false,
		}
	},
	methods: {
		async copyCloudId() {
			(document.querySelector('input#webdav-url-input') as HTMLInputElement)?.select()

			if (!navigator.clipboard) {
				// Clipboard API not available
				showError(t('files', 'Clipboard is not available'))
				return
			}

			await navigator.clipboard.writeText(this.webdavUrl)
			this.webdavUrlCopied = true
			showSuccess(t('files', 'WebDAV URL copied to clipboard'))
			setTimeout(() => {
				this.webdavUrlCopied = false
			}, 5000)
		},
	},
})
</script>

<style scoped>
ol {
	margin: 10px 30px;
}
</style>
