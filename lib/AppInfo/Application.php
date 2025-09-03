<?php

/**
 * SPDX-FileLicenseText: 2024 Thomas Lehmann <t.lehmann@strato.de>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

declare(strict_types=1);

namespace OCA\SimpleSettings\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\IRequest;
use OCP\Util;

class Application extends App implements IBootstrap {
	public const APP_ID = 'simplesettings';

	/** @psalm-suppress PossiblyUnusedMethod */
	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
	}

	/**
	 * Check if the current request is for a simplesettings page
	 *
	 * @param string $requestUri The request URI to check
	 * @return bool True if the request is for a simplesettings page
	 */
	private function isSimplesettingsPage(string $requestUri): bool {
		return str_starts_with($requestUri, '/apps/' . self::APP_ID . '/');
	}

	/**
	 * Handle request injection for simplesettings context
	 *
	 * @param IRequest $request The request object
	 * @return void
	 */
	private function handleRequestInjection(IRequest $request): void {
		// Only add the files search script when we're actually in the simplesettings context
		// This prevents interference with language detection on other pages
		try {
			$requestUri = $request->getPathInfo();
			// Only load files search script if we're in simplesettings context
			if ($this->isSimplesettingsPage($requestUri)) {
				Util::addScript('files', 'search');
			}
		} catch (\Exception) {
		}
	}

	public function boot(IBootContext $context): void {
		$context->injectFn(\Closure::fromCallable([$this, 'handleRequestInjection']));
	}
}
