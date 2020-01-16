<?php
require_once ('autoload.php');
require_once (__DIR__ . '/TestService.php');

class ServiceUnitTest extends \Mezon\Service\Tests\ServiceUnitTests
{

    /**
     * Method tests does custom routes were loaded.
     * Trying to read routes both from php and json file and call routes from them.
     */
    public function testCustomRoutesLoading()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $Service = new TestService(
            \Mezon\Service\ServiceConsoleTransport\ServiceConsoleTransport::class,
            $this->getSecurityProvider(AS_STRING),
            TestLogic::class);

        // route from routes.php
        $_GET['r'] = 'test';
        $Service->run();

        // route from routes.json
        $this->expectException(Exception::class);
        $_GET['r'] = 'test3';
        $Service->run();
    }
}