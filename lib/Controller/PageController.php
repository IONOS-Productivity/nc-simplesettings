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

namespace OCA\SimpleSettings\Controller;

use OC\Authentication\Token\INamedToken;
use OC\Authentication\Token\IProvider;
use OC\Authentication\Token\IToken;
use OCA\SimpleSettings\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\OpenAPI;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\Authentication\Exceptions\InvalidTokenException;
use OCP\IConfig;
use OCP\ISession;
use OCP\IUser;
use OCP\IUserManager;
use OCP\IUserSession;
use OCP\L10N\IFactory;
use OCP\Session\Exceptions\SessionNotAvailableException;

/**
 * @psalm-suppress UnusedClass
 */
class PageController extends Controller {
	private IConfig $config;
	private IUserManager $userManager;
	private IFactory $l10nFactory;
	private IProvider $tokenProvider;
	private IInitialState $initialState;
	private IUserSession $userSession;
	private ISession $session;
	private string|null $uid;

	public function __construct(
		IConfig $config,
		IUserManager $userManager,
		IFactory $l10nFactory,
		IProvider $tokenProvider,
		ISession $session,
		IInitialState $initialState,
		IUserSession $userSession,
		?string $UserId
	) {
		$this->config = $config;
		$this->userManager = $userManager;
		$this->l10nFactory = $l10nFactory;
		$this->tokenProvider = $tokenProvider;
		$this->session = $session;
		$this->initialState = $initialState;
		$this->userSession = $userSession;
		$this->uid = $UserId;
	}

	#[NoCSRFRequired]
	#[NoAdminRequired]
	#[OpenAPI(OpenAPI::SCOPE_IGNORE)]
	#[FrontpageRoute(verb: 'GET', url: '/')]
	public function index(): TemplateResponse {
		$this->initialState->provideInitialState(
			'app_tokens',
			$this->getAppTokens()
		);

		$this->initialState->provideInitialState(
			'can_create_app_token',
			$this->userSession->getImpersonatingUserID() === null
		);

		$user = $this->userManager->get($this->uid);

		$this->initialState->provideInitialState(
			'personalInfoParameters',
			[
				'languageMap' => $this->getLanguageMap($user),
			]
		);

		$this->initialState->provideInitialState(
			'customClientURL',
			$this->getCustomClientURL()
		);

		return new TemplateResponse(
			Application::APP_ID,
			'index',
		);
	}

	private function getAppTokens(): array {
		$tokens = $this->tokenProvider->getTokenByUser($this->uid);

		try {
			$sessionId = $this->session->getId();
		} catch (SessionNotAvailableException $ex) {
			return [];
		}
		try {
			$sessionToken = $this->tokenProvider->getToken($sessionId);
		} catch (InvalidTokenException $ex) {
			return [];
		}

		return array_map(function (IToken $token) use ($sessionToken) {
			$data = $token->jsonSerialize();
			$data['canDelete'] = true;
			$data['canRename'] = $token instanceof INamedToken && $data['type'] !== IToken::WIPE_TOKEN;
			if ($sessionToken->getId() === $token->getId()) {
				$data['canDelete'] = false;
				$data['canRename'] = false;
				$data['current'] = true;
			}
			return $data;
		}, $tokens);
	}

	/**
	 * returns the user's active language, common languages, and other languages in an
	 * associative array
	 */
	private function getLanguageMap(IUser $user): array {
		$forceLanguage = $this->config->getSystemValue('force_language', false);
		if ($forceLanguage !== false) {
			return [];
		}

		$uid = $user->getUID();

		$userConfLang = $this->config->getUserValue($uid, 'core', 'lang', $this->l10nFactory->findLanguage());
		$languages = $this->l10nFactory->getLanguages();

		$combinedLanguages = array_merge(
			$languages['commonLanguages'],
			$languages['otherLanguages']
		);

		$userLangIndex = array_search($userConfLang, array_column($combinedLanguages, 'code'));
		$userLang = null;

		if ($userLangIndex !== false) {
			$userLang = $combinedLanguages[$userLangIndex];
		}

		// if user language is not available but set somehow: show the actual code as name
		if (!is_array($userLang)) {
			$userLang = [
				'code' => $userConfLang,
				'name' => $userConfLang,
			];
		}

		return [
			'activeLanguage' => $userLang,
			'allLanguages' => $combinedLanguages
		];
	}

	/**
	 * returns the custom client URLs
	 */
	private function getCustomClientURL(): array {
		return [
			'apps.android.url' => $this->config->getSystemValue('ionos_customclient_android'),
			'apps.ios.url' => $this->config->getSystemValue('ionos_customclient_ios'),
			'apps.windows.url' => $this->config->getSystemValue('ionos_customclient_windows'),
			'apps.macos.url' => $this->config->getSystemValue('ionos_customclient_macos'),
			'apps.ios.id' => $this->config->getSystemValue('ionos_customclient_ios_appid'),
		];
	}
}
