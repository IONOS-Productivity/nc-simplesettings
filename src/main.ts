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

import Vue from 'vue'
import { PiniaVuePlugin, createPinia } from 'pinia'
import App from './AddFileAction.vue'

declare function n(a: string): string;
declare function t(a: string): string;

const pinia = createPinia()

Vue.use(PiniaVuePlugin)

Vue.mixin({ methods: { t, n } })

const View = Vue.extend(App)
new View({ pinia }).$mount('#simplesettings')
