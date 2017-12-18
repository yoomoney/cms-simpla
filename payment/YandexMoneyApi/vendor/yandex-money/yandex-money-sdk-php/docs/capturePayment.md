
### Подтверждение платежа

Если платеж был создан с параметром capture равным false то, в конце кончоц платеж перейдет в состояние [PaymentStatus](../lib/Model/PaymentStatus.php)::WAITING_FOR_CAPTURE, после чего необходимо будет подтвердить платёж со стороны мерчанта.

При подтверждении платежа необходимо указать сумму платежа.

Подтверждение платежа производится с помощью метода capturePayment так:
```php
use YaMoney\Request\Payments\Payment\CreateCaptureRequest;

// ...

// собираем запрос подтверждения платежа
try {
    $builder = CreateCaptureRequest::builder();
    $builder->setAmount($payment->getAmount());
    $request = $builder->build();
} catch (InvalidArgumentException $e) {
    $this->log('error', 'Failed to create capture payment request: ' . $e->getMessage());
    exit();
}

// осуществляем подтверждение платежа
try {
    $key = $payment->getId();
    $tries = 0;
    do {
        $response = $this->getClient()->capturePayment($request, $payment->getId(), $key);
        if ($response === null) {
            $tries++;
            if ($tries > 3) {
                break;
            }
            sleep(2);
        }
    } while ($response === null);
} catch (Exception $e) {
    $this->log('error', 'Failed to capture payment: ' . $e->getMessage());
    $response = null;
}
if ($response === null) {
    // не удалось подтвердить платёж
    die();
}
// в $payment теперь лежит последняя информация о платеже
$payment = $response;
```