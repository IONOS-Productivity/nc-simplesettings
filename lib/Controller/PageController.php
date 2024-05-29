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
	
	public function __construct(
		IAuthTokenProvider $tokenProvider,
		ISession $session,
		IInitialState $initialState,
		IUserSession $userSession
	) {
		$this->tokenProvider = $tokenProvider;
		$this->session = $session;
		$this->initialState = $initialState;

		$this->userSession = $userSession;
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
		$tokens = $this->tokenProvider->getTokenByUser(/* TODO */ "ebf4d899-c4c8-4974-8389-42f461f9a0e8");

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
