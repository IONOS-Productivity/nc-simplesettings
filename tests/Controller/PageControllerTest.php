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
use OCP\ISession;
use OCP\IUserManager;
use OCP\IUserSession;
use OCP\L10N\IFactory;
use PHPUnit\Framework\MockObject\Exception;
use Test\TestCase;

/**
 * @psalm-suppress UnusedClass
 */
class PageControllerTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock(IConfig::class);
		$this->userManager = $this->createMock(IUserManager::class);
		$this->l10nFactory = $this->createMock(IFactory::class);
		$this->tokenProvider = $this->createMock(IAuthTokenProvider::class);
		$this->session = $this->createMock(ISession::class);
		$this->initialState = $this->createMock(IInitialState::class);
		$this->userSession = $this->createMock(IUserSession::class);
		$this->uid = "mock-user-id-123";

		$this->controller = new PageController(
			$this->config,
			$this->userManager,
			$this->l10nFactory,
			$this->tokenProvider,
			$this->session,
			$this->initialState,
			$this->userSession,
			$this->uid
		);

		$mockCurrentSessionId = "mock-session-id-123";

		$mockCurrentSessionTokenId = 1;
		$mockOtherSessionTokenIdNotWiped = 2;
		$mockOtherSessionTokenIdWiped = 3;

		$mockSessionAppToken = $this->createMockAppToken(
			$mockCurrentSessionTokenId,
			[
				// true to detect change to false
				"canDelete" => true,
				"canRename" => true,
				"type" => IToken::TEMPORARY_TOKEN,
				// false to detect change to true
				"current" => false,
			]
		);

		$this->session->expects($this->once())
			->method('getId')
			->willReturn($mockCurrentSessionId);

		// Contains also the current session's token
		$mockTokenList = [
			$mockSessionAppToken,
			$this->createMockAppToken(
				$mockOtherSessionTokenIdNotWiped,
				[
					"canDelete" => false,
					// Wiped sessions can not be renamed; expect true, make false to detect change
					"canRename" => false,
					"type" => IToken::TEMPORARY_TOKEN,
					// Would not change, expect false too
					"current" => false,
				]
			),
			$this->createMockAppToken(
				$mockOtherSessionTokenIdWiped,
				[
					// Other sessions can be deleted; expect true, make false to detect change
					"canDelete" => false,
					// Wiped sessions can not be renamed; expect false, make true to detect change
					"canRename" => true,
					"type" => IToken::WIPE_TOKEN,
					// Would not change, expect true too
					"current" => true,
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
				"canDelete" => false,
				"canRename" => false,
				"type" => IToken::TEMPORARY_TOKEN,
				"current" => true,
			],
			[
				"canDelete" => true,
				"canRename" => true,
				"type" => IToken::TEMPORARY_TOKEN,
				"current" => false,
			],
			[
				"canDelete" => true,
				"canRename" => false,
				"type" => IToken::WIPE_TOKEN,
				"current" => true,
			],
		];

		$this->initialState->expects($this->exactly(2))
			->method('provideInitialState')
			->willReturnCallback(function($stateName, $stateValue) use ($expectedAppTokensRegisteredAsInitialState) {
				if ($stateName == "app_tokens") {
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
			->willReturn("some-user-id-is-not-null");

		$this->initialState->expects($this->exactly(2))
			->method('provideInitialState')
			->willReturnCallback(function ($stateName, $stateValue) {
				if ($stateName == "can_create_app_token") {
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

		$this->initialState->expects($this->exactly(2))
			->method('provideInitialState')
			->willReturnCallback(function($stateName, $stateValue) {
				if ($stateName == "can_create_app_token") {
					$this->assertEquals(true, $stateValue);
				}
			});

		$this->controller->index();
	}
}
