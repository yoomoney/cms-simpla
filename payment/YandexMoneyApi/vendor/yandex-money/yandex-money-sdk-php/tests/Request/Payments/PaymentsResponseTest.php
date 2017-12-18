<?php

namespace Tests\YaMoney\Request\Payments;

use PHPUnit\Framework\TestCase;
use YaMoney\Helpers\Random;
use YaMoney\Model\ConfirmationType;
use YaMoney\Model\CurrencyCode;
use YaMoney\Model\PaymentErrorCode;
use YaMoney\Model\PaymentInterface;
use YaMoney\Model\PaymentMethodType;
use YaMoney\Model\ReceiptRegistrationStatus;
use YaMoney\Model\Status;
use YaMoney\Request\Payments\PaymentsResponse;

class PaymentsResponseTest extends TestCase
{
    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetItems($options)
    {
        $instance = new PaymentsResponse($options);
        self::assertEquals(count($options['items']), count($instance->getItems()));
        foreach ($instance->getItems() as $index => $item) {
            self::assertTrue($item instanceof PaymentInterface);
            self::assertArrayHasKey($index, $options['items']);
            self::assertEquals($options['items'][$index]['id'], $item->getId());
            self::assertEquals($options['items'][$index]['status'], $item->getStatus());
            self::assertEquals($options['items'][$index]['amount']['value'], $item->getAmount()->getValue());
            self::assertEquals($options['items'][$index]['amount']['currency'], $item->getAmount()->getCurrency());
            self::assertEquals($options['items'][$index]['created_at'], $item->getCreatedAt()->format(DATE_ATOM));
            self::assertEquals($options['items'][$index]['payment_method']['type'], $item->getPaymentMethod()->getType());
            self::assertEquals($options['items'][$index]['paid'], $item->getPaid());
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testGetNextPage($options)
    {
        $instance = new PaymentsResponse($options);
        if (empty($options['next_page'])) {
            self::assertNull($instance->getNextPage());
        } else {
            self::assertEquals($options['next_page'], $instance->getNextPage());
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param array $options
     */
    public function testHasNext($options)
    {
        $instance = new PaymentsResponse($options);
        if (empty($options['next_page'])) {
            self::assertFalse($instance->hasNextPage());
        } else {
            self::assertTrue($instance->hasNextPage());
        }
    }

    public function validDataProvider()
    {
        return array(
            array(
                array(
                    'items' => array(),
                ),
            ),
            array(
                array(
                    'items' => array(
                        array(
                            'id' => Random::str(36),
                            'status' => Status::SUCCEEDED,
                            'amount' => array(
                                'value' => Random::int(1, 100000),
                                'currency' => CurrencyCode::EUR,
                            ),
                            'created_at' => date(DATE_ATOM),
                            'payment_method' => array(
                                'type' => PaymentMethodType::QIWI,
                            ),
                            'paid' => false,
                        )
                    ),
                    'next_page' => uniqid(),
                ),
            ),
            array(
                array(
                    'items' => array(
                        array(
                            'id' => Random::str(36),
                            'status' => Status::SUCCEEDED,
                            'amount' => array(
                                'value' => Random::int(1, 100000),
                                'currency' => CurrencyCode::EUR,
                            ),
                            'created_at' => date(DATE_ATOM),
                            'payment_method' => array(
                                'type' => PaymentMethodType::QIWI,
                            ),
                            'paid' => true,
                            'confirmation' => array(
                                'type' => ConfirmationType::EXTERNAL,
                            ),
                        ),
                        array(
                            'id' => Random::str(36),
                            'status' => Status::SUCCEEDED,
                            'amount' => array(
                                'value' => Random::int(1, 100000),
                                'currency' => CurrencyCode::EUR,
                            ),
                            'created_at' => date(DATE_ATOM),
                            'payment_method' => array(
                                'type' => PaymentMethodType::QIWI,
                            ),
                            'paid' => false,
                            'error' => array(
                                'code' => Random::value(PaymentErrorCode::getValidValues()),
                            ),
                            'recipient' => array(
                                'account_id' => uniqid(),
                                'gateway_id' => uniqid(),
                            ),
                            'reference_id' => uniqid(),
                            'captured_at' => date(DATE_ATOM),
                            'charge' => array('value' => Random::int(1, 100000), 'currency' => CurrencyCode::RUB),
                            'income' => array('value' => Random::int(1, 100000), 'currency' => CurrencyCode::USD),
                            'refunded' => array('value' => Random::int(1, 100000), 'currency' => CurrencyCode::EUR),
                            'metadata' => array('test_key' => 'test_value'),
                            'confirmation' => array(
                                'type' => ConfirmationType::EXTERNAL,
                            ),
                            'receipt_registration' => ReceiptRegistrationStatus::PENDING,
                        ),
                    ),
                    'next_page' => uniqid(),
                ),
            ),
            array(
                array(
                    'items' => array(
                        array(
                            'id' => Random::str(36),
                            'status' => Status::SUCCEEDED,
                            'amount' => array(
                                'value' => Random::int(1, 100000),
                                'currency' => CurrencyCode::EUR,
                            ),
                            'created_at' => date(DATE_ATOM),
                            'payment_method' => array(
                                'type' => PaymentMethodType::QIWI,
                            ),
                            'paid' => true,
                            'confirmation' => array(
                                'type' => ConfirmationType::REDIRECT,
                                'confirmation_url' => Random::str(10),
                                'return_url' => Random::str(10),
                                'enforce' => false,
                            ),
                            'error' => array(
                                'code' => Random::value(PaymentErrorCode::getValidValues()),
                                'description' => Random::str(100),
                            ),
                        ),
                    ),
                    'next_page' => uniqid(),
                ),
            ),
        );
    }
}