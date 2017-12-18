<?php
/**
 * Version: 1.0.1
 * License: Любое использование Вами программы означает полное и безоговорочное принятие Вами условий лицензионного договора, размещенного по адресу https://money.yandex.ru/doc.xml?id=527132 (далее – «Лицензионный договор»). Если Вы не принимаете условия Лицензионного договора в полном объёме, Вы не имеете права использовать программу в каких-либо целях.
 */
require_once('api/Simpla.php');
require_once 'YandexMoneyLogger.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'vendor/autoload.php';
define(YAMONEY_MODULE_VERSION, '1.0.1');
use YaMoney\Client\YandexMoneyApi as Api;
use YaMoney\Request\Payments\CreatePaymentRequest;

class YandexMoneyApi extends Simpla
{
    const DEFAULT_TAX_RATE_ID = 1;

    public function checkout_form($order_id, $button_text = null)
    {
        if (empty($button_text)) {
            $button_text = 'Перейти к оплате';
        }

        $order            = $this->orders->get_order((int)$order_id);
        $payment_method   = $this->payment->get_payment_method($order->payment_method_id);
        $settings         = $this->payment->get_payment_settings($payment_method->id);
        $amount           = round($this->money->convert($order->total_price, $payment_method->currency_id, false), 2);
        $result_url       = $this->config->root_url.'/payment/YandexMoneyApi/callback.php?order='.$order->id.'&action=return';
        $payment_sitemode = ($settings['yandex_api_paymode'] == 'site') ? true : false;
        $payment_type     = ($payment_sitemode) ? $settings['yandex_api_paymenttype'] : '';

        if ($payment_type == \YaMoney\Model\PaymentMethodType::ALFABANK) {
            if (isset($_POST['alfabak_login']) && !empty($_POST['alfabak_login'])) {
                $payment_type = new \YaMoney\Model\PaymentData\PaymentDataAlfabank();
                try {
                    $payment_type->setLogin($_POST['alfabak_login']);
                } catch (Exception $e) {
                    return $this->getAlfaForm($button_text, true);
                }
            } else {
                return $this->getAlfaForm($button_text, true);
            }
        }

        if ($payment_type == \YaMoney\Model\PaymentMethodType::QIWI) {
            if (isset($_POST['qiwi_phone']) && !empty($_POST['qiwi_phone'])) {

                $payment_type = new \YaMoney\Model\PaymentData\PaymentDataQiwi();
                $phone        = preg_replace('/[^\d]/', '', $_POST['qiwi_phone']);
                try {
                    $payment_type->setPhone($phone);
                } catch (Exception $e) {
                    return $this->getQiwiForm($button_text, true);
                }
            } else {
                return $this->getQiwiForm($button_text, true);
            }
        }

        if (isset($_POST['submit-button'])) {
            $apiClient = new Api();
            $apiClient->setAuth($settings['yandex_api_shopid'], $settings['yandex_api_password']);
            $apiClient->setLogger(new YandexMoneyLogger($settings['ya_kassa_debug']));
            $builder = CreatePaymentRequest::builder()
                                           ->setAmount($amount)
                                           ->setPaymentMethodData($payment_type)
                                           ->setCapture(false)
                                           ->setConfirmation(
                                               array(
                                                   'type'      => \YaMoney\Model\ConfirmationType::REDIRECT,
                                                   'returnUrl' => $result_url,
                                               )
                                           )
                                           ->setMetadata(array(
                                               'cms_name'       => 'ya_api_simpla',
                                               'module_version' => YAMONEY_MODULE_VERSION,
                                           ));

            if (isset($settings['ya_kassa_api_send_check']) && $settings['ya_kassa_api_send_check']) {

                $purchases = $this->orders->get_purchases(array('order_id' => intval($order->id)));

                $builder->setReceiptEmail($order->email);

                $id_tax = (isset($settings['ya_kassa_api_tax']) && $settings['ya_kassa_api_tax'] ? $settings['ya_kassa_api_tax'] : self::DEFAULT_TAX_RATE_ID);

                foreach ($purchases as $purchase) {
                    $builder->addReceiptItem($purchase->product_name, $purchase->price, $purchase->amount, $id_tax);
                }

                if ($order->delivery_id && $order->delivery_price > 0) {
                    $delivery = $this->delivery->get_delivery($order->delivery_id);
                    $builder->addReceiptShipping($delivery->name, $order->delivery_price, $id_tax);
                }
            }

            $paymentRequest = $builder->build();
            $idempotencyKey = base64_encode($order->id.microtime());
            $tries          = 0;
            do {
                $response = $apiClient->createPayment(
                    $paymentRequest,
                    $idempotencyKey
                );
                if ($response === null) {
                    $tries++;
                    if ($tries > 3) {
                        break;
                    }
                    sleep(2);
                }
            } while ($response === null);

            if ($response) {
                $order->payment_details = $response->getId();
                $this->orders->update_order($order->id, $order);
                $confirmationUrl = $response->confirmation->confirmationUrl;
                header('Location: '.$confirmationUrl);
            }
        } else {
            return $this->getForm($button_text);
        }
    }

    /**
     * @param $button_text
     *
     * @return string
     */
    protected function getQiwiForm($button_text, $error = false)
    {
        $button = '<form method="POST" >';
        if ($error) {
            $button .= '<div style="color: red">Поле телефон заполнено неверно.</div>';
        }
        $button .= '<div style="width: 600px">
                        Телефон, который привязан к Qiwi Wallet
                    </div>
                    <div style="width: 600px">
                        <input type="text" name="qiwi_phone" value="">
                    </div>
					<input type="submit" name="submit-button" value="'.$button_text.'" class="checkout_button">
                </form>';

        return $button;
    }

    /**
     * @param $button_text
     *
     * @return string
     */
    protected function getAlfaForm($button_text, $error = false)
    {
        $button = '<form method="POST" >';
        if ($error) {
            $button .= '<div style="color: red">Поле логин заполнено неверно.</div>';
        }
        $button .= '<div style="width: 600px">
                        Укажите логин, и мы выставим счет в Альфа-Клике. После этого останется подтвердить платеж на сайте интернет-банка.
                    </div>
                    <div style="width: 600px">
                        <input type="text" name="alfabank_login" value="">
                    </div>
					<input type="submit" name="submit-button" value="'.$button_text.'" class="checkout_button">
					</form>';

        return $button;
    }

    private function getForm($button_text)
    {
        $button = '<form method="POST" >
					<input type="submit" name="submit-button" value="'.$button_text.'" class="checkout_button">
                </form>';

        return $button;
    }
}