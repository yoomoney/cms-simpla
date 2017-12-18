
# Использование PHP SDK

## Методы клиента SDK

* getPaymentOptions - метод для получения способов оплаты и сценариев, доступных для заказа;
* getPayments - получение списока платежей магазина;
* [createPayment](docs/createPayment.md) - создание платежа;
* [getPaymentInfo](docs/getPaymentInfo.md) - получение информации о конкретном платеже;
* [capturePayment](docs/capturePayment.md) - подтверждение оплаты;
* [cancelPayment](docs/cancelPayment.md) - отмена незавершённой оплаты;
* getRefunds - получение списка возвратов;
* [createRefund](docs/createRefund.md) - создание нового возврата;
* getRefundInfo - получение информации о конкретном возврате.

## Общая информация о работе с SDK

Для начала нужно инициализировать клиент:
```php
use YaMoney\Client\YandexMoneyApi;
use YaMoney\Common\Exceptions\InvalidPropertyException;
use YaMoney\Common\Exceptions\InvalidRequestException;
use YaMoney\Model\CurrencyCode;
use YaMoney\Model\ConfirmationType;
use YaMoney\Request\PaymentOptionsRequest;

$client = new YandexMoneyApi();
$client->setAuth('shopId', 'shopPassword');
```

После чего можно с клиентом SDK работать.

Методы API не требующие для своего вызова объекты принимают один строковый аргумент:
* getPaymentInfo принимает идентификатор платежа в виде строки
* cancelPayment принимает идентификатор платежа в виде строки
* getRefundInfo принимает идентификатор возврата

Остальные методы принимают объект запроса, к примеру в сигнатуре метода getPaymentOptions указано, что он принимает на
вход объект типа PaymentOptionsRequestInterface, для того чтобы сгенерировать такой объект нужно написать:
```php
// получаем билдер запросов требуемого типа
$builder = PaymentOptionsRequest::builder();

try {
    // устанавливаем параметры запроса
    $builder->setAmount(13.43);
    $builder->setCurrency(CurrencyCode::USD);
    $builder->setConfirmationType(ConfirmationType::REDIRECT);

    // строим сам объект запроса
    $request = $builder->build();

    // отправляем запрос, получаем ответ
    $response = $client->getPaymentOptions($request);

} catch (InvalidPropertyException $e) {
    echo "Ошибка при установке свойства объекта запроса: " . $e->getMessage() . PHP_EOL;
    exit();
} catch (InvalidRequestException $e) {
    echo "Ошибка при постройке объекта запроса: " . $e->getMessage() . PHP_EOL;
    exit();
} catch (\Exception $e) {
    // При выполнении запроса произошла какая-то ошибка
    echo "Что-то пошло не так: " . $e->getMessage() . PHP_EOL;
    exit();
}
```

Все это можно записать в более коротком виде в виде цепочки вызовов:
```php
$response = $client->getPaymentOptions(
    PaymentOptionsRequest::builder()
        ->setAmount(13.43)
        ->setCurrency(CurrencyCode::USD)
        ->setConfirmationType(ConfirmationType::REDIRECT)
        ->build()
);
```

Кроме того в метод билд билдера можно передавать массив настроек как-то так:
```php
$response = $client->getPaymentOptions(
    PaymentOptionsRequest::builder()->build(
        array(
            'amount'           => 13.43,
            'currency'         => CurrencyCode::USD,
            'confirmationType' => ConfirmationType::REDIRECT,
        )
    )
);
```

Последовательность вызовов методов устанвки параметров через билдер не важна, главное, чтобы метод
build вызывался последним, при его вызове происходит валидация объекта запроса (проверяется чтобы
запрос содержал валидные данные и все обязательные поля были заполнены).

Результатом вызова методов апи являются объекты ответа, getPaymentOptions возвращает объект типа
PaymentOptionsResponse, с которым можно работать после получения как-то так:
```php
// отправляем запрос, получаем ответ
$response = $client->getPaymentOptions($request);

// выводим в консоль все полученные способы проведения платежа
foreach ($response->getItems() as $payment) {
    echo $payment->getPaymentMethodType() . ' ';
    echo implode('|', $payment->getConfirmationTypes()) . ' ';
    echo $payment->getCharge()->getAmount() . ' ';
    echo $payment->getCharge()->getCurrency() . ' ';
    if ($payment->getExtraFee()) {
        echo $payment->getFee()->getAmount() . ' ';
        echo $payment->getFee()->getCurrency() . ' ';
    } else {
        echo '-';
    }
    echo PHP_EOL;
}
```

Реквесты, респонзы, а так же используемые модельки так же поддерживают магические свойства,
поэтому то же самое можно сделать так:
```php
// отправляем запрос, получаем ответ
$response = $client->getPaymentOptions($request);

// выводим в консоль все полученные способы проведения платежа
foreach ($response->items as $payment) {
    echo $payment->paymentMethodType . ' ';
    echo implode('|', $payment->confirmationTypes) . ' ';
    echo $payment->charge->amount . ' ';
    echo $payment->charge->currency . ' ';
    if ($payment->extraFee) {
        echo $payment->fee->amount . ' ';
        echo $payment->fee->currency . ' ';
    } else {
        echo '-';
    }
    echo PHP_EOL;
}
```

## Вызов методов API

#### Проведение платежа

Для того чтобы провести платёж необходимо вызвать метод createPayment
```php
// билдер запроса на создание платежа
$builder = CreatePaymentRequest::builder();

try {
    // устанавливаем параметры платежа, обязательной является только сумма
    $builder->setAmount(3043.34);
} catch (\InvalidArgumentException $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    exit();
}

try {
    // отправяляем запрос, получаем ответ
    $response = $client->createPayment($builder->build());
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    exit();
}

if ($response->getError() !== null) {
    // если произошла ошибка
    echo 'Error#' . $response->getError()->getCode();
    if ($response->getError()->getDescription() !== null) {
        echo $response->getError()->getDescription();
    }
} else {
    // если ошибки нет, показываем айди созданного платежа и его статус
    echo 'Payment id is ' . $response->getId() . ' status ' . $response->getStatus();
}
```

Формирование чека в объекте запроса из заказа абстрактной CMS:
```php
// ...

// устанавливаем параметры платежа
$builder->setAmount(3043.34);
// добавляем информацию о товарах из заказа
foreach ($cmsOrder->getOrderItems() as $item) {
    $builder->addReceiptItem(
        $item->getTitle(),
        $item->getPrice(),
        $item->getQuantity(),
        $config->getTaxRate()
    );
}
// добавляем доставку в чек
$builder->addReceiptItem('Доставка', $cmsOrder->getShippingPrice(), 1, $config->getTaxRate());

// ...
```