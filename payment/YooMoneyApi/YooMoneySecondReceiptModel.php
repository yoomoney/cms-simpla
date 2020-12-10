<?php

namespace YooMoneyModule;

use YooKassa\Client;
use YooKassa\Model\PaymentInterface;
use YooKassa\Model\PaymentStatus;
use YooKassa\Model\Receipt\PaymentMode;
use YooKassa\Model\ReceiptCustomer;
use YooKassa\Model\ReceiptItem;
use YooKassa\Model\ReceiptType;
use YooKassa\Model\Settlement;
use YooKassa\Request\Receipts\CreatePostReceiptRequest;
use YooKassa\Request\Receipts\ReceiptResponseInterface;
use YooKassa\Request\Receipts\ReceiptResponseItemInterface;

class YooMoneySecondReceiptModel
{
    /**
     * @var array
     */
    private $orderInfo = array(
        'orderId' => 0,
        'user_email' => '',
        'user_phone' => '',
    );

    /**
     * @var PaymentInterface
     */
    private $paymentInfo;

    /**
     * @var string
     */
    private $settlementsSum;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var bool
     */
    private $isDebug;

    /**
     * YooMoneySecondReceiptModel constructor.
     * @param $paymentInfo
     * @param $orderInfo
     * @param $client Client
     * @param $isDebugLog
     */
    public function __construct($paymentInfo, $orderInfo, $client, $isDebug)
    {
        $this->orderInfo   = $orderInfo;
        $this->paymentInfo = $paymentInfo;
        $this->client      = $client;
        $this->isDebug     = $isDebug;
    }

    /**
     * @return bool
     */
    public function sendSecondReceipt()
    {
        $this->log("info", "Hook send second receipt");

        if (!$this->isPaymentInfoValid($this->paymentInfo)) {
            $this->log("error", "Invalid paymentInfo");
            return false;
        } elseif (empty($this->orderInfo['user_email']) && empty($this->orderInfo['user_phone'])) {
            $this->log("error", "Invalid orderInfo orderId = " . $this->orderInfo['order_id']);
            return false;
        }

        $receiptRequest = $this->buildSecondReceipt($this->getLastReceipt($this->paymentInfo->getId()), $this->paymentInfo, $this->orderInfo);

        if (empty($receiptRequest)) {
            return false;
        }

        $this->log("info", "Second receipt request data: " . json_encode($receiptRequest->jsonSerialize()));

        try {
            $response = $this->client->createReceipt($receiptRequest);
        } catch (\Exception $e) {
            $this->log("error", "Request second receipt error: " . $e->getMessage());
            return false;
        }

        $this->log("info", "Request second receipt result: " . json_encode($response->jsonSerialize()));
        $this->generateSettlementsAmountSum($response);

        return true;
    }

    /**
     * @return string
     */
    public function getSettlementsSum()
    {
        return $this->settlementsSum;
    }

    /**
     * @param ReceiptResponseInterface $response
     */
    private function generateSettlementsAmountSum($response)
    {
        $amount = 0;

        foreach ($response->getSettlements() as $settlement) {
            $amount += $settlement->getAmount()->getIntegerValue();
        }

        $this->settlementsSum = $amount / 100.0;
    }

    /**
     * @param ReceiptResponseInterface $lastReceipt
     * @param PaymentInterface $paymentInfo
     * @param $orderInfo
     *
     * @return void|CreatePostReceiptRequest
     */
    private function buildSecondReceipt($lastReceipt, $paymentInfo, $orderInfo)
    {
        if (!$lastReceipt instanceof ReceiptResponseInterface) {
            $this->log("info", "Second receipt isn't need, not found first receipt");
            return;
        }

        if ($lastReceipt->getType() === "refund") {
            $this->log("info", "Receipt type = refund, second receipt isn't need");
            return;
        }

        $resendItems = $this->getResendItems($lastReceipt->getItems());

        if (count($resendItems['items']) < 1) {
            $this->log("info", "Second receipt isn't need");
            return;
        }

        try {
            $receiptBuilder = CreatePostReceiptRequest::builder();
            $customer = $this->getReceiptCustomer($this->orderInfo);

            if (empty($customer)) {
                $this->log("error", "Need customer phone or email for second receipt");
                return;
            }

            $receiptBuilder->setObjectId($paymentInfo->getId())
                ->setType(ReceiptType::PAYMENT)
                ->setItems($resendItems['items'])
                ->setSettlements(
                    array(
                        new Settlement(
                            array(
                                'type' => 'prepayment',
                                'amount' => array(
                                    'value' => $resendItems['amount'],
                                    'currency' => 'RUB',
                                ),
                            )
                        ),
                    )
                )
                ->setCustomer($customer)
                ->setSend(true);

            return $receiptBuilder->build();
        } catch (\Exception $e) {
            $this->log("error", $e->getMessage() . ". Property name:". $e->getProperty());
        }

    }

    /**
     * @param PaymentInterface $paymentInfo
     * @return bool
     */
    private function isPaymentInfoValid($paymentInfo)
    {
        if (empty($paymentInfo)) {
            $this->log("error", "Fail send second receipt paymentInfo is null: " . print_r($paymentInfo, true));
            return false;
        }

        if ($paymentInfo->getStatus() !== PaymentStatus::SUCCEEDED) {
            $this->log("error", "Fail send second receipt payment have incorrect status: " . $paymentInfo->getStatus());
            return false;
        }

        return true;
    }

    /**
     * @param $orderInfo
     * @return ReceiptCustomer
     */
    private function getReceiptCustomer($orderInfo)
    {
        $customerData = array();

        if (!empty($orderInfo['user_email'])) {
            $customerData['email'] = $orderInfo['user_email'];
        }

        if (!empty($orderInfo['user_phone'])) {
            $customerData['phone'] = $orderInfo['user_phone'];
        }

        return new ReceiptCustomer($customerData);
    }

    /**
     * @param $paymentId
     * @return mixed|ReceiptResponseInterface
     */
    private function getLastReceipt($paymentId)
    {
        try {
            $receipts = $this->client->getReceipts(array(
                'payment_id' => $paymentId,
            ))->getItems();
        } catch (\Exception $e) {
            $this->log("error", "Fail get receipt message: " . $e->getMessage());
        }

        return array_shift($receipts);
    }

    /**
     * @param ReceiptResponseItemInterface[] $items
     *
     * @return array
     */
    private function getResendItems($items)
    {
        $resendItems = array(
            'items'  => array(),
            'amount' => 0,
        );

        foreach ($items as $item) {
            if ($item->getPaymentMode() === PaymentMode::FULL_PREPAYMENT) {
                $item->setPaymentMode(PaymentMode::FULL_PAYMENT);
                $resendItems['items'][] = new ReceiptItem($item->jsonSerialize());
                $resendItems['amount'] += $item->getAmount() / 100.0;
            }
        }

        return $resendItems;
    }

    /**
     * @param $level
     * @param $message
     */
    public function log($level, $message)
    {
        $logger = new YooMoneyLogger($this->isDebug);
        $logger->log($level, $message);
    }
}