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
	<tr :class="['auth-token', { 'auth-token--wiping': wiping }]" :data-id="token.id">
		<td>
			<NcIconSvgWrapper :path="tokenIcon" />
		</td>
		<td class="auth-token__name">
			<div class="auth-token__name-wrapper">
				<form v-if="token.canRename && renaming"
					class="auth-token__name-form"
					@submit.prevent.stop="rename">
					<NcTextField ref="input"
						:value.sync="newName"
						:label="t('simplesettings', 'Device name')"
						:show-trailing-button="true"
						:trailing-button-label="t('simplesettings', 'Cancel renaming')"
						@trailing-button-click="cancelRename"
						@keyup.esc="cancelRename" />
					<NcButton :aria-label="t('simplesettings', 'Save new name')" type="tertiary" native-type="submit">
						<template #icon>
							<NcIconSvgWrapper :path="mdiCheck" />
						</template>
					</NcButton>
				</form>
				<span v-else>{{ tokenLabel }}</span>
				<span v-if="wiping" class="wiping-warning">({{ t('simplesettings', 'Marked for remote wipe') }})</span>
			</div>
		</td>
		<td>
			<NcDateTime class="auth-token__last-activity"
				:ignore-seconds="true"
				:timestamp="tokenLastActivity" />
		</td>
		<td class="auth-token__actions">
			<NcActions v-if="!token.current"
				:title="t('simplesettings', 'Device settings')"
				:aria-label="t('simplesettings', 'Device settings')"
				:open.sync="actionOpen">
				<NcActionButton v-if="token.canRename"
					icon="icon-rename"
					@click.stop.prevent="startRename">
					<!-- TODO: add text/longtext with some description -->
					{{ t('simplesettings', 'Rename') }}
				</NcActionButton>

				<!-- revoke & wipe -->
				<template v-if="token.canDelete">
					<template v-if="token.type !== 2">
						<NcActionButton icon="icon-delete"
							@click.stop.prevent="revoke">
							<!-- TODO: add text/longtext with some description -->
							{{ t('simplesettings', 'Revoke') }}
						</NcActionButton>
						<NcActionButton icon="icon-delete"
							@click.stop.prevent="wipe">
							{{ t('simplesettings', 'Wipe device') }}
						</NcActionButton>
					</template>
					<NcActionButton v-else-if="token.type === 2"
						icon="icon-delete"
						:name="t('simplesettings', 'Revoke')"
						@click.stop.prevent="revoke">
						{{ t('simplesettings', 'Revoking this token might prevent the wiping of your device if it has not started the wipe yet.') }}
					</NcActionButton>
				</template>
			</NcActions>
		</td>
	</tr>
</template>

<script lang="ts">
import type { PropType } from 'vue'
import type { IToken } from '../../store/authtoken'

import { mdiCheck, mdiCellphone, mdiTablet, mdiMonitor, mdiWeb, mdiKey, mdiMicrosoftEdge, mdiFirefox, mdiGoogleChrome, mdiAppleSafari, mdiAndroid, mdiAppleIos } from '@mdi/js'
import { translate as t } from '@nextcloud/l10n'
import { defineComponent } from 'vue'
import { TokenType, useAuthTokenStore } from '../../store/authtoken'

// @ts-expect-error: Cannot find module or its corresponding type declarations.
import NcActions from '@nextcloud/vue/dist/Components/NcActions.js'
// @ts-expect-error: Cannot find module or its corresponding type declarations.
import NcActionButton from '@nextcloud/vue/dist/Components/NcActionButton.js'
// @ts-expect-error: Cannot find module or its corresponding type declarations.
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
// @ts-expect-error: Cannot find module or its corresponding type declarations.
import NcDateTime from '@nextcloud/vue/dist/Components/NcDateTime.js'
// @ts-expect-error: Cannot find module or its corresponding type declarations.
import NcIconSvgWrapper from '@nextcloud/vue/dist/Components/NcIconSvgWrapper.js'
// @ts-expect-error: Cannot find module or its corresponding type declarations.
import NcTextField from '@nextcloud/vue/dist/Components/NcTextField.js'

declare global {
	interface Window {
		oc_defaults: {
			baseUrl: string;
			docBaseUrl: string;
			docPlaceholderUrl: string;
			entity: string;
			folder: string;
			logoClaim: string;
			name: string;
			productName: string;
			slogan: string;
			syncClientUrl: string;
			title: string;
		};
	}
}

