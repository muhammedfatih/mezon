<?php
require_once (__DIR__ . '/../application.php');
require_once (__DIR__ . '/../../conf/conf.php');
require_once (__DIR__ . '/../../router/router.php');

/**
 * Application for testing purposes.
 */
class TestApplication extends \Mezon\Application
{

	function __construct()
	{
		if (is_object($this->Router)) {
			$this->Router->clear();
		}

		parent::__construct();
	}

	function actionExisting()
	{
		/* existing action */
		return ('OK!');
	}

	function drop_router()
	{
		$this->Router = false;
	}
}

class ApplicationTest extends PHPUnit\Framework\TestCase
{

	/**
	 * Running with correct router.
	 */
	public function testCorrectRoute()
	{
		$Application = new TestApplication();

		$_GET['r'] = '/existing/';

		$this->expectOutputString('OK!');

		$Application->run();
	}

	/**
	 * Running with incorrect router.
	 */
	public function testIncorrectRoute()
	{
		$Application = new TestApplication();

		$_GET['r'] = '/unexisting/';

		ob_start();
		$Application->run();
		$Output = ob_get_contents();
		ob_end_clean();

		$this->assertTrue(strpos($Output, 'The processor was not found for the route') !== false, 'Invalid behavior with incorrect route');
	}

	/**
	 * Test config structure validators.
	 */
	public function testConfigValidatorsRoute()
	{
		$Application = new TestApplication();

		$Msg = '';

		try {
			$Application->loadRoutesFromConfig(__DIR__ . '/test-invalid-routes-1.php');
		} catch (Exception $e) {
			$Msg = $e->getMessage();
		}

		$this->assertEquals('Field "route" must be set', $Msg, 'Invalid behavior for config validation');
	}

	/**
	 * Test config structure validators.
	 */
	public function testConfigValidatorsCallback()
	{
		$Application = new TestApplication();

		$Msg = '';

		try {
			$Application->loadRoutesFromConfig(__DIR__ . '/test-invalid-routes-2.php');
		} catch (Exception $e) {
			$Msg = $e->getMessage();
		}

		$this->assertEquals('Field "callback" must be set', $Msg, 'Invalid behavior for callback');
	}

	/**
	 * Testing loading routes from config file.
	 */
	public function testRoutesPhpConfig()
	{
		$Application = new TestApplication();

		$Application->loadRoutesFromConfig(__DIR__ . '/test-routes.php');

		$_GET['r'] = '/get-route/';

		$this->expectOutputString('OK!');

		$Application->run();
	}

	/**
	 * Testing loading routes from config file.
	 */
	public function testRoutesJsonConfig()
	{
		$Application = new TestApplication();

		$Application->loadRoutesFromConfig(__DIR__ . '/test-routes.json');

		$_GET['r'] = '/get-route/';

		$this->expectOutputString('OK!');

		$Application->run();
	}

	/**
	 * Testing loading POST routes from config file.
	 */
	public function testPostRoutesConfig()
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';

		$Application = new TestApplication();

		$Application->loadRoutesFromConfig(__DIR__ . '/test-routes.php');

		$_GET['r'] = '/post-route/';

		$this->expectOutputString('OK!');

		$Application->run();
	}

	/**
	 * Trying to load unexisting config.
	 */
	public function testLoadingFromUnexistingRoute()
	{
		try {
			$Application = new TestApplication();

			$Application->loadRoutesFromConfig('unexisting');

			$this->assertEquals(true, false, 'Exception was not thrown');
		} catch (Exception $e) {
			$this->assertEquals(true, true, 'OK');
		}
	}

	/**
	 * Method returns mocko bject of the application.
	 */
	protected function getMock()
	{
		$Mock = $this->getMockBuilder('\Mezon\Application')
			->disableOriginalConstructor()
			->setMethods([
			'handle_exception'
		])
			->getMock();

		return ($Mock);
	}

	/**
	 * Trying to load unexisting config.
	 */
	public function testUnexistingRouter()
	{
		try {
			$Application = $this->getMock();

			$Application->run();

			$this->fail();
		} catch (Exception $e) {
			$this->addToAssertionCount(1);
		}
	}

	/**
	 * Testing call of the method added onthe fly.
	 */
	public function testOnTheFlyMethod()
	{
		$Application = new \Mezon\Application();

		$Application->fly = function () {
			return ('OK!');
		};

		$Application->loadRoute([
			'route' => '/fly-route/',
			'callback' => 'fly'
		]);

		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_GET['r'] = '/fly-route/';

		$this->expectOutputString('OK!');

		$Application->run();
	}

	/**
	 * Testing call of the method added onthe fly.
	 */
	public function testOnTheFlyUnexistingMethod()
	{
		$Application = new \Mezon\Application();

		$Application->unexisting = function () {
			return ('OK!');
		};

		$Application->fly();

		$this->addToAssertionCount(1);
	}
}

?>