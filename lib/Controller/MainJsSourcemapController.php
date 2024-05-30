<?php

namespace OCA\SimpleSettings\Controller;

use OCA\SimpleSettings\Config;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\AppFramework\Http\TextPlainResponse;
use OCP\IConfig;
use OCP\IRequest;

class JavaScriptController extends Controller {


	/**
	 * constructor of the controller
	 *
	 * @param string $appName
	 * @param IRequest $request
	 */
	public function __construct($appName,
								IRequest $request) {
		parent::__construct($appName, $request);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 *
	 * @return DataDownloadResponse
	 */
	public function sourceMap(): DataDownloadResponse {
		$script = file_get_contents(__DIR__ . '/../../js/main.js.map');
		return new DataDownloadResponse($script, 'main.js.map', 'text/javascript');
	}
}
