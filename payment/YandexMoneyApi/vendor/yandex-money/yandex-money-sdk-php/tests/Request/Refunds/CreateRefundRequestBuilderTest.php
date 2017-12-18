<?php

namespace Tests\YaMoney\Request\Refunds;

use PHPUnit\Framework\TestCase;
use YaMoney\Helpers\Random;
use YaMoney\Model\AmountInterface;
use YaMoney\Model\CurrencyCode;
use YaMoney\Model\MonetaryAmount;
use YaMoney\Model\ReceiptItem;
use YaMoney\Request\Refunds\CreateRefundRequestBuilder;

class CreateRefundRequestBuilderTest extends TestCase
{
    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetPaymentId($options)
    {
        $builder = new CreateRefundRequestBuilder();
        try {
            $builder->build(array('amountValue' => mt_rand(1, 100)));
        } catch (\RuntimeException $e) {
            $builder->setPaymentId($options['paymentId']);
            $instance = $builder->build(array('amount' => mt_rand(1, 100)));
            self::assertEquals($options['paymentId'], $instance->getPaymentId());
            return;
        }
        self::fail('Exception not thrown');
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetAmountValue($options)
    {
        $builder = new CreateRefundRequestBuilder();
        try {
            $builder->build(array('paymentId' => Random::str(36)));
        } catch (\RuntimeException $e) {
            $builder->setAmount($options['amount']);
            $instance = $builder->build(array('paymentId' => Random::str(36)));
            if ($options['amount'] instanceof AmountInterface) {
                self::assertEquals($options['amount']->getValue(), $instance->getAmount()->getValue());
            } else {
                self::assertEquals($options['amount'], $instance->getAmount()->getValue());
            }
            return;
        }
        self::fail('Exception not thrown');
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetAmountCurrency($options)
    {
        $builder = new CreateRefundRequestBuilder();

        $builder->setCurrency($options['currency']);
        $instance = $builder->build(array(
            'paymentId' => Random::str(36),
            'amount'    => mt_rand(1, 100),
        ));
        self::assertEquals($options['currency'], $instance->getAmount()->getCurrency());
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetComment($options)
    {
        $builder = new CreateRefundRequestBuilder();
        $instance = $builder->build(array(
            'paymentId' => Random::str(36),
            'amount'    => mt_rand(1, 100),
        ));
        self::assertNull($instance->getComment());

        $builder->setComment($options['comment']);
        $instance = $builder->build(array(
            'paymentId' => Random::str(36),
            'amount'    => mt_rand(1, 100),
        ));
        if (empty($options['comment'])) {
            self::assertNull($instance->getComment());
        } else {
            self::assertEquals($options['comment'], $instance->getComment());
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testBuild($options)
    {
        $builder = new CreateRefundRequestBuilder();
        $instance = $builder->build($options);

        self::assertEquals($options['paymentId'], $instance->getPaymentId());
        if ($options['amount'] instanceof AmountInterface) {
            self::assertEquals($options['amount']->getValue(), $instance->getAmount()->getValue());
        } else {
            self::assertEquals($options['amount'], $instance->getAmount()->getValue());
        }
        self::assertEquals($options['currency'], $instance->getAmount()->getCurrency());
        if (empty($options['comment'])) {
            self::assertNull($instance->getComment());
        } else {
            self::assertEquals($options['comment'], $instance->getComment());
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetReceiptItems($options)
    {
        $builder = new CreateRefundRequestBuilder();

        $builder->setReceiptItems($options['receiptItems']);
        $builder->setReceiptEmail($options['receiptEmail']);
        $instance = $builder->build($this->getRequiredData());

        if (empty($options['receiptItems'])) {
            self::assertNull($instance->getReceipt());
        } else {
            self::assertNotNull($instance->getReceipt());
            self::assertEquals(count($options['receiptItems']), count($instance->getReceipt()->getItems()));
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testAddReceiptItems($options)
    {
        $builder = new CreateRefundRequestBuilder();

        foreach ($options['receiptItems'] as $item) {
            if ($item instanceof ReceiptItem) {
                $builder->addReceiptItem(
                    $item->getDescription(), $item->getPrice()->getValue(), $item->getQuantity(), $item->getVatCode()
                );
            } else {
                $builder->addReceiptItem($item['title'], $item['price'], $item['quantity'], $item['vatCode']);
            }
        }
        $builder->setReceiptEmail($options['receiptEmail']);
        $instance = $builder->build($this->getRequiredData());

        if (empty($options['receiptItems'])) {
            self::assertNull($instance->getReceipt());
        } else {
            self::assertNotNull($instance->getReceipt());
            self::assertEquals(count($options['receiptItems']), count($instance->getReceipt()->getItems()));
            foreach ($instance->getReceipt()->getItems() as $item) {
                self::assertFalse($item->isShipping());
            }
        }
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testAddReceiptShipping($options)
    {
        $builder = new CreateRefundRequestBuilder();

        foreach ($options['receiptItems'] as $item) {
            if ($item instanceof ReceiptItem) {
                $builder->addReceiptShipping(
                    $item->getDescription(), $item->getPrice()->getValue(), $item->getVatCode()
                );
            } else {
                $builder->addReceiptShipping($item['title'], $item['price'], $item['vatCode']);
            }
        }
        $builder->setReceiptEmail($options['receiptEmail']);
        $instance = $builder->build($this->getRequiredData());

        if (empty($options['receiptItems'])) {
            self::assertNull($instance->getReceipt());
        } else {
            self::assertNotNull($instance->getReceipt());
            self::assertEquals(count($options['receiptItems']), count($instance->getReceipt()->getItems()));
            foreach ($instance->getReceipt()->getItems() as $item) {
                self::assertTrue($item->isShipping());
            }
        }
    }

    /**
     * @dataProvider invalidItemsDataProvider
     * @expectedException \InvalidArgumentException
     * @param $items
     */
    public function testSetInvalidReceiptItems($items)
    {
        $builder = new CreateRefundRequestBuilder();
        $builder->setReceiptItems($items);
    }

    public function invalidItemsDataProvider()
    {
        return array(
            array(
                array(
                    array(
                        'price' => 1,
                        'quantity' => 1.4,
                        'vatCode' => 3,
                    ),
                )
            ),
            array(
                array(
                    array(
                        'title' => 'test',
                        'quantity' => 1.4,
                        'vatCode' => 3,
                    ),
                )
            ),
            array(
                array(
                    array(
                        'description' => 'test',
                        'quantity' => 1.4,
                        'vatCode' => 3,
                    ),
                )
            ),
            array(
                array(
                    array(
                        'title' => 'test',
                        'price' => 123,
                        'quantity' => 1.4,
                        'vatCode' => 7,
                    ),
                )
            ),
            array(
                array(
                    array(
                        'description' => 'test',
                        'price' => 123,
                        'quantity' => -1.4,
                    ),
                )
            ),
        );
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetReceiptEmail($options)
    {
        $builder = new CreateRefundRequestBuilder();

        $builder->setReceiptItems($options['receiptItems']);
        $builder->setReceiptEmail($options['receiptEmail']);
        $instance = $builder->build($this->getRequiredData());

        if (empty($options['receiptItems'])) {
            self::assertNull($instance->getReceipt());
        } else {
            self::assertNotNull($instance->getReceipt());
            self::assertEquals($options['receiptEmail'], $instance->getReceipt()->getEmail());
        }
    }

    /**
     * @dataProvider invalidEmailDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidEmail($value)
    {
        $builder = new CreateRefundRequestBuilder();
        $builder->setReceiptEmail($value);
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetReceiptPhone($options)
    {
        $builder = new CreateRefundRequestBuilder();

        $builder->setReceiptItems($options['receiptItems']);
        $builder->setReceiptEmail($options['receiptEmail']);
        $builder->setReceiptPhone($options['receiptPhone']);
        $instance = $builder->build($this->getRequiredData());

        if (empty($options['receiptItems'])) {
            self::assertNull($instance->getReceipt());
        } else {
            self::assertNotNull($instance->getReceipt());
            self::assertEquals($options['receiptPhone'], $instance->getReceipt()->getPhone());
        }
    }

    /**
     * @dataProvider invalidPhoneDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidPhone($value)
    {
        $builder = new CreateRefundRequestBuilder();
        $builder->setReceiptPhone($value);
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testSetReceiptTaxSystemCode($options)
    {
        $builder = new CreateRefundRequestBuilder();

        $builder->setReceiptItems($options['receiptItems']);
        $builder->setReceiptEmail($options['receiptEmail']);
        $builder->setTaxSystemCode($options['taxSystemCode']);
        $instance = $builder->build($this->getRequiredData());

        if (empty($options['receiptItems'])) {
            self::assertNull($instance->getReceipt());
        } else {
            self::assertNotNull($instance->getReceipt());
            self::assertEquals($options['taxSystemCode'], $instance->getReceipt()->getTaxSystemCode());
        }
    }

    /**
     * @dataProvider invalidVatIdDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidTaxSystemId($value)
    {
        $builder = new CreateRefundRequestBuilder();
        $builder->setTaxSystemCode($value);
    }


    public function validDataProvider()
    {
        $result = array(
            array(
                array(
                    'paymentId' => Random::str(36),
                    'amount' => mt_rand(1, 100000000),
                    'currency' => Random::value(CurrencyCode::getValidValues()),
                    'comment' => null,
                    'receiptItems' => array(),
                    'receiptEmail' => null,
                    'receiptPhone' => null,
                    'taxSystemCode' => Random::int(1, 6),
                ),
            ),
            array(
                array(
                    'paymentId' => Random::str(36),
                    'amount' => new MonetaryAmount(
                        Random::int(1, 99999),
                        Random::value(CurrencyCode::getValidValues())
                    ),
                    'currency' => Random::value(CurrencyCode::getValidValues()),
                    'comment' => '',
                    'receiptItems' => array(),
                    'receiptEmail' => '',
                    'receiptPhone' => '',
                    'taxSystemCode' => Random::int(1, 6),
                ),
            ),
        );
        $items = array(
            new ReceiptItem(),
            array(
                'title' => 'test',
                'price' => Random::int(1, 10000),
                'quantity' => Random::int(1, 10000),
                'vatCode' => Random::int(1, 6),
            ),
        );
        $items[0]->setDescription('test1');
        $items[0]->setQuantity(Random::int(1, 10000));
        $items[0]->setPrice(new MonetaryAmount(Random::int(1, 10000)));
        $items[0]->setVatCode(Random::int(1, 6));
        for ($i = 0; $i < 10; $i++) {
            $request = array(
                'paymentId' => Random::str(36),
                'amount' => mt_rand(1, 100000000),
                'currency' => Random::value(CurrencyCode::getValidValues()),
                'comment' => uniqid(),
                'receiptItems' => $items,
                'receiptEmail' => uniqid(),
                'receiptPhone' => Random::str(4, 15, '0123456789'),
                'taxSystemCode' => Random::int(1, 6),
            );
            $result[] = array($request);
        }
        return $result;
    }

    private function getRequiredData()
    {
        return array(
            'paymentId' => Random::str(36),
            'amount'    => mt_rand(1, 100),
        );
    }

    public function invalidEmailDataProvider()
    {
        return array(
            array(array()),
            array(true),
            array(false),
            array(new \stdClass()),
        );
    }

    public function invalidPhoneDataProvider()
    {
        return array(
            array(array()),
            array(true),
            array(false),
            array(new \stdClass()),
            array(Random::str(1, '0123456789')),
            array(Random::str(32)),
            array(Random::str(18, '0123456789')),
        );
    }

    public function invalidVatIdDataProvider()
    {
        return array(
            array(array()),
            array(true),
            array(false),
            array(new \stdClass()),
            array(0),
            array(7),
            array(Random::int(-100, -1)),
            array(Random::int(7, 100)),
        );
    }
}