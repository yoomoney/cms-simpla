<?php


use PHPUnit\Framework\TestCase;
use YaMoney\Client\StreamClient;
use YaMoney\Client\YandexMoneyApi;
use YaMoney\Request\PaymentOptionsRequest;
use YaMoney\Request\PaymentOptionsResponse;
use YaMoney\Request\PaymentOptionsResponseItem;
use YaMoney\Request\Payments\CreatePaymentResponse;
use YaMoney\Request\Payments\CreatePaymentRequest;
use YaMoney\Request\Payments\Payment\CancelResponse;
use YaMoney\Request\Payments\Payment\CreateCaptureRequest;
use YaMoney\Request\Payments\Payment\CreateCaptureResponse;
use YaMoney\Request\Payments\PaymentResponse;
use YaMoney\Request\Payments\PaymentsRequest;
use YaMoney\Request\Payments\PaymentsResponse;
use YaMoney\Request\Refunds\CreateRefundRequest;
use YaMoney\Request\Refunds\CreateRefundResponse;
use YaMoney\Request\Refunds\RefundResponse;
use YaMoney\Request\Refunds\RefundsRequest;
use YaMoney\Request\Refunds\RefundsResponse;

class YandexMoneyApiTest extends TestCase
{
    public function testPaymentOptions()
    {
        $paymentOptionsRequest = PaymentOptionsRequest::builder()->setAccountId('123')->build();
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('paymentOptionsFixtures.json'),
                array('http_code' => 200)
            ));

        $apiClient = new YandexMoneyApi();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->getPaymentOptions($paymentOptionsRequest);

        self::assertSame($curlClientStub, $apiClient->getApiClient());
        $this->assertTrue($response instanceof PaymentOptionsResponse);
        foreach ($response->getItems() as $item) {
            $this->assertTrue($item instanceof PaymentOptionsResponseItem);
        }

        $items = $response->getItems();
        $item = $items[0];

        $this->assertTrue($item->getExtraFee());
        $this->assertEquals("yandex_money", $item->getPaymentMethodType());
        $this->assertEquals(array("redirect"), $item->getConfirmationTypes());
        $this->assertEquals("10.00", $item->getCharge()->getValue());
        $this->assertEquals("RUB", $item->getCharge()->getCurrency());
        $this->assertEquals("10.00", $item->getFee()->getValue());
        $this->assertEquals("RUB", $item->getFee()->getCurrency());

        //Without PaymentOptionsRequest
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('paymentOptionsFixtures.json'),
                array('http_code' => 200)
            ));

        $apiClient = new YandexMoneyApi();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->getPaymentOptions();

        self::assertSame($curlClientStub, $apiClient->getApiClient());
        $this->assertTrue($response instanceof PaymentOptionsResponse);
        foreach ($response->getItems() as $item) {
            $this->assertTrue($item instanceof PaymentOptionsResponseItem);
        }

        $items = $response->getItems();
        $item = $items[0];

        $this->assertTrue($item->getExtraFee());
        $this->assertEquals("yandex_money", $item->getPaymentMethodType());
        $this->assertEquals(array("redirect"), $item->getConfirmationTypes());
        $this->assertEquals("10.00", $item->getCharge()->getValue());
        $this->assertEquals("RUB", $item->getCharge()->getCurrency());
        $this->assertEquals("10.00", $item->getFee()->getValue());
        $this->assertEquals("RUB", $item->getFee()->getCurrency());
    }

    /**
     * @dataProvider errorResponseDataProvider
     * @param $httpCode
     * @param $errorResponse
     * @param $requiredException
     */
    public function testInvalidPaymentOptions($httpCode, $errorResponse, $requiredException)
    {
        $paymentOptionsRequest = PaymentOptionsRequest::builder()->setAccountId('123')->build();
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $errorResponse,
                array('http_code' => $httpCode)
            ));

        $apiClient = new YandexMoneyApi();
        $apiClient->setApiClient($curlClientStub)->setAuth('shopId', 'shopPassword');
        try {
            $apiClient->getPaymentOptions($paymentOptionsRequest);
        } catch (\Exception $e) {
            self::assertInstanceOf($requiredException, $e);
            return;
        }
        self::fail('Exception not thrown');
    }

    public function testCreatePayment()
    {
        $payment = CreatePaymentRequest::builder()
            ->setAmount(123)
            ->setPaymentToken(\YaMoney\Helpers\Random::str(36))
            ->build();

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('createPaymentFixtures.json'),
                array('http_code' => 200)
            ));

        $apiClient = new YandexMoneyApi();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->createPayment($payment, 123);

        self::assertSame($curlClientStub, $apiClient->getApiClient());

        $this->assertTrue($response instanceof CreatePaymentResponse);

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                '{"type":"error","code":"request_accepted","retry_after":123}',
                array('http_code' => 202)
            ));

        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->createPayment($payment, 123);
        self::assertNull($response);
    }

    /**
     * @dataProvider errorResponseDataProvider
     * @param $httpCode
     * @param $errorResponse
     * @param $requiredException
     */
    public function testInvalidCreatePayment($httpCode, $errorResponse, $requiredException)
    {
        $payment = CreatePaymentRequest::builder()
            ->setAmount(123)
            ->setPaymentToken(\YaMoney\Helpers\Random::str(36))
            ->build();
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $errorResponse,
                array('http_code' => $httpCode)
            ));

        $apiClient = new YandexMoneyApi();
        $apiClient->setApiClient($curlClientStub)->setAuth('shopId', 'shopPassword');
        try {
            $apiClient->createPayment($payment);
        } catch (\Exception $e) {
            self::assertInstanceOf($requiredException, $e);
            return;
        }
        self::fail('Exception not thrown');
    }

    public function testPaymentsList()
    {
        $payments = PaymentsRequest::builder()->setAccountId(12)->build();

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('getPaymentsFixtures.json'),
                array('http_code' => 200)
            ));

        $apiClient = new YandexMoneyApi();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->getPayments($payments);

        $this->assertTrue($response instanceof PaymentsResponse);

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('getPaymentsFixtures.json'),
                array('http_code' => 200)
            ));

        $apiClient = new YandexMoneyApi();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->getPayments();

        $this->assertTrue($response instanceof PaymentsResponse);
    }

    /**
     * @dataProvider errorResponseDataProvider
     * @param $httpCode
     * @param $errorResponse
     * @param $requiredException
     */
    public function testInvalidPaymentsList($httpCode, $errorResponse, $requiredException)
    {
        $payments = PaymentsRequest::builder()->setAccountId(12)->build();
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $errorResponse,
                array('http_code' => $httpCode)
            ));

        $apiClient = new YandexMoneyApi();
        $apiClient->setApiClient($curlClientStub)->setAuth('shopId', 'shopPassword');
        try {
            $apiClient->getPayments($payments);
        } catch (\Exception $e) {
            self::assertInstanceOf($requiredException, $e);
            return;
        }
        self::fail('Exception not thrown');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetPaymentInfo()
    {
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('paymentInfoFixtures.json'),
                array('http_code' => 200)
            ));

        $apiClient = new YandexMoneyApi();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->getPaymentInfo(123);

        $this->assertTrue($response instanceof PaymentResponse);

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->never())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('paymentInfoFixtures.json'),
                array('http_code' => 200)
            ));

        $apiClient = new YandexMoneyApi();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->getPaymentInfo(null);
    }

    /**
     * @dataProvider errorResponseDataProvider
     * @param $httpCode
     * @param $errorResponse
     * @param $requiredException
     */
    public function testInvalidGetPaymentInfo($httpCode, $errorResponse, $requiredException)
    {
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $errorResponse,
                array('http_code' => $httpCode)
            ));

        $apiClient = new YandexMoneyApi();
        $apiClient->setApiClient($curlClientStub)->setAuth('shopId', 'shopPassword');
        try {
            $apiClient->getPaymentInfo(123);
        } catch (\Exception $e) {
            self::assertInstanceOf($requiredException, $e);
            return;
        }
        self::fail('Exception not thrown');
    }

    public function testCapturePayment()
    {
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('capturePaymentFixtures.json'),
                array('http_code' => 200)
            ));

        $capturePaymentRequest = CreateCaptureRequest::builder()->setAmount(10)->build();

        $apiClient = new YandexMoneyApi();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->capturePayment($capturePaymentRequest, '1ddd77af-0bd7-500d-895b-c475c55fdefc', 123);

        $this->assertTrue($response instanceof CreateCaptureResponse);

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                '{"type":"error","code":"request_accepted","retry_after":123}',
                array('http_code' => 202)
            ));

        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->capturePayment($capturePaymentRequest, '1ddd77af-0bd7-500d-895b-c475c55fdefc', 123);
        self::assertNull($response);

        try {
            $apiClient->capturePayment($capturePaymentRequest, null);
        } catch (\InvalidArgumentException $e) {
            // it's ok
            return;
        }
        self::fail('Exception not thrown');
    }

    /**
     * @dataProvider errorResponseDataProvider
     * @param $httpCode
     * @param $errorResponse
     * @param $requiredException
     */
    public function testInvalidCapturePayment($httpCode, $errorResponse, $requiredException)
    {
        $capturePaymentRequest = CreateCaptureRequest::builder()->setAmount(10)->build();
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $errorResponse,
                array('http_code' => $httpCode)
            ));

        $apiClient = new YandexMoneyApi();
        $apiClient->setApiClient($curlClientStub)->setAuth('shopId', 'shopPassword');
        try {
            $apiClient->capturePayment($capturePaymentRequest, '1ddd77af-0bd7-500d-895b-c475c55fdefc', 123);
        } catch (\Exception $e) {
            self::assertInstanceOf($requiredException, $e);
            return;
        }
        self::fail('Exception not thrown');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCancelPayment()
    {
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('cancelPaymentFixtures.json'),
                array('http_code' => 200)
            ));

        $apiClient = new YandexMoneyApi();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->cancelPayment(123, 123);

        $this->assertTrue($response instanceof CancelResponse);

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->never())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('cancelPaymentFixtures.json'),
                array('http_code' => 200)
            ));

        $apiClient = new YandexMoneyApi();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->cancelPayment(null, 123);

        self::assertTrue($response instanceof \YaMoney\Model\PaymentInterface);

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                '{"type":"error","code":"request_accepted","retry_after":123}',
                array('http_code' => 202)
            ));

        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->cancelPayment(123, 123);
        self::assertNull($response);
    }

    /**
     * @dataProvider errorResponseDataProvider
     * @param $httpCode
     * @param $errorResponse
     * @param $requiredException
     */
    public function testInvalidCancelPayment($httpCode, $errorResponse, $requiredException)
    {
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $errorResponse,
                array('http_code' => $httpCode)
            ));

        $apiClient = new YandexMoneyApi();
        $apiClient->setApiClient($curlClientStub)->setAuth('shopId', 'shopPassword');
        try {
            $apiClient->cancelPayment(123, 123);
        } catch (\Exception $e) {
            self::assertInstanceOf($requiredException, $e);
            return;
        }
        self::fail('Exception not thrown');
    }

    public function testGetRefunds()
    {
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->exactly(2))
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('refundsInfoFixtures.json'),
                array('http_code' => 200)
            ));

        $refundsRequest = RefundsRequest::builder()->setAccountId(123)->build();

        $apiClient = new YandexMoneyApi();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->getRefunds($refundsRequest);

        $this->assertTrue($response instanceof RefundsResponse);

        $response = $apiClient->getRefunds();
        $this->assertTrue($response instanceof RefundsResponse);
    }

    /**
     * @dataProvider errorResponseDataProvider
     * @param $httpCode
     * @param $errorResponse
     * @param $requiredException
     */
    public function testInvalidGetRefunds($httpCode, $errorResponse, $requiredException)
    {
        $refundsRequest = RefundsRequest::builder()->setAccountId(123)->build();
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $errorResponse,
                array('http_code' => $httpCode)
            ));

        $apiClient = new YandexMoneyApi();
        $apiClient->setApiClient($curlClientStub)->setAuth('shopId', 'shopPassword');
        try {
            $apiClient->getRefunds($refundsRequest);
        } catch (\Exception $e) {
            self::assertInstanceOf($requiredException, $e);
            return;
        }
        self::fail('Exception not thrown');
    }

    public function testCreateRefund()
    {
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('createRefundFixtures.json'),
                array('http_code' => 200)
            ));

        $refundRequest = CreateRefundRequest::builder()->setPaymentId('1ddd77af-0bd7-500d-895b-c475c55fdefc')->setAmount(123)->build();

        $apiClient = new YandexMoneyApi();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->createRefund($refundRequest, 123);


        $this->assertTrue($response instanceof CreateRefundResponse);

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                '{"type":"error","code":"request_accepted","retry_after":123}',
                array('http_code' => 202)
            ));

        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->createRefund($refundRequest, 123);
        self::assertNull($response);
    }

    /**
     * @dataProvider errorResponseDataProvider
     * @param $httpCode
     * @param $errorResponse
     * @param $requiredException
     */
    public function testInvalidCreateRefund($httpCode, $errorResponse, $requiredException)
    {
        $refundRequest = CreateRefundRequest::builder()->setPaymentId('1ddd77af-0bd7-500d-895b-c475c55fdefc')->setAmount(123)->build();
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $errorResponse,
                array('http_code' => $httpCode)
            ));

        $apiClient = new YandexMoneyApi();
        $apiClient->setApiClient($curlClientStub)->setAuth('shopId', 'shopPassword');
        try {
            $apiClient->createRefund($refundRequest, 123);
        } catch (\Exception $e) {
            self::assertInstanceOf($requiredException, $e);
            return;
        }
        self::fail('Exception not thrown');
    }

    public function testRefundInfo()
    {
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('refundInfoFixtures.json'),
                array('http_code' => 200)
            ));

        $apiClient = new YandexMoneyApi();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->getRefundInfo(123);

        $this->assertTrue($response instanceof RefundResponse);

        try {
            $apiClient->getRefundInfo(null);
        } catch (InvalidArgumentException $e) {
            // it's ok
            return;
        }
        self::fail('Exception not thrown');
    }

    /**
     * @dataProvider errorResponseDataProvider
     * @param $httpCode
     * @param $errorResponse
     * @param $requiredException
     */
    public function testInvalidRefundInfo($httpCode, $errorResponse, $requiredException)
    {
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $errorResponse,
                array('http_code' => $httpCode)
            ));

        $apiClient = new YandexMoneyApi();
        $apiClient->setApiClient($curlClientStub)->setAuth('shopId', 'shopPassword');
        try {
            $apiClient->getRefundInfo(123);
        } catch (\Exception $e) {
            self::assertInstanceOf($requiredException, $e);
            return;
        }
        self::fail('Exception not thrown');
    }

    public function testApiException()
    {
        $payment = CreatePaymentRequest::builder()
            ->setAmount(123)
            ->setPaymentToken(\YaMoney\Helpers\Random::str(36))
            ->build();

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                'unknown response here',
                array('http_code' => 444)
            ));
        $this->setExpectedException('YaMoney\Common\Exceptions\ApiException');

        $apiClient = new YandexMoneyApi();
        $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->createPayment($payment, 123);
    }

    public function testBadRequestException()
    {
        $payment = CreatePaymentRequest::builder()
            ->setAmount(123)
            ->setPaymentToken(\YaMoney\Helpers\Random::str(36))
            ->build();

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                '{"description": "error_msg", "code": "error_code", "parameter_name": "parameter_name"}',
                array('http_code' => 400)
            ));
        $this->setExpectedException('YaMoney\Common\Exceptions\BadApiRequestException');

        $apiClient = new YandexMoneyApi();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->createPayment($payment, 123);
    }

    public function testTechnicalErrorException()
    {
        $payment = CreatePaymentRequest::builder()
            ->setAmount(123)
            ->setPaymentToken(\YaMoney\Helpers\Random::str(36))
            ->build();

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                '{"description": "error_msg", "code": "error_code"}',
                array('http_code' => 500)
            ));
        $this->setExpectedException('YaMoney\Common\Exceptions\InternalServerError');

        $apiClient = new YandexMoneyApi();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->createPayment($payment, 123);
    }

    public function testUnauthorizedException()
    {
        $payment = CreatePaymentRequest::builder()
            ->setAmount(123)
            ->setPaymentToken(\YaMoney\Helpers\Random::str(36))
            ->build();

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                '{"description": "error_msg"}',
                array('http_code' => 401)
            ));
        $this->setExpectedException('YaMoney\Common\Exceptions\UnauthorizedException');

        $apiClient = new YandexMoneyApi();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->createPayment($payment, 123);
    }

    public function testForbiddenException()
    {
        $payment = CreatePaymentRequest::builder()
            ->setAmount(123)
            ->setPaymentToken(\YaMoney\Helpers\Random::str(36))
            ->build();

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                '{"description": "error_msg","error_code": "error_code", "parameter_name": "parameter_name", "operation_name": "operation_name"}',
                array('http_code' => 403)
            ));
        $this->setExpectedException('YaMoney\Common\Exceptions\ForbiddenException');

        $apiClient = new YandexMoneyApi();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->createPayment($payment, 123);
    }
    
    public function testConfig()
    {
        $apiClient = new YandexMoneyApi();
        $apiClient->setConfig(array(
            'url' => 'test'
        ));

        $this->assertEquals(array('url' => 'test'), $apiClient->getConfig());
    }

    public function testApiClient()
    {
        $client = new StreamClient();
        $apiClient = new YandexMoneyApi();
        $apiClient->setApiClient($client);

        $this->assertEquals($client, $client);
    }

    public function testSetLogger()
    {
        $wrapped = new ArrayLogger();
        $logger = new \YaMoney\Common\LoggerWrapper($wrapped);

        $apiClient = new YandexMoneyApi();
        $apiClient->setLogger($logger);

        $clientMock = $this->getMockBuilder('YaMoney\Client\ApiClientInterface')
            ->setMethods(array('setLogger', 'setConfig', 'call'))
            ->disableOriginalConstructor()
            ->getMock();
        $expectedLoggers = array();
        $clientMock->expects(self::exactly(3))->method('setLogger')->willReturnCallback(function ($logger) use(&$expectedLoggers) {
            $expectedLoggers[] = $logger;
        });
        $clientMock->expects(self::once())->method('setConfig')->willReturn($clientMock);

        $apiClient->setApiClient($clientMock);
        self::assertSame($expectedLoggers[0], $logger);

        $apiClient->setLogger($wrapped);
        $apiClient->setLogger(function ($level, $log, $context = array()) use ($wrapped) {
            $wrapped->log($level, $log, $context);
        });
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getCurlClientStub()
    {
        $clientStub = $this->getMockBuilder('YaMoney\Client\CurlClient')
            ->setMethods(array('sendRequest'))
            ->getMock();

        return $clientStub;
    }

    public function errorResponseDataProvider()
    {
        return array(
            array(\YaMoney\Common\Exceptions\BadApiRequestException::HTTP_CODE, '{}', 'YaMoney\Common\Exceptions\BadApiRequestException'),
            array(\YaMoney\Common\Exceptions\ForbiddenException::HTTP_CODE, '{}', 'YaMoney\Common\Exceptions\ForbiddenException'),
            array(\YaMoney\Common\Exceptions\UnauthorizedException::HTTP_CODE, '{}', 'YaMoney\Common\Exceptions\UnauthorizedException'),
            array(\YaMoney\Common\Exceptions\InternalServerError::HTTP_CODE, '{}', 'YaMoney\Common\Exceptions\InternalServerError'),
        );
    }

    /**
     * @return bool|string
     */
    private function getFixtures($fileName)
    {
        return file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . $fileName);
    }
}

class ArrayLogger
{
    private $lastLog;

    public function log($level, $message, $context)
    {
        $this->lastLog = array($level, $message, $context);
    }

    public function getLastLog()
    {
        return $this->lastLog;
    }
}