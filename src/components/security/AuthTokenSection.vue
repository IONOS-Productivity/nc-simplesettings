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
	<div id="security" class="section">
		<h2>{{ t('simplesettings', 'Devices & sessions', {}, undefined, {sanitize: false}) }}</h2>
		<p class="settings-hint hidden-when-empty">
			{{ t('simplesettings', 'Web, desktop and mobile clients currently logged in to your account.') }}
		</p>
		<AuthTokenList @wipe="doWipe" />
		<AuthTokenSetup v-if="canCreateToken" />
		<NcDialog
			:open.sync="confirmingWipe"
			:name="t('simplesettings', 'Confirm wipe')"
			content-classes="wipe-dialog">
			{{ t('simplesettings', 'Do you really want to wipe your data from this device?') }}
			<div class="button-row">
				<NcButton
					@click="cancelWipe">
					{{ t('simplesettings', 'Cancel') }}
				</NcButton>

				<NcButton icon="icon-delete"
					@click="confirmedWipe">
					{{ t('simplesettings', 'Confirm wipe') }}
				</NcButton>
			</div>
		</NcDialog>
	</div>
</template>

<script lang="ts">
// @ts-expect-error: Cannot find module or its corresponding type declarations.
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
// @ts-expect-error: Cannot find module or its corresponding type declarations.
import NcDialog from '@nextcloud/vue/dist/Components/NcDialog.js'

import { confirmPassword } from '@nextcloud/password-confirmation'
import { loadState } from '@nextcloud/initial-state'
import { translate as t } from '@nextcloud/l10n'
import { useAuthTokenStore, type IToken } from '../../store/authtoken'
import { defineComponent } from 'vue'

import AuthTokenList from './AuthTokenList.vue'
import AuthTokenSetup from './AuthTokenSetup.vue'

export default defineComponent({
	name: 'AuthTokenSection',
	components: {
		NcButton,
		NcDialog,
		AuthTokenList,
		AuthTokenSetup,
	},
	setup() {
		const authTokenStore = useAuthTokenStore()
		return { authTokenStore }
	},
	data() {
		return {
			canCreateToken: loadState('simplesettings', 'can_create_app_token'),
			tokenToBeWiped: null as IToken | null,
			confirmingWipe: false,
		}
	},
	methods: {
		t,
		async doWipe(token: IToken) {
			this.tokenToBeWiped = token
			this.confirmingWipe = true

			await confirmPassword()
		},
		async cancelWipe() {
			this.tokenToBeWiped = null
			this.confirmingWipe = false
		},
		async confirmedWipe() {
			if (this.tokenToBeWiped === null) {
				return
			}
			await this.authTokenStore.wipeToken(this.tokenToBeWiped as IToken)
			this.tokenToBeWiped = null
			this.confirmingWipe = false
		},
	},
})
</script>

<style scoped>
.button-row {
	display: flex;
	flex-direction: row;
	margin: 10px 0;
	gap: 4px;
}
</style>
