
### Отмена платежа

До того, как платёж перешёл в состояние [PaymentStatus](../lib/Model/PaymentStatus.php)::SUCCEEDED его можно отменить, для этого нужно вызвать метод cancelPayment:
```php
try {
    $key = $payment->getId();
    $tries = 0;
    $triesCount = 3;
    $timeout = 2;
    do {
        $response = $client->cancelPayment($payment->getId(), $key);
        if ($response === null) {
            $tries++;
            if ($tries > $triesCount) {
                break;
            }
            sleep($timeout);
        }
    } while ($response === null);
} catch (\Exception $e) {
    // что-то пошло не так
    $response = null;
}
if ($response === null) {
    // не удалось отменить платёж
} else {
    // в $response - информация об отменённом платеже
}
```
Если требуется вернуть деньги по уже подтверждённому платежу необходимо оформить возврат с помощью метода [createRefund](createRefund.md), отменить его нельзя.