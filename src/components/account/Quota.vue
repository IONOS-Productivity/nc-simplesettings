<!--
SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
SPDX-FileCopyrightText: 2024 STRATO AG
SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<section class="section">
		<h2>{{ t('simplesettings', 'Storage usage') }}</h2>
		<div class="quota">
			<NcProgressBar
				v-if="hasLimitedSpace"
				size="medium"
				:value="usageRelative"
				:color="barColor"
				:style="{'background-color': backgroundColor}" />
			<div class="quota-info">
				<p>
					<span v-if="hasLimitedSpace" :style="quotaPillStyles(barColor)" />
					<span>
						{{ quotaUsedPrefix }}<strong>{{ usage }}</strong>
					</span>
				</p>
				<p v-if="hasLimitedSpace">
					<span :style="quotaPillStyles(backgroundColor)" />
					<span>
						{{ quotaFreePrefix }}<strong>{{ freeSpace }}</strong>
					</span>
				</p>
			</div>
		</div>
	</section>
</template>

<script>
import { loadState } from '@nextcloud/initial-state'
import NcProgressBar from '@nextcloud/vue/dist/Components/NcProgressBar.js'

const { totalSpace, freeSpace, usage, usageRelative } = loadState('simplesettings', 'personalInfoParameters', {})
const hasLimitedSpace = totalSpace !== 'Unlimited'

export default {
	name: 'Quota',

	components: {
		NcProgressBar,
	},

	data() {
		return {
			usageRelative,
			barColor: null,
			backgroundColor: null,
			hasLimitedSpace,
			freeSpace,
			usage,
		}
	},

	mounted() {
		const styles = getComputedStyle(document.documentElement)
		this.barColor = styles.getPropertyValue('--ion-color-blue-b4')
		this.backgroundColor = styles.getPropertyValue('--ion-color-cool-grey-c2')
	},

	computed: {
		quotaUsedPrefix() {
			return t('simplesettings', 'Used: ')
		},
		quotaFreePrefix() {
			return t('simplesettings', 'Free: ')
		},
	},

	methods: {
		quotaPillStyles(color) {
			return {
				'background-color': color,
				'border-radius': '10px',
				'margin-right': '12px',
				display: 'inline-block',
				height: '10px',
				width: '20px',
			}
		},
	},
}
</script>

<style lang="scss" scoped>
.quota {
	display: flex;
	flex-direction: column;
	width: 66.6%;
	padding-top: 5px;
	gap: 20px 0;

	&-info {
		display: flex;
		flex-direction: column;
		width: 100%;
		gap: 8px 0;
	}
}
</style>
