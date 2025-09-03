<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 STRATO GmbH
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SimpleSettings\Tests\AppInfo;

use OCA\SimpleSettings\AppInfo\Application;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\IRequest;
use Test\TestCase;

/**
 * @group DB
 */
class ApplicationTest extends TestCase {
	private Application $application;

	protected function setUp(): void {
		parent::setUp();
		$this->application = new Application();
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
	 * Get a private method from the Application class for testing
	 */
	private function getPrivateMethod(string $methodName): \ReflectionMethod {
		$reflection = new \ReflectionClass($this->application);
		$method = $reflection->getMethod($methodName);
		$method->setAccessible(true);
		return $method;
	}

	// Basic functionality tests

	public function testAppId(): void {
		$this->assertEquals('simplesettings', Application::APP_ID);
		$this->assertIsString(Application::APP_ID);
		$this->assertNotEmpty(Application::APP_ID);
	}

	public function testConstructor(): void {
		$this->assertInstanceOf(Application::class, $this->application);
	}

	public function testImplementsRequiredInterfaces(): void {
		$this->assertInstanceOf(\OCP\AppFramework\Bootstrap\IBootstrap::class, $this->application);
		$this->assertInstanceOf(\OCP\AppFramework\App::class, $this->application);
	}

	// Bootstrap lifecycle tests

	public function testRegister(): void {
		$context = $this->createMock(IRegistrationContext::class);

		// The register method is currently empty, so we just verify it can be called without exception
		$this->expectNotToPerformAssertions();
		$this->application->register($context);
	}

	public function testBoot(): void {
		$context = $this->createMock(IBootContext::class);

		$context->expects($this->once())
			->method('injectFn')
			->with($this->isInstanceOf(\Closure::class));

		$this->application->boot($context);
	}

	public function testBootInjectsCorrectFunction(): void {
		$context = $this->createMock(IBootContext::class);

		$capturedFunction = null;
		$context->expects($this->once())
			->method('injectFn')
			->willReturnCallback(function ($fn) use (&$capturedFunction) {
				$capturedFunction = $fn;
			});

		$this->application->boot($context);

		$this->assertInstanceOf(\Closure::class, $capturedFunction);

		// Test that the injected function works correctly
		$mockRequest = $this->createMock(IRequest::class);
		$mockRequest->method('getPathInfo')->willReturn('/apps/simplesettings/test');

		// Verify the closure can be invoked without exception
		$capturedFunction($mockRequest);
		$this->addToAssertionCount(1); // Explicitly count the successful execution
	}

	// Path detection tests

	/**
	 * @dataProvider validPathsProvider
	 */
	public function testIsSimplesettingsPageWithValidPath(string $path): void {
		$method = $this->getPrivateMethod('isSimplesettingsPage');
		$this->assertTrue($method->invoke($this->application, $path));
	}

	public function validPathsProvider(): array {
		return [
			'root path' => ['/apps/simplesettings/'],
			'settings page' => ['/apps/simplesettings/settings'],
			'admin page' => ['/apps/simplesettings/admin'],
			'any subpage' => ['/apps/simplesettings/any/subpage'],
		];
	}

	/**
	 * @dataProvider invalidPathsProvider
	 */
	public function testIsSimplesettingsPageWithInvalidPath(string $path): void {
		$method = $this->getPrivateMethod('isSimplesettingsPage');
		$this->assertFalse($method->invoke($this->application, $path));
	}

	public function invalidPathsProvider(): array {
		return [
			'files app' => ['/apps/files/'],
			'settings page' => ['/settings/'],
			'other app' => ['/apps/other/'],
			'empty string' => [''],
			'similar but different' => ['/apps/simplesetting/'], // missing 's'
			'root' => ['/'],
			// Edge cases
			'case sensitivity - upper' => ['/apps/SimpleSettings/'],
			'case sensitivity - all caps' => ['/APPS/simplesettings/'],
			'partial match' => ['/apps/simplesetting'],
			'missing leading slash' => ['apps/simplesettings/'],
		];
	}

	public function testAppIdConsistency(): void {
		$method = $this->getPrivateMethod('isSimplesettingsPage');
		$expectedPath = '/apps/' . Application::APP_ID . '/test';
		$this->assertTrue($method->invoke($this->application, $expectedPath));
	}

	// Script injection tests

	/**
	 * @dataProvider scriptInjectionProvider
	 */
	public function testHandleRequestInjection(string $path, bool $shouldInjectScript): void {
		$request = $this->createMock(IRequest::class);
		$request->expects($this->once())
			->method('getPathInfo')
			->willReturn($path);

		$method = $this->getPrivateMethod('handleRequestInjection');
		$method->invoke($this->application, $request);

		$scripts = \OCP\Util::getScripts();

		if ($shouldInjectScript) {
			$this->assertContains('files/js/search', $scripts, "Script should be injected for path: $path");
		} else {
			$this->assertNotContains('files/js/search', $scripts, "Script should NOT be injected for path: $path");
		}
	}

	public function scriptInjectionProvider(): array {
		return [
			'simplesettings path - should inject' => ['/apps/simplesettings/settings', true],
			'simplesettings root - should inject' => ['/apps/simplesettings/', true],
			'simplesettings subpage - should inject' => ['/apps/simplesettings/admin/config', true],
			'files app - should not inject' => ['/apps/files/', false],
			'other app - should not inject' => ['/apps/other/', false],
			'settings page - should not inject' => ['/settings/', false],
			'root - should not inject' => ['/', false],
		];
	}

	public function testHandleRequestInjectionWithException(): void {
		$request = $this->createMock(IRequest::class);
		$request->expects($this->once())
			->method('getPathInfo')
			->willThrowException(new \Exception('Test exception'));

		$method = $this->getPrivateMethod('handleRequestInjection');

		// Exceptions should be caught internally; also ensure no script was added
		$method->invoke($this->application, $request);
		$scripts = \OCP\Util::getScripts();
		$this->assertNotContains('files/js/search', $scripts);
	}
}
