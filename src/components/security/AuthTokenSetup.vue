<!--
  - @copyright 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
  -
  - @author 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
  - @author Ferdinand Thiessen <opensource@fthiessen.de>
  -
  - @license GNU AGPL version 3 or any later version
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program.  If not, see <http://www.gnu.org/licenses/>.
  -->

<template>
	<form id="generate-app-token-section"
		class="row spacing"
		@submit.prevent="submit">
		<!-- Port to TextField component when available -->
		<NcTextField :value.sync="deviceName"
			type="text"
			:maxlength="120"
			:disabled="loading"
			class="app-name-text-field"
			:label="t('simplesettings', 'App name')"
			:placeholder="t('simplesettings', 'App name')" />
		<NcButton type="primary"
			:disabled="loading || deviceName.length === 0"
			native-type="submit">
			{{ t('simplesettings', 'Create new app password') }}
		</NcButton>

		<AuthTokenSetupDialog :token="newToken" @close="newToken = null" />
	</form>
</template>

<script lang="ts">
import { showError } from '@nextcloud/dialogs'
import { confirmPassword } from '@nextcloud/password-confirmation'
import { translate as t } from '@nextcloud/l10n'
import { defineComponent } from 'vue'
import { useAuthTokenStore, type ITokenResponse } from '../../store/authtoken'

// @ts-expect-error: Cannot find module or its corresponding type declarations.
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
// @ts-expect-error: Cannot find module or its corresponding type declarations.
import NcTextField from '@nextcloud/vue/dist/Components/NcTextField.js'

import AuthTokenSetupDialog from './AuthTokenSetupDialog.vue'
import logger from '../../logger'

export default defineComponent({
	name: 'AuthTokenSetup',
	components: {
		NcButton,
		NcTextField,
		AuthTokenSetupDialog,
	},
	setup() {
		const authTokenStore = useAuthTokenStore()
		return { authTokenStore }
	},
	methods: {
		t,
		reset() {
			this.loading = false
			this.deviceName = ''
			this.newToken = null
		},
		async submit() {
			try {
				this.loading = true
				await confirmPassword()
				this.newToken = await this.authTokenStore.addToken(this.deviceName)
			} catch (error) {
				logger.error(error as Error)
				showError(t('simplesettings', 'Error while creating device token'))
				this.reset()
			} finally {
				this.loading = false
				this.deviceName = ''
			}
		},
	},
	data() {
		return {
			deviceName: '',
			loading: false,
			newToken: null as ITokenResponse|null,
		}
	},
})
</script>

<style lang="scss" scoped>
	.app-name-text-field {
		height: 44px !important;
		padding-left: 12px;
		margin-right: 12px;
		width: 200px;
	}

	.row {
		display: flex;
		align-items: center;
	}

	.spacing {
		padding-top: 16px;
	}
</style>
