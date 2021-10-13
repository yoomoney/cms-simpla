<?php
/**
 * Version: 2.1.0
 * License: Любое использование Вами программы означает полное и безоговорочное принятие Вами условий лицензионного договора, размещенного по адресу https://yoomoney.ru/doc.xml?id=527132 (далее – «Лицензионный договор»). Если Вы не принимаете условия Лицензионного договора в полном объёме, Вы не имеете права использовать программу в каких-либо целях.
 */

require_once 'api/Simpla.php';
require_once 'autoload.php';
define('YOOMONEY_MODULE_VERSION', '2.1.0');

use YooKassa\Client;
use YooKassa\Model\Payment;
use YooKassa\Request\Payments\CreatePaymentRequest;
use YooMoneyModule\YooMoneyLogger;

class YooMoneyApi extends Simpla
{
    const DEFAULT_TAX_RATE_ID = 1;

    const INSTALLMENTS_MIN_AMOUNT = 3000;

    /**
     * @param int|mixed $order_id
     * @param string|null $button_text
     *
     * @return string
     */
    public function checkout_form($order_id, $button_text = null)
    {
        if (empty($button_text)) {
            $button_text = 'Перейти к оплате';
        }

        $order            = $this->orders->get_order((int)$order_id);
        $payment_method   = $this->payment->get_payment_method($order->payment_method_id);
        $settings         = $this->payment->get_payment_settings($payment_method->id);
        $amount           = round($this->money->convert($order->total_price, $payment_method->currency_id, false), 2);
        $result_url       = $this->config->root_url.'/payment/YooMoneyApi/callback.php?order='.$order->id.'&action=return';
        $payment_sitemode = ($settings['yoomoney_paymode'] == 'site') ? true : false;
        $payment_type     = ($payment_sitemode) ? $settings['yoomoney_paymenttype'] : '';

        $logger = new YooMoneyLogger($settings['yookassa_debug']);

        if (($payment_type == \YooKassa\Model\PaymentMethodType::INSTALLMENTS)
            && ($amount < self::INSTALLMENTS_MIN_AMOUNT)
        ) {
            return '<span style="color:#ec0060;">Заплатить этим способом не получится: сумма должна быть больше '
                . self::INSTALLMENTS_MIN_AMOUNT . ' рублей.</span>';
        }

        if ($payment_type == \YooKassa\Model\PaymentMethodType::ALFABANK) {
            if (isset($_POST['alfabak_login']) && !empty($_POST['alfabak_login'])) {
                $payment_type = new \YooKassa\Model\PaymentData\PaymentDataAlfabank();
                try {
                    $payment_type->setLogin($_POST['alfabak_login']);
                } catch (Exception $e) {
                    return $this->getAlfaForm($button_text, true);
                }
            } else {
                return $this->getAlfaForm($button_text, true);
            }
        }

        if ($payment_type == \YooKassa\Model\PaymentMethodType::QIWI) {
            if (isset($_POST['qiwi_phone']) && !empty($_POST['qiwi_phone'])) {

                $payment_type = new \YooKassa\Model\PaymentData\PaymentDataQiwi();
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

        if (isset($_POST['payment_submit'])) {
            if (!empty($_POST['payment_type'])) {
                $payment_type = $_POST['payment_type'];
            }
            $apiClient = $this->getApiClient($settings['yoomoney_shopid'], $settings['yoomoney_password'], $settings['yookassa_debug']);

            $builder = CreatePaymentRequest::builder()
                       ->setAmount($amount)
                       ->setPaymentMethodData($payment_type)
                       ->setCapture(true)
                       ->setDescription($this->createDescription($order, $settings))
                       ->setConfirmation(
                           array(
                               'type'      => \YooKassa\Model\ConfirmationType::REDIRECT,
                               'returnUrl' => $result_url,
                           )
                       )
                       ->setMetadata(array(
                           'cms_name'       => 'yoo_simpla',
                           'module_version' => YOOMONEY_MODULE_VERSION,
                           'order_id'       => $order_id,
                       ));

            if (isset($settings['yookassa_api_send_check']) && $settings['yookassa_api_send_check']) {

                $purchases = $this->orders->get_purchases(array('order_id' => intval($order->id)));

                if(!empty($order->email)) {
                    $builder->setReceiptEmail($order->email);
                }
                if(!empty($order->phone)) {
                    $builder->setReceiptPhone($order->phone);
                }

                $id_tax = (isset($settings['yookassa_api_tax']) && $settings['yookassa_api_tax'] ? $settings['yookassa_api_tax'] : self::DEFAULT_TAX_RATE_ID);
                foreach ($purchases as $purchase) {
                    $properties     = $this->features->get_product_options($purchase->product_id);
                    $paymentMode    = $this->getPaymentMode($properties, $settings);
                    $paymentSubject = $this->getPaymentSubject($properties, $settings);
                    $builder->addReceiptItem($purchase->product_name, $purchase->price, $purchase->amount, $id_tax,
                        $paymentMode, $paymentSubject);
                }
                if ($order->delivery_id && !$order->separate_delivery && $order->delivery_price > 0) {
                    $delivery = $this->delivery->get_delivery($order->delivery_id);
                    $builder->addReceiptShipping($delivery->name, $order->delivery_price, $id_tax,
                        $settings['yookassa_api_payment_mode'], $settings['yookassa_api_payment_subject']);
                }
            }

            $paymentRequest = $builder->build();
            $receipt = $paymentRequest->getReceipt();
            if ($receipt instanceof \YooKassa\Model\Receipt) {
                $receipt->normalize($paymentRequest->getAmount());
            }
            $idempotencyKey = base64_encode($order->id.microtime());
            try {
                $response = $apiClient->createPayment($paymentRequest, $idempotencyKey);
            } catch (Exception $exception) {
                $logger->error($exception->getMessage());
            }

            if (!empty($response)) {
                $order->payment_details = $response->getId();
                $this->orders->update_order($order->id, $order);
                $confirmationUrl = $response->confirmation->confirmationUrl;
                header('Location: '.$confirmationUrl);
            } else {
                return '<span style="color:#ec0060;">Платеж не прошел. Попробуйте еще или выберите другой способ оплаты.</span>';
            }
        } else {
            return $this->getForm($button_text, $settings, $amount);
        }
    }

    /**
     * @param string $button_text
     * @param bool $error
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
     * @param string $button_text
     * @param bool $error
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

    /**
     * @param string $button_text
     * @param array $settings
     * @param float $amount
     *
     * @return string
     */
    private function getForm($button_text, $settings, $amount)
    {
        ob_start();
        ?>
        <style type="text/css">
            .yoomoney_kassa_buttons {
                display: flex;
                margin-bottom: 20px;
            }

            .yookassa_installments_button_container {
                margin-right: 20px;
            }

            .yoomoney-pay-button {
                position: relative;
                height: 60px;
                width: 155px;
                border-radius: 4px;
                font-family: YandexSansTextApp-Regular, Arial, Helvetica, sans-serif;
                text-align: center;
            }

            .yoomoney-pay-button button {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                border-radius: 4px;
                transition: 0.1s ease-out 0s;
                color: #000;
                box-sizing: border-box;
                outline: 0;
                border: 0;
                background: #FFDB4D;
                cursor: pointer;
                font-size: 12px;
            }

            .yoomoney-pay-button button:hover, .yoomoney-pay-button button:active {
                background: #f2c200;
            }

            .yoomoney-pay-button button span {
                display: block;
                font-size: 20px;
                line-height: 20px;
            }

            .yoomoney-pay-button_type_fly {
                box-shadow: 0 1px 0 0 rgba(0, 0, 0, 0.12), 0 5px 10px -3px rgba(0, 0, 0, 0.3);
            }

            .yookassa_button {
                cursor: pointer;
            }

            .yookassa_button:hover {
                background-color: #abff87;
            }
        </style>
        <form method="POST">
            <input type="hidden" name="payment_submit"/>
            <input type="hidden" name="payment_type" id="pm_yoomoney_payment_type" value=""/>
            <?php
            $onKassaSide             = $settings['yoomoney_paymode'] === 'kassa';
            $showInstallmentsButton  = false;

            if ($onKassaSide) {
                $showInstallmentsButton  = $settings['yoomoney_show_installments_button'];
                if ($showInstallmentsButton) {
                    ?>
                    <div class="yoomoney_kassa_buttons">
                        <div class="yookassa_installments_button_container"></div>
                    </div>
                <?php
                }
            }

            if (!$onKassaSide || ($onKassaSide && !$showInstallmentsButton)) {
                ?>
                <input type="submit" name="submit-button" value="<?= $button_text; ?>"
                       class="checkout_button yookassa_button">
                <?php
            }
            ?>
        </form>
        <?php
        if ($onKassaSide && $showInstallmentsButton) {
            ?>
            <script src="https://static.yoomoney.ru/checkout-credit-ui/v1/index.js"></script>
            <script type="text/javascript"><!--
                jQuery(document).ready(function () {
                    const yooShopId = <?= $settings['yoomoney_shopid']; ?>;
                    const yooAmount = <?= $amount; ?>;

                    function createCheckoutCreditUI() {
                        if (!CheckoutCreditUI) {
                            setTimeout(createCheckoutCreditUI, 200);
                        }
                        const yoomoneyСheckoutCreditUI = CheckoutCreditUI({
                            shopId: yooShopId,
                            sum: yooAmount
                        });
                        const checkoutCreditButton = yoomoneyСheckoutCreditUI({
                            type: 'button',
                            domSelector: '.yookassa_installments_button_container'
                        });
                        checkoutCreditButton.on('click', function () {
                            jQuery('#pm_yoomoney_payment_type').val('installments');
                        });
                    };
                    setTimeout(createCheckoutCreditUI, 200);
                });
                //--></script>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    /**
     * @param $shopId
     * @param $shopPassword
     * @param $logger
     *
     * @return Client
     */
    public function getApiClient($shopId, $shopPassword, $logger)
    {
        $apiClient = new Client();
        $apiClient->setAuth($shopId, $shopPassword);
        $apiClient->setLogger(new YooMoneyLogger($logger));
        $userAgent = $apiClient->getApiClient()->getUserAgent();
        $userAgent->setCms('Simpla', $this->config->version);
        $userAgent->setModule('yoomoney-cms-simpla', YOOMONEY_MODULE_VERSION);

        return $apiClient;
    }

    /**
     * @param $orderInfo
     * @param $config
     *
     * @return bool|string
     */
    private function createDescription($orderInfo, $config)
    {
        $descriptionTemplate = !empty($config['yoomoney_description_template'])
            ? $config['yoomoney_description_template']
            : 'Оплата заказа №%id%';

        $replace = array();
        foreach ($orderInfo as $key => $value) {
            if (is_scalar($value)) {
                $replace['%'.$key.'%'] = $value;
            }
        }

        $description = strtr($descriptionTemplate, $replace);

        return (string)mb_substr($description, 0, Payment::MAX_LENGTH_DESCRIPTION);
    }

    /**
     * @param $properties
     * @param $settings
     * @return mixed
     */
    private function getPaymentMode($properties, $settings)
    {
        foreach ($properties as $property) {
            if ($property->name == 'payment_mode') {
                return $property->value;
            }
        }

        return $settings['yookassa_api_payment_mode'];
    }

    /**
     * @param $properties
     * @param $settings
     * @return mixed
     */
    private function getPaymentSubject($properties, $settings)
    {
        foreach ($properties as $property) {
            if ($property->name == 'payment_subject') {
                return $property->value;
            }
        }

        return $settings['yookassa_api_payment_subject'];
    }
}