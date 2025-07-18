<?php

/**
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author Daniel Kesselberg <mail@danielkesselberg.de>
 * @author Fabrizio Steiner <fabrizio.steiner@gmail.com>
 * @author Greta Doci <gretadoci@gmail.com>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Marcel Waldvogel <marcel.waldvogel@uni-konstanz.de>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <robin@icewind.nl>
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 * @author Sergej Nikolaev <kinolaev@gmail.com>
 * @author Thomas Lehmann <t.lehmann@strato.de>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */
namespace OCA\SimpleSettings\Controller;

use BadMethodCallException;
use OC\Authentication\Exceptions\PasswordlessTokenException;
use OC\Authentication\Token\INamedToken;
use OC\Authentication\Token\IProvider;
use OC\Authentication\Token\RemoteWipe;
use OCA\Settings\Activity\Provider;
use OCP\Activity\IManager;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Authentication\Exceptions\ExpiredTokenException;
use OCP\Authentication\Exceptions\InvalidTokenException;
use OCP\Authentication\Exceptions\WipeTokenException;
use OCP\Authentication\Token\IToken;
use OCP\IRequest;
use OCP\ISession;
use OCP\IUserSession;
use OCP\Security\ISecureRandom;
use OCP\Session\Exceptions\SessionNotAvailableException;
use Psr\Log\LoggerInterface;

/**
 * Controller to configure app tokens.
 * @psalm-api
 */
