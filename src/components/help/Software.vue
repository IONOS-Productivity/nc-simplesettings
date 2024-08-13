<!--
SPDX-FileLicenseText: 2024 Franziska Bath <franziska.bath@strato.de>
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
	<div id="software" class="section">
		<h2>{{ t('simplesettings', 'App & Software') }}</h2>
		<p class="settings-hint hidden-when-empty">
			{{ t('simplesettings', 'Download app for your mobile device') }}
		</p>
		<br>
		<div class="mobile-apps">
			<div class="ios">
				<a
					role="button"
					class="mobile-link"
					:href="iosUrl"
					target="_blank">
					<img :src="iosSVG" alt="App Store">
				</a>
				<VueQrcode
					tag="img"
					class="qr-code"
					:value="iosUrl"
					:options="{
						width: 150,
						margin: 1,
					}" />
			</div>
			<div class="android">
				<a
					role="button"
					class="mobile-link"
					:href="androidUrl"
					target="_blank">
					<img :src="androidSVG" alt="Play Store">
				</a>
				<VueQrcode
					tag="img"
					class="qr-code"
					:value="androidUrl"
					:options="{
						width: 150,
						margin: 1,
					}" />
			</div>
		</div>
		<br>
		<p class="settings-hint hidden-when-empty">
			{{ t('simplesettings', 'Download app for desktop clients') }}
		</p>
		<br>
		<div class="desktop-apps">
			<NcButton
				type="primary"
				:href="macosUrl">
				{{ t('simplesettings', 'Install desktop app for Mac') }}
				<span class="symbol">&ensp;❯&ensp;</span>
			</NcButton>
			<NcButton
				type="primary"
				:href="windowsUrl">
				{{ t('simplesettings', 'Install desktop app for Windows') }}
				<span class="symbol">&ensp;❯&ensp;</span>
			</NcButton>
		</div>
	</div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
// @ts-expect-error: Cannot find module or its corresponding type declarations.
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import { imagePath } from '@nextcloud/router'
import links from '../../links.json'
import { translate as t, getLanguage } from '@nextcloud/l10n'
import VueQrcode from '@chenfengyuan/vue-qrcode'

const currentLanguage = getLanguage().substring(0, 2)
const appLinks = links[currentLanguage]

export default defineComponent({
	name: 'Software',
	components: {
		NcButton,
		VueQrcode,
	},
	data() {
		const androidSVG = imagePath('simplesettings', 'software/mobilePlayStore.svg')
		const iosSVG = imagePath('simplesettings', 'software/mobileAppStore.svg')
		const androidUrl = appLinks['apps.android.url']
		const iosUrl = appLinks['apps.ios.url']
		const macosUrl = appLinks['apps.macos.url']
		const windowsUrl = appLinks['apps.windows.url']

		return {
			androidUrl,
			iosUrl,
			macosUrl,
			windowsUrl,
			androidSVG,
			iosSVG,
		}
	},
	methods: {
		t,
	},
})
</script>

<style lang="scss" scoped>

#software {
	--software-content-width: 39em;
}

.mobile-apps {
	display: flex;
	justify-content: space-between;
	padding-bottom: 2em;
	max-width: var(--software-content-width);

	.ios, .android {
		display: flex;
		flex-direction: column;
	}

	.android {
		padding-right: 8em;
	}

	.mobile-link {
		padding-bottom: .6em;
	}
}

.desktop-apps {
	display: flex;
	justify-content: space-between;
	max-width: var(--software-content-width);
}

.symbol {
	font-size: 10px;
	vertical-align: middle;
}

@media (max-width: 600px) {
	.mobile-apps {
		gap: 1em;
		padding-bottom: 0;

		.ios, .android {
			max-width: 15em;
		}

		.android {
			padding-right: 0;
		}
	}

	.desktop-apps {
		flex-direction: column;
		gap: 1em;
	}
}
</style>
