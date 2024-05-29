<?php

declare(strict_types=1);

namespace OCA\SimpleSettings\Controller;

use OC\Authentication\Token\IProvider as IAuthTokenProvider;
use OC\Authentication\Token\INamedToken;
// TODO what's the preferred way?
use OC\Authentication\Token\IToken;
use OCA\SimpleSettings\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\ISession;
use OCP\IUserSession;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\OpenAPI;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\Authentication\Exceptions\InvalidTokenException;
use OCP\Session\Exceptions\SessionNotAvailableException;

/**
 * @psalm-suppress UnusedClass
 */
class PageController extends Controller {
	/** @var IInitialState */
	private $initialState;

	/** @var IUserSession */
	private $userSession;

	/** @var ISession */
	private $session;

	/** @var string|null */
	private $uid;

	public function __construct(
		IAuthTokenProvider $tokenProvider,
		ISession $session,
		IInitialState $initialState,
		IUserSession $userSession,
		?string $UserId
	) {
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
}
