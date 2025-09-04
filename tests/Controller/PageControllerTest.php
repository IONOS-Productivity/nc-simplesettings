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
use OC\Authentication\Token\IProvider as IAuthTokenProvider;
use OC\Authentication\Token\IToken;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;
use OCP\IRequest;
use OCP\ISession;
use OCP\IUser;
use OCP\IUserManager;
use OCP\IUserSession;
use OCP\L10N\IFactory;
use OCP\Util;
use PHPUnit\Framework\MockObject\Exception;
use Test\TestCase;

/**
 * @psalm-suppress UnusedClass
 */
class PageControllerTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		$this->request = $this->createMock(IRequest::class);
		$this->config = $this->createMock(IConfig::class);
		$this->userManager = $this->createMock(IUserManager::class);
		$this->l10nFactory = $this->createMock(IFactory::class);
		$this->tokenProvider = $this->createMock(IAuthTokenProvider::class);
		$this->session = $this->createMock(ISession::class);
		$this->initialState = $this->createMock(IInitialState::class);
		$this->userSession = $this->createMock(IUserSession::class);
		$this->uid = 'mock-user-id-123';
		$this->util = $this->createMock(Util::class);
		$this->controller = $this->getMockBuilder(PageController::class)
			->setConstructorArgs([
				'core',
				$this->request,
				$this->config,
				$this->userManager,
				$this->l10nFactory,
				$this->tokenProvider,
				$this->session,
				$this->initialState,
				$this->userSession,
				$this->uid,
				$this->util
			])
			->onlyMethods(['getStorageInfo', 'humanFileSize'])
			->getMock();

		$mockCurrentSessionId = 'mock-session-id-123';

		$mockCurrentSessionTokenId = 1;
		$mockOtherSessionTokenIdNotWiped = 2;
		$mockOtherSessionTokenIdWiped = 3;

		$mockSessionAppToken = $this->createMockAppToken(
			$mockCurrentSessionTokenId,
			[
				// true to detect change to false
				'canDelete' => true,
				'canRename' => true,
				'type' => IToken::TEMPORARY_TOKEN,
				// false to detect change to true
				'current' => false,
			]
		);

		$this->session->expects($this->once())
			->method('getId')
			->willReturn($mockCurrentSessionId);

		$this->controller->expects($this->once())
			->method('getStorageInfo')
			->willReturn([
				'used' => 123,
				'quota' => 100,
				'total' => 100,
				'relative' => 123,
				'free' => 0,
				'owner' => 'MyName',
				'ownerDisplayName' => 'MyDisplayName',
			]);

		// mock humanFileSize
		$this->controller->expects($this->any())
			->method('humanFileSize')
			->willReturn('mocked-human-file-size');

		// Contains also the current session's token
		$mockTokenList = [
			$mockSessionAppToken,
			$this->createMockAppToken(
				$mockOtherSessionTokenIdNotWiped,
				[
					'canDelete' => false,
					// Wiped sessions can not be renamed; expect true, make false to detect change
					'canRename' => false,
					'type' => IToken::TEMPORARY_TOKEN,
					// Would not change, expect false too
					'current' => false,
				]
			),
			$this->createMockAppToken(
				$mockOtherSessionTokenIdWiped,
				[
					// Other sessions can be deleted; expect true, make false to detect change
					'canDelete' => false,
					// Wiped sessions can not be renamed; expect false, make true to detect change
					'canRename' => true,
					'type' => IToken::WIPE_TOKEN,
					// Would not change, expect true too
					'current' => true,
				]
			),
		];

		$this->tokenProvider->expects($this->once())
			->method('getTokenByUser')
			->with($this->equalTo($this->uid))
			->willReturn($mockTokenList);

		$this->tokenProvider->expects($this->once())
			->method('getToken')
			->willReturn($mockSessionAppToken);

		// Mocks for getLanguageMap()

		$this->mockedForcedLanguage = false;

		$this->mockUserUid = 'some-mock-uid';
		$this->mockConfiguredUserLanguage = 'xx-XX';
		$this->mockAvailableLanguages = [
			'commonLanguages' => [
				['code' => 'de-DE', 'name' => 'Deutsch'],
			],
			'otherLanguages' => [
				['code' => 'ru-RU', 'name' => 'Русский'],
			]
		];

		$this->mockConfigValues = [
			'force_language' => $this->mockedForcedLanguage,
			'ionos_customclient_android' => 'mocked-android-url',
			'ionos_customclient_ios' => 'mocked-ios-url',
			'ionos_customclient_ios_appid' => 'mocked-ios-appid',
			'ionos_customclient_windows' => 'mocked-windows-url',
			'ionos_customclient_macos' => 'mocked-macos-url',
		];

		$mockUser = $this->createMock(IUser::class);

		$mockUser->expects($this->atMost(1))
			->method('getUID')
			->willReturn($this->mockUserUid);

		$this->userManager->expects($this->atMost(1))
			->method('get')
			->with($this->equalTo($this->uid))
			->willReturn($mockUser);

		$this->config->expects($this->atMost(6))
			->method('getSystemValue')
			->with(
				$this->logicalOr(
					$this->equalTo('force_language'),
					$this->equalTo('ionos_customclient_android'),
					$this->equalTo('ionos_customclient_ios'),
					$this->equalTo('ionos_customclient_ios_appid'),
					$this->equalTo('ionos_customclient_windows'),
					$this->equalTo('ionos_customclient_macos'),
				),
				$this->anything() // This catches any default value passed
			)
			->willReturnCallback(fn ($_propertyName, $_defaultValue) => $this->mockConfigValues[$_propertyName] ?? $_defaultValue);

		$this->l10nFactory->expects($this->atMost(1))
			->method('findLanguage')
			->willReturnCallback(fn () => $this->mockConfiguredUserLanguage);

		$this->config->expects($this->atMost(1))
			->method('getUserValue')
			->with($this->mockUserUid, 'core', 'lang', $this->anything())
			->willReturnCallback(fn ($userId, $_appName, $_properyName, $_defaultValue) => $this->mockConfiguredUserLanguage);

		$this->l10nFactory->expects($this->atMost(1))
			->method('getLanguages')
			->willReturn($this->mockAvailableLanguages);

		$this->resetUtilState();
	}

	protected function tearDown(): void {
		$this->resetUtilState();
		parent::tearDown();
	}

	/**
	 * Reset Util script and style state for clean test isolation
	 */
	private function resetUtilState(): void {
		\OC_Util::$scripts = [];
		\OC_Util::$styles = [];
		self::invokePrivate(\OCP\Util::class, 'scripts', [[]]);
		self::invokePrivate(\OCP\Util::class, 'scriptDeps', [[]]);
	}

	/**
	 * Create a mock App token with the specified ID and serialized JSON object
	 * @param $id int
	 * @param $serializedJson array associative array with fields canDelete, canRename, type, current
	 * @return IToken
	 * @throws \PHPUnit\Framework\MockObject\Exception
	 */
	private function createMockAppToken(int $id, array $serializedJson = []): IToken {
		$token = $this->createMock(INamedToken::class);

		$token->expects($this->atLeastOnce())
			->method('getId')
			->willReturn($id);

		$token->expects($this->atLeastOnce())
			->method('jsonSerialize')
			->willReturn($serializedJson);

		return $token;
	}

	/**
	 * @throws Exception
	 */
	public function testIndexProvidesInitialStateWithAppTokens() {
		$expectedAppTokensRegisteredAsInitialState = [
			[
				'canDelete' => false,
				'canRename' => false,
				'type' => IToken::TEMPORARY_TOKEN,
				'current' => true,
			],
			[
				'canDelete' => true,
				'canRename' => true,
				'type' => IToken::TEMPORARY_TOKEN,
				'current' => false,
			],
			[
				'canDelete' => true,
				'canRename' => false,
				'type' => IToken::WIPE_TOKEN,
				'current' => true,
			],
		];

		$this->initialState->expects($this->exactly(4))
			->method('provideInitialState')
			->willReturnCallback(function ($stateName, $stateValue) use ($expectedAppTokensRegisteredAsInitialState) {
				if ($stateName == 'app_tokens') {
					$this->assertEquals($expectedAppTokensRegisteredAsInitialState, $stateValue);
				}
			});

		$this->controller->index();
	}

	/**
	 * @throws Exception
	 */
	public function testIndexProvidesInitialStateWithCanCreateAppTokensFalse() {
		$this->userSession->getImpersonatingUserID();

		$this->userSession->expects($this->exactly(1))
			->method('getImpersonatingUserID')
			->willReturn('some-user-id-is-not-null');

		$this->initialState->expects($this->exactly(4))
			->method('provideInitialState')
			->willReturnCallback(function ($stateName, $stateValue) {
				if ($stateName == 'can_create_app_token') {
					$this->assertEquals(false, $stateValue);
				}
			});

		$this->controller->index();
	}

	/**
	 * @throws Exception
	 */
	public function testIndexProvidesInitialStateWithCanCreateAppTokensTrue() {
		$this->userSession->expects($this->exactly(1))
			->method('getImpersonatingUserID')
			->willReturn(null);

		$this->initialState->expects($this->exactly(4))
			->method('provideInitialState')
			->willReturnCallback(function ($stateName, $stateValue) {
				if ($stateName == 'can_create_app_token') {
					$this->assertEquals(true, $stateValue);
				}
			});

		$this->controller->index();
	}

	private function configureInitialStateLanguageMock($expectedActiveLanguage) {
		$mockAvailableLanguages = $this->mockAvailableLanguages;

		$this->initialState->expects($this->exactly(4))
			->method('provideInitialState')
			->willReturnCallback(function ($stateName, $stateValue) use ($expectedActiveLanguage, $mockAvailableLanguages) {
				if ($stateName == 'personalInfoParameters') {
					$this->assertEquals([
						'languageMap' => [
							'activeLanguage' => $expectedActiveLanguage,
							'allLanguages' => array_merge(
								$mockAvailableLanguages['commonLanguages'],
								$mockAvailableLanguages['otherLanguages'],
							)
						],
						'totalSpace' => 'mocked-human-file-size',
						'freeSpace' => 'mocked-human-file-size',
						'usage' => 'mocked-human-file-size',
						'usageRelative' => 123.0,
					], $stateValue);
				}
			});
	}

	/**
	 * @throws Exception
	 */
	public function testIndexProvidesInitialStateWithLanguagesLanguageForced() {
		$this->mockConfigValues['force_language'] = true;

		$this->initialState->expects($this->exactly(4))
			->method('provideInitialState')
			->willReturnCallback(function ($stateName, $stateValue) {
				if ($stateName == 'personalInfoParameters') {
					$this->assertEquals([
						'languageMap' => [],
						'totalSpace' => 'mocked-human-file-size',
						'freeSpace' => 'mocked-human-file-size',
						'usage' => 'mocked-human-file-size',
						'usageRelative' => 123.0,
					], $stateValue);
				}
			});

		$this->controller->index();
	}

	/**
	 * @throws Exception
	 */
	public function testIndexProvidesInitialStateWithLanguagesNoLanguageForcedAndConfiguredLanguageNotFound() {
		$this->configureInitialStateLanguageMock(['code' => $this->mockConfiguredUserLanguage, 'name' => $this->mockConfiguredUserLanguage]);
		$this->controller->index();
	}

	/**
	 * @throws Exception
	 */
	public function testIndexProvidesInitialStateWithLanguagesNoLanguageForcedAndConfiguredLanguageFound() {
		$this->mockConfiguredUserLanguage = 'de-DE';
		$this->configureInitialStateLanguageMock(['code' => $this->mockConfiguredUserLanguage, 'name' => 'Deutsch']);
		$this->controller->index();
	}

	/**
	 * @throws Exception
	 */
	public function testIndexProvidesInitialStateWithCustomClientURLs() {
		$expectedCustomClientsURLs = [
			'apps.android.url' => 'mocked-android-url',
			'apps.ios.url' => 'mocked-ios-url',
			'apps.ios.id' => 'mocked-ios-appid',
			'apps.windows.url' => 'mocked-windows-url',
			'apps.macos.url' => 'mocked-macos-url',
		];

		$this->initialState->expects($this->exactly(4))
			->method('provideInitialState')
			->willReturnCallback(function ($stateName, $stateValue) use ($expectedCustomClientsURLs) {
				if ($stateName == 'customClientURL') {
					$this->assertEquals($expectedCustomClientsURLs, $stateValue);
				}
			});

		$this->controller->index();
	}

	public function testFileSearchScriptInjection(): void {
		$scripts = Util::getScripts();

		$this->assertNotContains('files/js/search', $scripts, 'File search script should NOT be injected');

		$this->controller->index();

		$scripts = Util::getScripts();

		$this->assertContains('files/l10n/en', $scripts, 'File search script i18n should be injected');
		$this->assertContains('files/js/search', $scripts, 'File search script should be injected');
	}
}
