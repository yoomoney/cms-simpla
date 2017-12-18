<?php


use PHPUnit\Framework\TestCase;

class CurlClientTest extends TestCase
{
    public function testConnectionTimeout()
    {
        $client = new \YaMoney\Client\CurlClient();
        $client->setConnectionTimeout(10);
        $this->assertEquals(10, $client->getConnectionTimeout());
    }

    public function testTimeout()
    {
        $client = new \YaMoney\Client\CurlClient();
        $client->setTimeout(10);
        $this->assertEquals(10, $client->getTimeout());
    }

    /**
     * @expectedException \YaMoney\Common\Exceptions\ApiConnectionException
     */
    public function testHandleCurlError()
    {

        $client = new \YaMoney\Client\CurlClient();
        $reflector = new ReflectionClass( '\YaMoney\Client\CurlClient' );
        $method = $reflector->getMethod( 'handleCurlError' );
        $method->setAccessible( true );

        $method->invokeArgs($client, array( '$error', CURLE_COULDNT_CONNECT ) );
        $method->invokeArgs($client, array( '$error', CURLE_SSL_CACERT ) );
        $method->invokeArgs($client, array( '$error', 0 ) );
    }
}