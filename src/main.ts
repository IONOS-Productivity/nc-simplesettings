/*
 * SPDX-FileLicenseText: 2024 Thomas Lehmann <t.lehmann@strato.de>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

import { createApp } from 'vue'
import { translate as t, translatePlural as n } from '@nextcloud/l10n'
import { createPinia } from 'pinia'
import App from './App.vue'

const app = createApp(App)
const pinia = createPinia()

// Make t() and n() available in all component templates without per-component import
app.config.globalProperties.t = t
app.config.globalProperties.n = n

app.use(pinia)
app.mount('#simplesettings')
