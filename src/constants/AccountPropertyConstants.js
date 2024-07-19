/*
 *SPDX-FileLicenseText: 2021 Christopher Ng <chrng8@gmail.com>
 *SPDX-FileLicenseText: 2024 Tatjana Kaschperko Lindt <kaschperko-lindt@strato.de>
 *SPDX-License-Identifier: AGPL-3.0-or-later
 *
 *This program is free software: you can redistribute it and/or modify
 *it under the terms of the GNU Affero General Public License as
 *published by the Free Software Foundation, either version 3 of the
 *License, or (at your option) any later version.
 *
 *This program is distributed in the hope that it will be useful,
 *but WITHOUT ANY WARRANTY; without even the implied warranty of
 *MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *GNU Affero General Public License for more details.
 *
 *You should have received a copy of the GNU Affero General Public License
 *along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/*
 * SYNC to be kept in sync with `lib/public/Accounts/IAccountManager.php`
 */

import { translate as t } from '@nextcloud/l10n'

/**
 * Enum of account setting properties
 *
 * Account setting properties unlike account properties do not support scopes*
 */
export const ACCOUNT_SETTING_PROPERTY_ENUM = Object.freeze({
	LANGUAGE: 'language',
})

/** Enum of account setting properties to human readable setting properties */
export const ACCOUNT_SETTING_PROPERTY_READABLE_ENUM = Object.freeze({
	LANGUAGE: t('simplesettings', 'Language'),
})
