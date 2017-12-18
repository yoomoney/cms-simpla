
### Осуществление возврата платежа

Если платёж был подтвержден, но сумму необходимо вернуть плательщику, нужно вызывать метод createRefund.
Для создания возврата необходимо помимо указания идентификатора платежа задать возвращаемую сумму.
```php
use YaMoney\Request\Refunds\CreateRefundRequest;

// ...

// собираем запрос создания возврата
try {
    $builder = CreateRefundRequest::builder();
    $builder
        ->setPaymentId($payment->getId())
        ->setAmount($payment->getAmount())
        ->setComment('Комментарий к возврату');
    $request = $builder->build();
} catch (InvalidArgumentException $e) {
    $this->log('error', 'Failed to create refund request: ' . $e->getMessage());
    exit();
}

// осуществляем создание возврата
try {
    $key = $payment->getId();
    $tries = 0;
    do {
        $response = $this->getClient()->createRefund($request, $payment->getId(), $key);
        if ($response === null) {
            $tries++;
            if ($tries > 3) {
                break;
            }
            sleep(2);
        }
    } while ($response === null);
} catch (Exception $e) {
    $this->log('error', 'Failed to create refund: ' . $e->getMessage());
    $response = null;
}
if ($response === null) {
    // не удалось создать возврат
    die();
}
```
Если требуется отменить ещё не подтверждённый платёж, нужно воспользоваться методом [cancelPayment](cancelPayment.md)