// When using capture groups the following parts are extracted the first is used as the version number, the second as the OS
const userAgentMap = {
	ie: /(?:MSIE|Trident|Trident\/7.0; rv)[ :](\d+)/,
	// Microsoft Edge User Agent from https://msdn.microsoft.com/en-us/library/hh869301(v=vs.85).aspx
	edge: /^Mozilla\/5\.0 \([^)]+\) AppleWebKit\/[0-9.]+ \(KHTML, like Gecko\) Chrome\/[0-9.]+ (?:Mobile Safari|Safari)\/[0-9.]+ Edge\/[0-9.]+$/,
	// Firefox User Agent from https://developer.mozilla.org/en-US/docs/Web/HTTP/Gecko_user_agent_string_reference
	firefox: /^Mozilla\/5\.0 \([^)]*(Windows|OS X|Linux)[^)]+\) Gecko\/[0-9.]+ Firefox\/(\d+)(?:\.\d)?$/,
	// Chrome User Agent from https://developer.chrome.com/multidevice/user-agent
	chrome: /^Mozilla\/5\.0 \([^)]*(Windows|OS X|Linux)[^)]+\) AppleWebKit\/[0-9.]+ \(KHTML, like Gecko\) Chrome\/(\d+)[0-9.]+ (?:Mobile Safari|Safari)\/[0-9.]+$/,
	// Safari User Agent from http://www.useragentstring.com/pages/Safari/
	safari: /^Mozilla\/5\.0 \([^)]*(Windows|OS X)[^)]+\) AppleWebKit\/[0-9.]+ \(KHTML, like Gecko\)(?: Version\/([0-9]+)[0-9.]+)? Safari\/[0-9.A-Z]+$/,
	// Android Chrome user agent: https://developers.google.com/chrome/mobile/docs/user-agent
	androidChrome: /Android.*(?:; (.*) Build\/).*Chrome\/(\d+)[0-9.]+/,
	iphone: / *CPU +iPhone +OS +([0-9]+)_(?:[0-9_])+ +like +Mac +OS +X */,
	ipad: /\(iPad; *CPU +OS +([0-9]+)_(?:[0-9_])+ +like +Mac +OS +X */,
	iosClient: /^Mozilla\/5\.0 \(iOS\) (?:ownCloud|Nextcloud)-iOS.*$/,
	androidClient: /^Mozilla\/5\.0 \(Android\) (?:ownCloud|Nextcloud)-android.*$/,
	iosTalkClient: /^Mozilla\/5\.0 \(iOS\) Nextcloud-Talk.*$/,
	androidTalkClient: /^Mozilla\/5\.0 \(Android\) Nextcloud-Talk.*$/,
	// DAVx5/3.3.8-beta2-gplay (2021/01/02; dav4jvm; okhttp/4.9.0) Android/10
	davx5: /DAV(?:droid|x5)\/([^ ]+)/,
	// Mozilla/5.0 (U; Linux; Maemo; Jolla; Sailfish; like Android 4.3) AppleWebKit/538.1 (KHTML, like Gecko) WebPirate/2.0 like Mobile Safari/538.1 (compatible)
	webPirate: /(Sailfish).*WebPirate\/(\d+)/,
	// Mozilla/5.0 (Maemo; Linux; U; Jolla; Sailfish; Mobile; rv:31.0) Gecko/31.0 Firefox/31.0 SailfishBrowser/1.0
	sailfishBrowser: /(Sailfish).*SailfishBrowser\/(\d+)/,
	// Neon 1.0.0+1
	neon: /Neon \d+\.\d+\.\d+\+\d+/,
}
const nameMap = {
	edge: 'Microsoft Edge',
	firefox: 'Firefox',
	chrome: 'Google Chrome',
	safari: 'Safari',
	androidChrome: t('simplesettings', 'Google Chrome for Android'),
	iphone: 'iPhone',
	ipad: 'iPad',
	iosClient: t('simplesettings', '{productName} iOS app', { productName: window.oc_defaults.productName }),
	androidClient: t('simplesettings', '{productName} Android app', { productName: window.oc_defaults.productName }),
	iosTalkClient: t('simplesettings', '{productName} Talk for iOS', { productName: window.oc_defaults.productName }),
	androidTalkClient: t('simplesettings', '{productName} Talk for Android', { productName: window.oc_defaults.productName }),
	syncClient: t('simplesettings', 'Sync client'),
	davx5: 'DAVx5',
	webPirate: 'WebPirate',
	sailfishBrowser: 'SailfishBrowser',
	neon: 'Neon',
}