class AuthSettingsController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private readonly IProvider $tokenProvider,
		private readonly ISession $session,
		private readonly ISecureRandom $random,
		private readonly ?string $userId,
		private readonly IUserSession $userSession,
		private readonly IManager $activityManager,
		private readonly RemoteWipe $remoteWipe,
		private readonly LoggerInterface $logger,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @NoAdminRequired
	 * @NoSubAdminRequired
	 * @PasswordConfirmationRequired
	 *
	 * @param string $name
	 * @return JSONResponse
	 */
	public function create($name) {
		if ($this->checkAppToken()) {
			return $this->getServiceNotAvailableResponse();
		}

		try {
			$sessionId = $this->session->getId();
		} catch (SessionNotAvailableException $ex) {
			return $this->getServiceNotAvailableResponse();
		}
		if ($this->userSession->getImpersonatingUserID() !== null) {
			return $this->getServiceNotAvailableResponse();
		}

		try {
			$sessionToken = $this->tokenProvider->getToken($sessionId);
			$loginName = $sessionToken->getLoginName();
			try {
				$password = $this->tokenProvider->getPassword($sessionToken, $sessionId);
			} catch (PasswordlessTokenException $ex) {
				$password = null;
			}
		} catch (InvalidTokenException $ex) {
			return $this->getServiceNotAvailableResponse();
		}

		if (mb_strlen($name) > 128) {
			$name = mb_substr($name, 0, 120) . '…';
		}

		$token = $this->generateRandomDeviceToken();
		$deviceToken = $this->tokenProvider->generateToken($token, $this->userId, $loginName, $password, $name, IToken::PERMANENT_TOKEN);
		$tokenData = $deviceToken->jsonSerialize();
		$tokenData['canDelete'] = true;
		$tokenData['canRename'] = true;

		$this->publishActivity(Provider::APP_TOKEN_CREATED, $deviceToken->getId(), ['name' => $deviceToken->getName()]);

		return new JSONResponse([
			'token' => $token,
			'loginName' => $loginName,
			'deviceToken' => $tokenData,
		]);
	}

	/**
	 * @return JSONResponse
	 */
	private function getServiceNotAvailableResponse() {
		$resp = new JSONResponse();
		$resp->setStatus(Http::STATUS_SERVICE_UNAVAILABLE);
		return $resp;
	}

	/**
	 * Return a 25 digit device password
	 *
	 * Example: AbCdE-fGhJk-MnPqR-sTwXy-23456
	 *
	 * @return string
	 */
	private function generateRandomDeviceToken() {
		$groups = [];
		for ($i = 0; $i < 5; $i++) {
			$groups[] = $this->random->generate(5, ISecureRandom::CHAR_HUMAN_READABLE);
		}
		return implode('-', $groups);
	}

	private function checkAppToken(): bool {
		return $this->session->exists('app_password');
	}

	/**
	 * @NoAdminRequired
	 * @NoSubAdminRequired
	 *
	 * @param int $id
	 * @return array|JSONResponse
	 */
	public function destroy($id) {
		if ($this->checkAppToken()) {
			return new JSONResponse([], Http::STATUS_BAD_REQUEST);
		}

		try {
			$token = $this->findTokenByIdAndUser($id);
		} catch (WipeTokenException $e) {
			//continue as we can destroy tokens in wipe
			$token = $e->getToken();
		} catch (InvalidTokenException $e) {
			return new JSONResponse([], Http::STATUS_NOT_FOUND);
		}

		$this->tokenProvider->invalidateTokenById($this->userId, $token->getId());
		$this->publishActivity(Provider::APP_TOKEN_DELETED, $token->getId(), ['name' => $token->getName()]);
		return [];
	}

	/**
	 * @NoAdminRequired
	 * @NoSubAdminRequired
	 *
	 * @param int $id
	 * @param array $scope
	 * @param string $name
	 * @return array|JSONResponse
	 */
	public function update($id, array $scope, string $name) {
		if ($this->checkAppToken()) {
			return new JSONResponse([], Http::STATUS_BAD_REQUEST);
		}

		try {
			$token = $this->findTokenByIdAndUser($id);
		} catch (InvalidTokenException $e) {
			return new JSONResponse([], Http::STATUS_NOT_FOUND);
		}

		$currentName = $token->getName();

		if ($scope !== $token->getScopeAsArray()) {
			$token->setScope(['filesystem' => $scope['filesystem']]);
			$this->publishActivity($scope['filesystem'] ? Provider::APP_TOKEN_FILESYSTEM_GRANTED : Provider::APP_TOKEN_FILESYSTEM_REVOKED, $token->getId(), ['name' => $currentName]);
		}

		if (mb_strlen($name) > 128) {
			$name = mb_substr($name, 0, 120) . '…';
		}

		if ($token instanceof INamedToken && $name !== $currentName) {
			$token->setName($name);
			$this->publishActivity(Provider::APP_TOKEN_RENAMED, $token->getId(), ['name' => $currentName, 'newName' => $name]);
		}

		$this->tokenProvider->updateToken($token);
		return [];
	}

	/**
	 * @param string $subject
	 * @param int $id
	 * @param array $parameters
	 */
	private function publishActivity(string $subject, int $id, array $parameters = []): void {
		$event = $this->activityManager->generateEvent();
		$event->setApp('settings')
			->setType('security')
			->setAffectedUser($this->userId)
			->setAuthor($this->userId)
			->setSubject($subject, $parameters)
			->setObject('app_token', $id, 'App Password');

		try {
			$this->activityManager->publish($event);
		} catch (BadMethodCallException $e) {
			$this->logger->warning('could not publish activity', ['exception' => $e]);
		}
	}

	/**
	 * Find a token by given id and check if uid for current session belongs to this token
	 *
	 * @param int $id
	 * @return IToken|INamedToken
	 * @throws InvalidTokenException
	 */
	private function findTokenByIdAndUser(int $id): IToken|INamedToken {
		try {
			$token = $this->tokenProvider->getTokenById($id);
		} catch (ExpiredTokenException $e) {
			$token = $e->getToken();
		}
		if ($token->getUID() !== $this->userId) {
			/** @psalm-suppress DeprecatedClass We have to throw the OC version so both OC and OCP catches catch it */
			throw new InvalidTokenException('This token does not belong to you!');
		}
		return $token;
	}

	/**
	 * @NoAdminRequired
	 * @NoSubAdminRequired
	 * @PasswordConfirmationRequired
	 *
	 * @param int $id
	 * @return JSONResponse
	 * @throws InvalidTokenException
	 * @throws ExpiredTokenException
	 */
	public function wipe(int $id): JSONResponse {
		if ($this->checkAppToken()) {
			return new JSONResponse([], Http::STATUS_BAD_REQUEST);
		}

		try {
			$token = $this->findTokenByIdAndUser($id);
		} catch (InvalidTokenException $e) {
			return new JSONResponse([], Http::STATUS_NOT_FOUND);
		}

		if (!$this->remoteWipe->markTokenForWipe($token)) {
			return new JSONResponse([], Http::STATUS_BAD_REQUEST);
		}

		return new JSONResponse([]);
	}
}
