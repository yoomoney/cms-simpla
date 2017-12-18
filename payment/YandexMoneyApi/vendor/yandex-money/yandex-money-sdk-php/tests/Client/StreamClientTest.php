<?php


use PHPUnit\Framework\TestCase;
use YaMoney\Client\StreamClient;

class StreamClientTest extends TestCase
{
    public function testConfig()
    {
        $client = new StreamClient();
        $client->setConfig(array('url' => 'test'));
        $this->assertEquals(array('url' => 'test'), $client->getConfig());
    }

    public function testFailCall()
    {
        $clientStub = $this->getMockBuilder('YaMoney\Client\StreamClient')
            ->setMethods(array('sendRequest'))
            ->getMock();

        $clientStub->expects($this->any())->method('sendRequest')
            ->willReturn(false);

        $clientStub->setTimeout(1);
        $clientStub->setShopPassword(1);
        $clientStub->setShopId(2);
        $this->setExpectedException('\YaMoney\Common\Exceptions\ApiConnectionException');
        $clientStub->call('$path', '$method', '$queryParams');


    }

    public function testSuccessCall()
    {
        $clientStub = $this->getMockBuilder('YaMoney\Client\StreamClient')
            ->setMethods(array('sendRequest'))
            ->getMock();

        $clientStub->expects($this->any())->method('sendRequest')
            ->willReturn(true);

        $clientStub->responseBody = '';
        $clientStub->responseHeaders = array('Header:Header');
        $clientStub->setShopPassword(1);
        $clientStub->setShopId(2);

        $result = $clientStub->call('$path', '$method', '$queryParams');
        $this->assertTrue($result instanceof YaMoney\Common\ResponseObject);
    }

    /**
     * @dataProvider headersDataProvider
     */
    public function testPrepareHeaders($headers, $expected)
    {
        $client = new StreamClient();
        $client->setShopId(1)->setShopPassword(2);

        $this->assertEquals($expected, $client->prepareHeaders($headers));
    }

    public function headersDataProvider()
    {
        return array(
            array(
                array(
                    "Header-Type-1" => "HeaderValue1",
                    "Header-Type-2" => "HeaderValue2"
                ),
                "Authorization:Basic MTI=\r\nContent-Type:application/json\r\nAccept:application/json\r\nHeader-Type-1:HeaderValue1\r\nHeader-Type-2:HeaderValue2"
            )
        );
    }
}