export default defineComponent({
	name: 'AuthToken',
	components: {
		NcActions,
		NcActionButton,
		NcButton,
		NcDateTime,
		NcIconSvgWrapper,
		NcTextField,
	},
	props: {
		token: {
			type: Object as PropType<IToken>,
			required: true,
		},
	},
	setup() {
		const authTokenStore = useAuthTokenStore()
		return { authTokenStore }
	},
	data() {
		return {
			actionOpen: false,
			renaming: false,
			newName: '',
			oldName: '',
			mdiCheck,
		}
	},
	computed: {
		canChangeScope() {
			return this.token.type === TokenType.PERMANENT_TOKEN
		},
		/**
		 * @typedef {object} ClientInfo
		 * @property {string} id client type
		 * @property {string} os the OS the client is running on
		 * @property {string} version the client version
		 */
		/**
		 * Object ob the current user agend used by the token
		 * @return {ClientInfo} Either an object containing user agent information or null if unknown
		 */
		client() {
			// pretty format sync client user agent
			const matches = this.token.name.match(/Mozilla\/5\.0 \((\w+)\) (?:mirall|csyncoC)\/(\d+\.\d+\.\d+)/)

			if (matches) {
				return {
					id: 'syncClient',
					os: matches[1],
					version: matches[2],
				}
			}

			for (const client in userAgentMap) {
				const matches = this.token.name.match(userAgentMap[client])
				if (matches) {
					return {
						id: client,
						os: matches[2] && matches[1],
						version: matches[2] ?? matches[1],
					}
				}
			}

			return null
		},
		/**
		 * Last activity of the token as ECMA timestamp (in ms)
		 */
		tokenLastActivity() {
			return this.token.lastActivity * 1000
		},
		/**
		 * Icon to use for the current token
		 */
		tokenIcon() {
			// For custom created app tokens / app passwords
			if (this.token.type === TokenType.PERMANENT_TOKEN) {
				return mdiKey
			}

			switch (this.client?.id) {
			case 'edge':
				return mdiMicrosoftEdge
			case 'firefox':
				return mdiFirefox
			case 'chrome':
				return mdiGoogleChrome
			case 'safari':
				return mdiAppleSafari
			case 'androidChrome':
			case 'androidClient':
			case 'androidTalkClient':
				return mdiAndroid
			case 'iphone':
			case 'iosClient':
			case 'iosTalkClient':
				return mdiAppleIos
			case 'ipad':
				return mdiTablet
			case 'davx5':
				return mdiCellphone
			case 'syncClient':
				return mdiMonitor
			case 'webPirate':
			case 'sailfishBrowser':
			default:
				return mdiWeb
			}
		},
		/**
		 * Label to be shown for current token
		 */
		tokenLabel() {
			if (this.token.current) {
				return t('simplesettings', 'This session')
			}
			if (this.client === null) {
				return this.token.name
			}

			const name = nameMap[this.client.id]
			if (this.client.os) {
				return t('simplesettings', '{client} - {version} ({system})', { client: name, system: this.client.os, version: this.client.version })
			} else if (this.client.version) {
				return t('simplesettings', '{client} - {version}', { client: name, version: this.client.version })
			}
			return name
		},
		/**
		 * If the current token is considered for remote wiping
		 */
		wiping() {
			return this.token.type === TokenType.WIPING_TOKEN
		},
	},
	methods: {
		t,
		updateFileSystemScope(state: boolean) {
			this.authTokenStore.setTokenScope(this.token, 'filesystem', state)
		},
		startRename() {
			// Close action (popover menu)
			this.actionOpen = false

			this.oldName = this.token.name
			this.newName = this.token.name
			this.renaming = true
			this.$nextTick(() => {
				(this.$refs.input as HTMLInputElement)!.select()
			})
		},
		cancelRename() {
			this.renaming = false
		},
		revoke() {
			this.actionOpen = false
			this.authTokenStore.deleteToken(this.token)
		},
		rename() {
			this.renaming = false
			this.authTokenStore.renameToken(this.token, this.newName)
		},
		wipe() {
			this.actionOpen = false
			this.authTokenStore.wipeToken(this.token)
		},
	},
})
</script>

<style lang="scss" scoped>
@use '../../../../../core/css/variables.scss' as variables;

tr.auth-token {
	&:hover {
		background-color: var(--ion-button-sidebar-background-hover);
		color: var(--ion-button-sidebar-text);
	}
}

.auth-token {
	border-top: 2px solid var(--color-border);
	max-width: 200px;
	white-space: normal;
	vertical-align: middle;
	position: relative;

	&--wiping {
		background-color: var(--ion-button-sidebar-background-hover);
		color: var(--ion-button-sidebar-text);
	}

	&__name {
		padding-block: 10px;
		display: flex;
		align-items: center;
		gap: 6px;
		min-width: 355px; // ensure no jumping when renaming
	}

	&__name-wrapper {
		display: flex;
		flex-direction: column;
	}

	&__name-form {
		align-items: end;
		display: flex;
		gap: 4px;
	}

	&__actions {
		padding: 0 10px;
	}

	&__last-activity {
		padding-inline-start: 10px;
	}

	.wiping-warning {
		color: var(--color-text-maxcontrast);
	}
}

@media screen and (max-width: calc(variables.$breakpoint-mobile / 2)) {
	.auth-token__name {
		min-width: auto;
		padding-bottom: 0;
	}

	td:has(.auth-token__last-activity) {
		display: block;
		color: var(--color-text-lighter);

		& .auth-token__last-activity {
			padding-inline-start: 0;
		}
	}
}
</style>
