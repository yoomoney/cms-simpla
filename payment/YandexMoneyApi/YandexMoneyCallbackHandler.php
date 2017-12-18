<?php
require_once('vendor/autoload.php');
require_once 'YandexMoneyLogger.php';

use YaMoney\Client\YandexMoneyApi;
use YaMoney\Common\Exceptions\ApiException;
use YaMoney\Model\Notification\NotificationWaitingForCapture;
use YaMoney\Model\PaymentStatus;
use YaMoney\Request\Payments\Payment\CreateCaptureRequest;

class YandexMoneyCallbackHandler
{
    public $simplaApi;

    public function __construct($simplaApi)
    {
        $this->simplaApi = $simplaApi;
    }

    public function processReturnUrl($simpla)
    {
        $orderId       = $simpla->request->get('order');
        $order         = $simpla->orders->get_order(intval($orderId));
        $paymentMethod = $simpla->payment->get_payment_method(intval($order->payment_method_id));
        $settings      = $simpla->payment->get_payment_settings($paymentMethod->id);
        $apiClient     = $this->getApiClient($settings['yandex_api_shopid'], $settings['yandex_api_password']);
        $paymentId     = $this->getPaymentId($orderId);
        $logger        = new YandexMoneyLogger($settings['ya_kassa_debug']);
        $apiClient->setLogger($logger);
        try {
            $paymentInfo = $apiClient->getPaymentInfo($paymentId);
            if ($paymentInfo->status == PaymentStatus::WAITING_FOR_CAPTURE) {
                $captureResult = $this->capturePayment($apiClient, $paymentInfo, $order);
                if ($captureResult->status == PaymentStatus::SUCCEEDED) {
                    $this->completePayment($order);
                } else {
                    $simpla->orders->close($order->id);
                }
            } elseif ($paymentInfo->status == PaymentStatus::CANCELED) {
                $simpla->orders->close($order->id);
            } elseif ($paymentInfo->status == PaymentStatus::SUCCEEDED) {
                $this->completePayment($order);
            }

            $return_url = $simpla->config->root_url.'/order/'.$order->url;
            header('Location: '.$return_url);
            exit;
        } catch (ApiException $e) {
            throw $e;
        }
    }

    public function processNotification($simpla, $callbackParams)
    {
        $body           = @file_get_contents('php://input');
        $callbackParams = json_decode($body);
        if (!json_last_error()) {
            $notificationModel = new NotificationWaitingForCapture($callbackParams);

            $payment       = $notificationModel->getObject();
            $order         = $this->getOrderByPaymentId($payment->getId());
            $paymentMethod = $simpla->payment->get_payment_method(intval($order->payment_method_id));
            $settings      = $this->payment->get_payment_settings($paymentMethod->id);
            $apiClient     = $this->getApiClient($settings['yandex_api_shopid'], $settings['yandex_api_password']);
            if ($order) {

                $tries = 0;
                do {
                    $paymentInfo = $apiClient->getPaymentInfo($payment->getId());
                    if (paymentInfo === null) {
                        $tries++;
                        if ($tries > 3) {
                            break;
                        }
                        sleep(2);
                    }
                } while ($paymentInfo == null);

                if ($paymentInfo) {
                    switch ($paymentInfo->status) {
                        case PaymentStatus::WAITING_FOR_CAPTURE:
                            $captureResult = $this->capturePayment($apiClient, $paymentInfo, $order);
                            if ($captureResult->status == PaymentStatus::SUCCEEDED) {
                                $this->completePayment($order);
                            } else {
                                $simpla->orders->close($order);
                            }
                            header("HTTP/1.1 200 OK");
                            header("Status: 200 OK");
                            break;
                        case PaymentStatus::PENDING:
                            header("HTTP/1.1 400 Bad Request");
                            header("Status: 400 Bad Request");
                            break;
                        case PaymentStatus::SUCCEEDED:
                            $this->completePayment($order);
                            header("HTTP/1.1 200 OK");
                            header("Status: 200 OK");
                            break;
                        case PaymentStatus::CANCELED:
                            $simpla->orders->close($order);
                            header("HTTP/1.1 200 OK");
                            header("Status: 200 OK");
                            break;
                    }
                }

            } else {
                header("HTTP/1.1 404 Not Found");
                header("Status: 404 Not Found");
            }
        } else {
            header("HTTP/1.1 400 Bad Request");
            header("Status: 400 Bad Request");
        }
        exit();
    }

    /**
     * @param $apiClient
     * @param $payment
     * @param $order
     *
     * @return mixed
     */
    protected function capturePayment($apiClient, $payment, $order)
    {
        $captureRequest = CreateCaptureRequest::builder()->setAmount($payment->getAmount())->build();

        $tries = 0;
        do {
            $result = $apiClient->capturePayment(
                $captureRequest,
                $payment->id,
                $payment->id
            );
            if ($result === null) {
                $tries++;
                if ($tries > 3) {
                    break;
                }
                sleep(2);
            }
        } while ($result === null);

        return $result;
    }

    /**
     * @param $shopId
     * @param $shopPassword
     *
     * @return YandexMoneyApi
     */
    protected function getApiClient($shopId, $shopPassword)
    {
        $apiClient = new YandexMoneyApi();
        $apiClient->setAuth($shopId, $shopPassword);

        return $apiClient;
    }

    private function getPaymentId($orderId)
    {
        $query = $this->simplaApi->db->placehold(
            "
            SELECT o.payment_details
            FROM __orders AS o 
            WHERE o.id = ".$this->simplaApi->db->escape(intval($orderId))
        );
        $this->simplaApi->db->query($query);
        $result = array_shift($this->simplaApi->db->results());

        return $result->payment_details;
    }

    private function completePayment($order)
    {
        $comment = "Номер транзакции в Яндекс.Кассе: {$order->payment_details}. Сумма: {$order->total_price}";
        $query  = $this->simplaApi->db->placehold(
            "UPDATE s_orders SET paid=1, status=2, payment_date=NOW(),comment='{$comment}',modified=NOW() WHERE id=?",
            intval($order->id)
        );
        $result = $this->simplaApi->db->query($query);

        return $result;
    }
}